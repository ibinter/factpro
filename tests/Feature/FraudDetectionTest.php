<?php

use App\Models\FraudAlert;
use App\Models\License;
use App\Models\ManualPaymentMethod;
use App\Models\Order;
use App\Models\PaymentProof;
use App\Models\PaymentTransaction;
use App\Models\Plan;
use App\Services\FraudDetectionService;
use App\Services\ManualPaymentMethodService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

// ---------------------------------------------------------------------------
// Helpers locaux
// ---------------------------------------------------------------------------

function fraudOrder(array $attrs = []): Order
{
    seedPlans();
    $user  = createUserWithCompanyAndTrial();
    $plan  = Plan::where('code', 'pro')->firstOrFail();

    return createPendingOrder($user, $plan, 1, $attrs);
}

// ---------------------------------------------------------------------------
// Tests FraudDetectionService
// ---------------------------------------------------------------------------

it('detects duplicate reference', function () {
    $order1 = fraudOrder();
    $order2 = fraudOrder();

    // Une transaction avec cette référence existe déjà pour order1
    PaymentTransaction::create([
        'order_id'           => $order1->id,
        'user_id'            => $order1->user_id,
        'payment_provider'   => 'orange_money',
        'provider_reference' => 'REF-DUPLICATE-001',
        'internal_reference' => 'FP-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)),
        'amount_expected'    => $order1->total_amount,
        'currency'           => 'XOF',
        'status'             => 'under_review',
        'initiated_at'       => now(),
    ]);

    $service = app(FraudDetectionService::class);
    $result  = $service->analyze($order2, ['transaction_reference' => 'REF-DUPLICATE-001']);

    expect($result['flags'])->toContain('duplicate_reference')
        ->and($result['score'])->toBeGreaterThanOrEqual(FraudDetectionService::SIGNALS['duplicate_reference']);
});

it('detects duplicate proof hash', function () {
    $order1 = fraudOrder();
    $order2 = fraudOrder();

    $tx = PaymentTransaction::create([
        'order_id'           => $order1->id,
        'user_id'            => $order1->user_id,
        'payment_provider'   => 'orange_money',
        'provider_reference' => 'REF-' . strtoupper(Str::random(8)),
        'internal_reference' => 'FP-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)),
        'amount_expected'    => $order1->total_amount,
        'currency'           => 'XOF',
        'status'             => 'under_review',
        'initiated_at'       => now(),
    ]);

    $hash = hash('sha256', 'fake-proof-content');

    PaymentProof::create([
        'transaction_id'   => $tx->id,
        'original_filename' => 'proof.jpg',
        'stored_filename'  => 'proof_stored.jpg',
        'file_path'        => 'proofs/proof_stored.jpg',
        'mime_type'        => 'image/jpeg',
        'file_size'        => 1024,
        'file_hash'        => $hash,
        'uploaded_by'      => $order1->user_id,
        'verification_status' => 'pending',
    ]);

    $service = app(FraudDetectionService::class);
    $result  = $service->analyze($order2, [], $hash);

    expect($result['flags'])->toContain('duplicate_proof_hash')
        ->and($result['score'])->toBeGreaterThanOrEqual(FraudDetectionService::SIGNALS['duplicate_proof_hash']);
});

it('detects amount mismatch above 5 percent', function () {
    $order   = fraudOrder();  // total_amount = 10 000 XOF
    $service = app(FraudDetectionService::class);

    // Déclare 8 000 XOF alors qu'on attend 10 000 XOF → écart de 20 %
    $result = $service->analyze($order, ['declared_amount' => 8000.00]);

    expect($result['flags'])->toContain('amount_mismatch')
        ->and($result['score'])->toBeGreaterThanOrEqual(FraudDetectionService::SIGNALS['amount_mismatch']);
});

it('does not flag amount mismatch within 5 percent tolerance', function () {
    $order   = fraudOrder();  // total_amount = 10 000 XOF
    $service = app(FraudDetectionService::class);

    // Déclare 9 950 XOF → écart de 0,5 % < seuil
    $result = $service->analyze($order, ['declared_amount' => 9950.00]);

    expect($result['flags'])->not->toContain('amount_mismatch');
});

it('flags new account under 24 hours', function () {
    seedPlans();

    // Crée un utilisateur avec created_at = il y a 1 heure
    $user  = createUserWithCompanyAndTrial(['created_at' => now()->subHour()]);
    $plan  = Plan::where('code', 'pro')->firstOrFail();
    $order = createPendingOrder($user, $plan);

    $service = app(FraudDetectionService::class);
    $result  = $service->analyze($order, []);

    expect($result['flags'])->toContain('suspicious_new_account');
});

it('flags multiple pending orders', function () {
    $order = fraudOrder();

    $plan = Plan::where('code', 'pro')->firstOrFail();

    // 2 autres commandes en attente pour le même utilisateur
    createPendingOrder($order->user, $plan, 1);
    createPendingOrder($order->user, $plan, 1);

    $service = app(FraudDetectionService::class);
    $result  = $service->analyze($order, []);

    expect($result['flags'])->toContain('multiple_pending')
        ->and($result['score'])->toBeGreaterThanOrEqual(FraudDetectionService::SIGNALS['multiple_pending']);
});

