<?php

use App\Models\PosSession;
use App\Services\PosReportService;

/** Ouvre une session POS avec fonds de caisse. */
function openPosSessionZ($user, float $openingFloat = 5000, array $attributes = []): PosSession
{
    return PosSession::create([
        'company_id' => $user->current_company_id,
        'user_id' => $user->id,
        'status' => 'open',
        'opening_float' => $openingFloat,
        'opened_at' => now(),
        ...$attributes,
    ]);
}

/** Ajoute des ventes simulées dans les totaux de la session. */
function addSalesToSession(PosSession $session, float $cashAmount, float $cardAmount = 0): void
{
    $totals = $session->totals_by_method ?? [];
    if ($cashAmount > 0) {
        $totals['cash'] = round(($totals['cash'] ?? 0) + $cashAmount, 2);
    }
    if ($cardAmount > 0) {
        $totals['card'] = round(($totals['card'] ?? 0) + $cardAmount, 2);
    }
    $session->update([
        'total_sales' => round((float) $session->total_sales + $cashAmount + $cardAmount, 2),
        'tickets_count' => $session->tickets_count + 1,
        'totals_by_method' => $totals,
    ]);
    $session->refresh();
}

// ─── Tests ───────────────────────────────────────────────────────────────────

it('opens session with opening float', function () {
    $user = createUserWithCompanyAndTrial();

    $this->actingAs($user)
        ->post(route('pos.sessions.open'), ['opening_float' => 10000])
        ->assertRedirect();

    expect(PosSession::where('company_id', $user->current_company_id)->exists())->toBeTrue();
    $session = PosSession::where('company_id', $user->current_company_id)->first();
    expect((float) $session->opening_float)->toBe(10000.0);
    expect($session->status)->toBe('open');
});

it('generates x report without closing session', function () {
    $user = createUserWithCompanyAndTrial();
    $session = openPosSessionZ($user, 5000);
    addSalesToSession($session, 3000, 1000);

    $this->actingAs($user)
        ->getJson(route('pos.x-report', $session))
        ->assertOk()
        ->assertJsonPath('type', 'X')
        ->assertJsonPath('total_sales', 4000);

    // Session toujours ouverte
    expect($session->fresh()->status)->toBe('open');
});

it('x report shows theoretical cash including float', function () {
    $user = createUserWithCompanyAndTrial();
    $session = openPosSessionZ($user, 5000);
    addSalesToSession($session, 3000); // seulement espèces

    $service = app(PosReportService::class);
    $report = $service->generateXReport($session->fresh());

    // Caisse théorique = fonds d'ouverture + ventes cash
    expect($report['theoretical_cash'])->toBe(8000.0);
    expect($report['opening_float'])->toBe(5000.0);
});

it('generates z report and closes session', function () {
    $user = createUserWithCompanyAndTrial();
    $session = openPosSessionZ($user, 5000);
    addSalesToSession($session, 2000);

    $this->actingAs($user)
        ->postJson(route('pos.z-report.generate', $session), [
            'actual_cash' => 7000,
            'notes' => 'Test clôture',
        ])
        ->assertOk()
        ->assertJsonPath('type', 'Z');

    $session->refresh();
    expect($session->status)->toBe('closed');
    expect($session->z_report_number)->not->toBeNull();
    expect($session->z_report_generated_at)->not->toBeNull();
    expect((float) $session->counted_cash)->toBe(7000.0);
});

it('z report number increments per company', function () {
    $user = createUserWithCompanyAndTrial();
    $service = app(PosReportService::class);

    $s1 = openPosSessionZ($user, 5000);
    $r1 = $service->generateZReport($s1, 5000);

    $s2 = openPosSessionZ($user, 5000);
    $r2 = $service->generateZReport($s2, 5000);

    expect($r1['z_number'])->not->toBe($r2['z_number']);
    // Le second numéro doit avoir une séquence plus élevée
    expect($r2['z_number'])->toContain('-0002');
});

it('cannot generate z report twice', function () {
    $user = createUserWithCompanyAndTrial();
    $session = openPosSessionZ($user, 5000);

    $service = app(PosReportService::class);
    $service->generateZReport($session, 5000);

    $this->actingAs($user)
        ->postJson(route('pos.z-report.generate', $session), ['actual_cash' => 5000])
        ->assertStatus(422)
        ->assertJsonPath('error', 'Rapport Z déjà généré pour cette session.');
});

it('z report calculates cash difference correctly', function () {
    $user = createUserWithCompanyAndTrial();
    $session = openPosSessionZ($user, 5000);
    addSalesToSession($session, 3000); // ventes cash uniquement

    // Caisse théorique = 5000 + 3000 = 8000
    // Caisse comptée = 7800 → écart = -200

    $service = app(PosReportService::class);
    $report = $service->generateZReport($session->fresh(), 7800);

    expect($report['expected_cash'])->toBe(8000.0);
    expect($report['actual_cash'])->toBe(7800.0);
    expect($report['cash_difference'])->toBe(-200.0);
});

it('z report pdf returns pdf response', function () {
    $user = createUserWithCompanyAndTrial();
    $session = openPosSessionZ($user, 5000);
    $service = app(PosReportService::class);
    $service->generateZReport($session, 5000);

    $this->actingAs($user)
        ->get(route('pos.z-report.pdf', $session))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

it('z history returns company reports only', function () {
    $user = createUserWithCompanyAndTrial();
    $service = app(PosReportService::class);

    // 2 rapports Z pour cette company
    $s1 = openPosSessionZ($user, 5000);
    $service->generateZReport($s1, 5000);

    $s2 = openPosSessionZ($user, 3000);
    $service->generateZReport($s2, 3000);

    $history = $service->getZHistory($user->current_company_id);
    expect($history)->toHaveCount(2);
});

it('isolates between companies', function () {
    $user1 = createUserWithCompanyAndTrial();
    $user2 = createUserWithCompanyAndTrial();
    $service = app(PosReportService::class);

    $s1 = openPosSessionZ($user1, 5000);
    $service->generateZReport($s1, 5000);

    // Company 2 ne doit voir aucun rapport Z
    $history2 = $service->getZHistory($user2->current_company_id);
    expect($history2)->toHaveCount(0);

    // L'API history renvoie bien la séparation
    $this->actingAs($user2)
        ->get(route('pos.z-reports.history'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Pos/ZReport')
            ->where('reports', [])
        );
});
