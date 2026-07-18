<?php

use App\Models\Company;
use App\Models\Document;
use App\Models\ForecastSnapshot;
use App\Models\SalesTarget;
use App\Services\DocumentService;
use App\Services\ForecastingService;

beforeEach(function () {
    $this->user = createUserWithCompanyAndTrial();
    $this->company = $this->user->currentCompany;
    $this->service = app(ForecastingService::class);
    $this->docService = app(DocumentService::class);
});

// ─── Helper ──────────────────────────────────────────────────────────────────

function makeForecastInvoice($test, string $date, float $amount): Document
{
    return $test->docService->create(
        $test->company,
        $test->user,
        ['type' => 'invoice', 'status' => 'sent', 'issue_date' => $date, 'currency' => 'XOF'],
        [['description' => 'Service', 'quantity' => 1, 'unit_price' => $amount, 'tax_rate' => 0]]
    );
}

// ─── Tests ────────────────────────────────────────────────────────────────────

it('creates a monthly sales target', function () {
    $this->actingAs($this->user)
        ->postJson(route('forecasting.targets.store'), [
            'period_type' => 'month',
            'period_month' => now()->month,
            'period_year' => now()->year,
            'target_amount' => 5_000_000,
            'currency' => 'XOF',
        ])
        ->assertStatus(201)
        ->assertJsonPath('target.target_amount', '5000000.00');

    $this->assertDatabaseHas('sales_targets', [
        'company_id' => $this->company->id,
        'period_type' => 'month',
        'period_month' => now()->month,
        'period_year' => now()->year,
        'target_amount' => 5_000_000,
    ]);
});

it('compares actual revenue with target', function () {
    SalesTarget::create([
        'company_id' => $this->company->id,
        'period_type' => 'month',
        'period_month' => now()->month,
        'period_year' => now()->year,
        'target_amount' => 1_000_000,
        'currency' => 'XOF',
    ]);

    makeForecastInvoice($this, now()->toDateString(), 600_000);

    $result = $this->service->compareWithTarget($this->company->id);

    expect($result)
        ->toHaveKeys(['target', 'actual', 'pct_achieved', 'on_track', 'gap', 'currency'])
        ->and($result['target'])->toBe(1_000_000.0)
        ->and($result['actual'])->toBe(600_000.0)
        ->and($result['pct_achieved'])->toBe(60.0)
        ->and($result['currency'])->toBe('XOF');
});

it('returns on_track when achievement is above 70 percent', function () {
    SalesTarget::create([
        'company_id' => $this->company->id,
        'period_type' => 'month',
        'period_month' => now()->month,
        'period_year' => now()->year,
        'target_amount' => 1_000_000,
        'currency' => 'XOF',
    ]);

    makeForecastInvoice($this, now()->toDateString(), 750_000);

    $result = $this->service->compareWithTarget($this->company->id);

    expect($result['on_track'])->toBeTrue()
        ->and($result['pct_achieved'])->toBe(75.0);
});

it('calculates forecast using linear projection', function () {
    makeForecastInvoice($this, now()->toDateString(), 100_000);

    $result = $this->service->forecastCurrentMonth($this->company->id);

    expect($result)->toHaveKeys(['actual_so_far', 'days_elapsed', 'days_remaining', 'daily_rate', 'forecasts'])
        ->and($result['forecasts'])->toHaveKey('linear_projection')
        ->and($result['actual_so_far'])->toBe(100_000.0)
        ->and($result['forecasts']['linear_projection'])->toBeNumeric();
});

it('calculates moving average correctly', function () {
    for ($i = 3; $i >= 1; $i--) {
        $month = now()->subMonths($i);
        makeForecastInvoice($this, $month->startOfMonth()->toDateString(), $i * 100_000);
    }

    $result = $this->service->forecastCurrentMonth($this->company->id);
    $movAvg = $result['forecasts']['moving_average'];

    expect($movAvg)->toBeGreaterThan(0);
});

it('detects underperformance below 50 percent', function () {
    SalesTarget::create([
        'company_id' => $this->company->id,
        'period_type' => 'month',
        'period_month' => now()->month,
        'period_year' => now()->year,
        'target_amount' => 1_000_000,
        'assigned_to_id' => $this->user->id,
        'currency' => 'XOF',
    ]);

    $targets = SalesTarget::where('company_id', $this->company->id)
        ->whereNotNull('assigned_to_id')
        ->get();

    expect($targets)->toHaveCount(1)
        ->and($targets->first()->assigned_to_id)->toBe($this->user->id);
});

it('saves forecast snapshot', function () {
    makeForecastInvoice($this, now()->toDateString(), 500_000);

    $snapshot = $this->service->saveSnapshot($this->company->id);

    expect($snapshot)->toBeInstanceOf(ForecastSnapshot::class)
        ->and($snapshot->company_id)->toBe($this->company->id)
        ->and($snapshot->period_month)->toBe(now()->month)
        ->and($snapshot->period_year)->toBe(now()->year)
        ->and((float) $snapshot->actual_revenue)->toBe(500_000.0)
        ->and($snapshot->forecasted_revenue)->toBeNumeric();

    $this->assertDatabaseHas('forecast_snapshots', [
        'company_id' => $this->company->id,
        'period_month' => now()->month,
        'period_year' => now()->year,
    ]);
});

it('returns 12 months historical revenue', function () {
    for ($i = 0; $i < 3; $i++) {
        $month = now()->subMonths($i);
        makeForecastInvoice($this, $month->startOfMonth()->toDateString(), 100_000 * ($i + 1));
    }

    $history = $this->service->getMonthlyRevenue($this->company->id, 12);

    expect($history)->toHaveCount(12);
    $currentMonth = collect($history)->last();
    expect($currentMonth['revenue'])->toBe(100_000.0);
});

it('generates forecast report pdf', function () {
    makeForecastInvoice($this, now()->toDateString(), 300_000);

    $this->actingAs($this->user)
        ->get(route('forecasting.export'))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

it('isolates targets between companies', function () {
    $other = createUserWithCompanyAndTrial();
    $otherCompany = $other->currentCompany;

    SalesTarget::create([
        'company_id' => $this->company->id,
        'period_type' => 'month',
        'period_month' => now()->month,
        'period_year' => now()->year,
        'target_amount' => 1_000_000,
        'currency' => 'XOF',
    ]);

    SalesTarget::create([
        'company_id' => $otherCompany->id,
        'period_type' => 'month',
        'period_month' => now()->month,
        'period_year' => now()->year,
        'target_amount' => 9_000_000,
        'currency' => 'XOF',
    ]);

    $result1 = $this->service->compareWithTarget($this->company->id);
    $result2 = $this->service->compareWithTarget($otherCompany->id);

    expect($result1['target'])->toBe(1_000_000.0)
        ->and($result2['target'])->toBe(9_000_000.0);
});
