<?php

use App\Models\License;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\Plan;
use App\Models\User;
use App\Services\LicenseService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

beforeEach(function () {
    seedPlans();
});

// ─── Webhook Mobile Money ────────────────────────────────────────────────────

it('mobile money webhook activates license on success', function () {
    $user = User::factory()->create();
    $plan = Plan::where('code', 'pro')->firstOrFail();
    $order = createPendingOrder($user, $plan, months: 1);
    $transaction = createUnderReviewTransaction($order, [
        'payment_provider'   => 'wave',
        'internal_reference' => 'FP-TEST-WEBHOOK01',
        'status'             => 'pending',
    ]);

    config(['services.wave.secret' => 'webhook-secret']);

    $payload   = [
        'status'      => 'SUCCESSFUL',
        'amount'      => (float) $order->total_amount,
        'external_id' => 'FP-TEST-WEBHOOK01',
        'id'          => 'wave-txn-abc',
    ];
    $signature = hash_hmac('sha256', json_encode($payload), 'webhook-secret');

    $response = $this->postJson(
        route('mobile-money.webhook', ['driver' => 'wave']),
        $payload,
        ['X-Signature' => $signature]
    );

    $response->assertOk()->assertJsonPath('status', 'processed');

    expect($transaction->fresh()->status)->toBe('succeeded');
    expect($order->fresh()->status)->toBe('paid');
    expect(License::where('user_id', $user->id)->where('status', 'active')->exists())->toBeTrue();
});

it('mobile money webhook is idempotent on duplicate', function () {
    $user = User::factory()->create();
    $plan = Plan::where('code', 'pro')->firstOrFail();
    $order = createPendingOrder($user, $plan, months: 1);
    $transaction = createUnderReviewTransaction($order, [
        'payment_provider'   => 'wave',
        'internal_reference' => 'FP-TEST-WEBHOOK02',
        'status'             => 'succeeded',
    ]);

    config(['services.wave.secret' => 'webhook-secret']);

    $payload   = [
        'status'      => 'SUCCESSFUL',
        'amount'      => (float) $order->total_amount,
        'external_id' => 'FP-TEST-WEBHOOK02',
    ];
    $signature = hash_hmac('sha256', json_encode($payload), 'webhook-secret');

    $response = $this->postJson(
        route('mobile-money.webhook', ['driver' => 'wave']),
        $payload,
        ['X-Signature' => $signature]
    );

    $response->assertOk()->assertJsonPath('status', 'already_processed');

    // Aucune licence en double ne doit être créée
    expect(License::where('user_id', $user->id)->count())->toBe(0);
});

it('mobile money webhook flags amount mismatch without rejecting', function () {
    $user = User::factory()->create();
    $plan = Plan::where('code', 'pro')->firstOrFail();
    $order = createPendingOrder($user, $plan, months: 1);
    $transaction = createUnderReviewTransaction($order, [
        'payment_provider'   => 'wave',
        'internal_reference' => 'FP-TEST-WEBHOOK03',
        'status'             => 'pending',
        'amount_expected'    => 10000,
    ]);

    config(['services.wave.secret' => 'webhook-secret']);

    // Montant reçu : 500 XOF de moins (écart > 1)
    $payload = [
        'status'      => 'SUCCESSFUL',
        'amount'      => 9499,
        'external_id' => 'FP-TEST-WEBHOOK03',
    ];
    $signature = hash_hmac('sha256', json_encode($payload), 'webhook-secret');

    $response = $this->postJson(
        route('mobile-money.webhook', ['driver' => 'wave']),
        $payload,
        ['X-Signature' => $signature]
    );

    $response->assertOk()->assertJsonPath('status', 'flagged');

    // La transaction doit passer en under_review, pas succeeded
    expect($transaction->fresh()->status)->toBe('under_review');
    // Aucune licence ne doit être activée
    expect(License::where('user_id', $user->id)->exists())->toBeFalse();
});

it('mobile money webhook rejects invalid signature', function () {
    config(['services.wave.secret' => 'webhook-secret']);

    $response = $this->postJson(
        route('mobile-money.webhook', ['driver' => 'wave']),
        ['status' => 'SUCCESSFUL', 'amount' => 5000, 'external_id' => 'REF-XYZ'],
        ['X-Signature' => 'bad-signature']
    );

    $response->assertStatus(400);
});