it('creates fraud alert when score above 1', function () {
    $order = fraudOrder();

    // Deux signaux = score 4 (duplicate_reference=3 + amount_mismatch=2 → avec un seul déjà > 1)
    $plan = Plan::where('code', 'pro')->firstOrFail();

    PaymentTransaction::create([
        'order_id'           => fraudOrder()->id,
        'user_id'            => $order->user_id,
        'payment_provider'   => 'orange_money',
        'provider_reference' => 'REF-ALERT-TRIGGER',
        'internal_reference' => 'FP-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)),
        'amount_expected'    => $order->total_amount,
        'currency'           => 'XOF',
        'status'             => 'under_review',
        'initiated_at'       => now(),
    ]);

    expect(FraudAlert::count())->toBe(0);

    $service = app(FraudDetectionService::class);
    $service->analyze($order, [
        'transaction_reference' => 'REF-ALERT-TRIGGER',
        'declared_amount'       => 5000.00,  // mismatch > 5% → +2
    ]);

    expect(FraudAlert::count())->toBeGreaterThanOrEqual(1);

    $alert = FraudAlert::latest()->first();
    expect($alert->order_id)->toBe($order->id)
        ->and($alert->status)->toBe('open')
        ->and($alert->score)->toBeGreaterThan(1);
});

it('sends admin email when score is critical', function () {
    Mail::fake();

    config(['factpro.fraud_alert_email' => 'admin@factpro.test']);

    $order = fraudOrder();

    // Référence dupliquée (score 3 = critique)
    PaymentTransaction::create([
        'order_id'           => fraudOrder()->id,
        'user_id'            => $order->user_id,
        'payment_provider'   => 'wave',
        'provider_reference' => 'REF-CRITICAL-MAIL',
        'internal_reference' => 'FP-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)),
        'amount_expected'    => $order->total_amount,
        'currency'           => 'XOF',
        'status'             => 'under_review',
        'initiated_at'       => now(),
    ]);

    $service = app(FraudDetectionService::class);
    $service->analyze($order, ['transaction_reference' => 'REF-CRITICAL-MAIL']);

    Mail::assertSent(\App\Mail\FraudAlertMail::class, fn (\App\Mail\FraudAlertMail $mail) => $mail->score >= 3);
});

// ---------------------------------------------------------------------------
// Tests ManualPaymentMethodService — filtrage
// ---------------------------------------------------------------------------

it('returns available manual methods filtered by country', function () {
    ManualPaymentMethod::create(['type' => 'mobile_money', 'label' => 'Wave CI',    'country' => 'CI', 'currency' => 'XOF', 'is_active' => true,  'sort_order' => 0]);
    ManualPaymentMethod::create(['type' => 'mobile_money', 'label' => 'Wave SN',    'country' => 'SN', 'currency' => 'XOF', 'is_active' => true,  'sort_order' => 0]);
    ManualPaymentMethod::create(['type' => 'mobile_money', 'label' => 'Wave Global','country' => null,  'currency' => 'XOF', 'is_active' => true,  'sort_order' => 1]);

    $service = app(ManualPaymentMethodService::class);
    $methods = $service->getAvailableMethods('CI', 10000, 'XOF');

    $names = collect($methods)->flatten(1)->pluck('label')->toArray();

    expect($names)->toContain('Wave CI')
        ->and($names)->toContain('Wave Global')
        ->and($names)->not->toContain('Wave SN');
});

it('returns available manual methods filtered by amount range', function () {
    ManualPaymentMethod::create(['type' => 'transfer_service', 'label' => 'WU Low',    'is_active' => true, 'sort_order' => 0, 'min_amount' => 500,    'max_amount' => 5000,   'currency' => 'XOF']);
    ManualPaymentMethod::create(['type' => 'transfer_service', 'label' => 'WU High',   'is_active' => true, 'sort_order' => 1, 'min_amount' => 10000,  'max_amount' => 500000, 'currency' => 'XOF']);
    ManualPaymentMethod::create(['type' => 'transfer_service', 'label' => 'WU All',    'is_active' => true, 'sort_order' => 2, 'min_amount' => null,   'max_amount' => null,   'currency' => 'XOF']);

    $service = app(ManualPaymentMethodService::class);
    $methods = $service->getAvailableMethods('CI', 3000, 'XOF');

    $names = collect($methods)->flatten(1)->pluck('label')->toArray();

    expect($names)->toContain('WU Low')
        ->and($names)->toContain('WU All')
        ->and($names)->not->toContain('WU High');
});

it('manual payment method seed creates defaults', function () {
    expect(ManualPaymentMethod::count())->toBe(0);

    $service = app(ManualPaymentMethodService::class);
    $service->seedDefaults();

    expect(ManualPaymentMethod::count())->toBeGreaterThanOrEqual(10);

    // Les méthodes créées par défaut sont inactives (le superadmin les active manuellement)
    expect(ManualPaymentMethod::where('is_active', true)->count())->toBe(0);

    // Idempotent : un deuxième appel ne crée pas de doublons
    $countBefore = ManualPaymentMethod::count();
    $service->seedDefaults();
    expect(ManualPaymentMethod::count())->toBe($countBefore);
});

// ---------------------------------------------------------------------------
// Tests des risk levels
// ---------------------------------------------------------------------------

it('returns low risk level when score is zero', function () {
    $service = app(FraudDetectionService::class);
    expect($service->riskLevel(0))->toBe('low');
});

it('returns medium risk level when score is 1 or 2', function () {
    $service = app(FraudDetectionService::class);
    expect($service->riskLevel(1))->toBe('medium');
    expect($service->riskLevel(2))->toBe('medium');
});

it('returns high risk level when score is 3 or more', function () {
    $service = app(FraudDetectionService::class);
    expect($service->riskLevel(3))->toBe('high');
    expect($service->riskLevel(10))->toBe('high');
});
