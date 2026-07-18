<?php

use App\Models\Plan;
use App\Models\PrepaidVoucher;
use App\Services\VoucherService;

beforeEach(function () {
    seedPlans();
    $this->service = app(VoucherService::class);
    $this->plan = Plan::where('code', 'pro')->firstOrFail();
});

/** Paramètres de base pour générer un lot. */
function batchParams(array $overrides = []): array
{
    return [
        'quantity'        => 5,
        'plan_id'         => null,
        'duration_months' => 1,
        'currency'        => 'XOF',
        'face_value'      => 10000,
        'reseller_price'  => 8000,
        'reseller_name'   => 'Revendeur Test',
        'created_by'      => null, // null = pas de contrainte FK en test
        ...$overrides,
    ];
}

// ── Génération ───────────────────────────────────────────────────────────────

it('can generate a batch of vouchers', function () {
    $result = $this->service->generateBatch(batchParams(['quantity' => 3]));

    expect($result)->toHaveKey('batch_ref')
        ->and($result)->toHaveKey('vouchers')
        ->and($result['vouchers'])->toHaveCount(3)
        ->and(PrepaidVoucher::where('batch_ref', $result['batch_ref'])->count())->toBe(3);
});

it('generated codes are unique', function () {
    $result = $this->service->generateBatch(batchParams(['quantity' => 20]));

    $codes = collect($result['vouchers'])->pluck('code');
    expect($codes->unique()->count())->toBe(20);
});

it('code format is IBIG-XXXX-XXXX-XXXX', function () {
    $result = $this->service->generateBatch(batchParams(['quantity' => 1]));

    $code = $result['vouchers'][0]->code;
    expect($code)->toMatch('/^IBIG-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/');
});

// ── Vérification ─────────────────────────────────────────────────────────────

it('verify returns valid for available code', function () {
    $result = $this->service->generateBatch(batchParams(['quantity' => 1]));
    $code = $result['vouchers'][0]->code;

    $verification = $this->service->verify($code);

    expect($verification['valid'])->toBeTrue()
        ->and($verification)->toHaveKey('duration_months')
        ->and($verification)->toHaveKey('face_value');
});

it('verify returns invalid for used code', function () {
    $voucher = PrepaidVoucher::create([
        'code'            => 'IBIG-USED-USED-USED',
        'duration_months' => 1,
        'currency'        => 'XOF',
        'face_value'      => 10000,
        'status'          => 'used',
    ]);

    $result = $this->service->verify($voucher->code);

    expect($result['valid'])->toBeFalse()
        ->and($result['error'])->toContain('déjà utilisé');
});

it('verify returns invalid for expired code', function () {
    PrepaidVoucher::create([
        'code'            => 'IBIG-EXPR-EXPR-EXPR',
        'duration_months' => 1,
        'currency'        => 'XOF',
        'face_value'      => 10000,
        'status'          => 'available',
        'expires_at'      => now()->subDay(),
    ]);

    $result = $this->service->verify('IBIG-EXPR-EXPR-EXPR');

    expect($result['valid'])->toBeFalse()
        ->and($result['error'])->toContain('expiré');
});

it('verify returns invalid for nonexistent code', function () {
    $result = $this->service->verify('IBIG-FAKE-FAKE-FAKE');

    expect($result['valid'])->toBeFalse()
        ->and($result['error'])->toContain('introuvable');
});

// ── Activation (redeem) ──────────────────────────────────────────────────────

it('redeem activates license immediately', function () {
    $user    = createUserWithCompany();
    $company = $user->currentCompany;

    $result  = $this->service->generateBatch(batchParams(['plan_id' => $this->plan->id, 'quantity' => 1]));
    $code    = $result['vouchers'][0]->code;

    $license = $this->service->redeem($code, $company->id, $user->id);

    expect($license->status)->toBe('active')
        ->and($license->type)->toBe('paid')
        ->and($license->user_id)->toBe($user->id);
});

it('redeem marks voucher as used', function () {
    $user    = createUserWithCompany();
    $company = $user->currentCompany;

    $result  = $this->service->generateBatch(batchParams(['plan_id' => $this->plan->id, 'quantity' => 1]));
    $code    = $result['vouchers'][0]->code;

    $this->service->redeem($code, $company->id, $user->id);

    $voucher = PrepaidVoucher::where('code', $code)->first();

    expect($voucher->status)->toBe('used')
        ->and($voucher->used_at)->not->toBeNull()
        ->and($voucher->used_by_user_id)->toBe($user->id)
        ->and($voucher->used_by_company_id)->toBe($company->id);
});

it('redeem prevents double use', function () {
    $user    = createUserWithCompany();
    $company = $user->currentCompany;

    $result  = $this->service->generateBatch(batchParams(['plan_id' => $this->plan->id, 'quantity' => 1]));
    $code    = $result['vouchers'][0]->code;

    $this->service->redeem($code, $company->id, $user->id);

    expect(fn () => $this->service->redeem($code, $company->id, $user->id))
        ->toThrow(\RuntimeException::class);
});

// ── Export CSV ───────────────────────────────────────────────────────────────

it('admin can export batch csv', function () {
    $admin   = createUserWithCompany(['is_superadmin' => true]);
    $result  = $this->service->generateBatch(batchParams(['quantity' => 3, 'created_by' => $admin->id]));
    $batchRef = $result['batch_ref'];

    $this->actingAs($admin)
        ->get(route('admin.vouchers.export', $batchRef))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
});

it('csv contains all codes in batch', function () {
    $result   = $this->service->generateBatch(batchParams(['quantity' => 3]));
    $batchRef = $result['batch_ref'];

    $csv = $this->service->exportBatchCsv($batchRef);

    expect(substr_count($csv, 'IBIG-'))->toBe(3);
});

// ── Admin annulation ─────────────────────────────────────────────────────────

it('admin can cancel individual voucher', function () {
    $admin   = createUserWithCompany(['is_superadmin' => true]);
    $result  = $this->service->generateBatch(batchParams(['quantity' => 1, 'created_by' => $admin->id]));
    $voucher = $result['vouchers'][0];

    $this->actingAs($admin)
        ->delete(route('admin.vouchers.cancel', $voucher->id))
        ->assertRedirect();

    expect($voucher->fresh()->status)->toBe('cancelled');
});

// ── Sécurité routes ──────────────────────────────────────────────────────────

it('admin voucher index is forbidden for non-superadmin', function () {
    $user = createUserWithCompany();

    $this->actingAs($user)
        ->get(route('admin.vouchers.index'))
        ->assertForbidden();
});

it('admin voucher index is accessible for superadmin', function () {
    $admin = createUserWithCompany(['is_superadmin' => true]);

    $this->actingAs($admin)
        ->get(route('admin.vouchers.index'))
        ->assertOk();
});