// ─── Activation provisoire ────────────────────────────────────────────────────

it('license can be activated provisionally by admin', function () {
    $user  = User::factory()->create();
    $admin = User::factory()->create();
    $plan  = Plan::where('code', 'pro')->firstOrFail();
    $order = createPendingOrder($user, $plan, months: 1);
    $transaction = createUnderReviewTransaction($order);

    $service = app(LicenseService::class);
    $license = $service->activateProvisionally($order, $transaction, $admin, 'Virement en cours de confirmation', 7);

    expect($license->status)->toBe('provisional')
        ->and($license->user_id)->toBe($user->id)
        ->and($license->plan_id)->toBe($plan->id)
        ->and($license->ends_at->isFuture())->toBeTrue()
        ->and($license->metadata['provisional_reason'])->toBe('Virement en cours de confirmation');
});

it('provisional license converts to active on confirmation', function () {
    $user  = User::factory()->create();
    $admin = User::factory()->create();
    $plan  = Plan::where('code', 'pro')->firstOrFail();
    $order = createPendingOrder($user, $plan, months: 1);
    $transaction = createUnderReviewTransaction($order);

    $service = app(LicenseService::class);
    $provisional = $service->activateProvisionally($order, $transaction, $admin, 'Test confirmation', 7);

    $confirmed = $service->confirmProvisional($provisional, $transaction, $admin);

    expect($confirmed->status)->toBe('active')
        ->and($confirmed->ends_at->isFuture())->toBeTrue()
        ->and($transaction->fresh()->status)->toBe('manually_validated');
});

it('activateProvisionally throws if active license already exists', function () {
    $user  = User::factory()->create();
    $admin = User::factory()->create();
    $plan  = Plan::where('code', 'pro')->firstOrFail();
    $order = createPendingOrder($user, $plan, months: 1);
    $transaction = createUnderReviewTransaction($order);

    // Créer une licence active d'abord
    License::create([
        'user_id'           => $user->id,
        'plan_id'           => $plan->id,
        'license_key'       => 'FP-TEST-AAAA-BBBB-CCCC',
        'type'              => 'paid',
        'status'            => 'active',
        'starts_at'         => now(),
        'ends_at'           => now()->addMonth(),
        'limits'            => $plan->limits,
        'activation_source' => 'manual',
    ]);

    $service = app(LicenseService::class);

    expect(fn () => $service->activateProvisionally($order, $transaction, $admin, 'Test'))
        ->toThrow(\RuntimeException::class);
});

// ─── submitProof — méthodes de paiement ──────────────────────────────────────

it('submit proof accepts cash payment method', function () {
    Storage::fake('local');

    $user  = User::factory()->create();
    $plan  = Plan::where('code', 'pro')->firstOrFail();
    $order = createPendingOrder($user, $plan, months: 1);

    $file = UploadedFile::fake()->create('recu.pdf', 100, 'application/pdf');

    $response = $this->actingAs($user)->post(route('billing.proof', $order), [
        'provider'           => 'cash',
        'sender_name'        => 'Jean Dupont',
        'sender_number'      => null,
        'provider_reference' => 'CASH-001',
        'amount_declared'    => $order->total_amount,
        'proof'              => $file,
    ]);

    // Doit rediriger (succès) et non renvoyer d'erreur de validation
    $response->assertRedirect();
    $response->assertSessionMissing('errors');
});

it('submit proof accepts transfer service method', function () {
    Storage::fake('local');

    $user  = User::factory()->create();
    $plan  = Plan::where('code', 'pro')->firstOrFail();
    $order = createPendingOrder($user, $plan, months: 1);

    $file = UploadedFile::fake()->create('recu.jpg', 50, 'image/jpeg');

    $response = $this->actingAs($user)->post(route('billing.proof', $order), [
        'provider'           => 'transfer_service',
        'sender_name'        => 'Marie Martin',
        'sender_number'      => null,
        'provider_reference' => 'TRANSFER-001',
        'amount_declared'    => $order->total_amount,
        'proof'              => $file,
    ]);

    $response->assertRedirect();
    $response->assertSessionMissing('errors');
});
