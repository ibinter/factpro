<?php

use App\Models\Order;
use App\Models\PaymentProof;
use App\Models\PaymentTransaction;
use App\Models\Plan;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->user = createUserWithCompanyAndTrial();
});

it('creates a pending_payment order (10 000 XOF for pro monthly) and redirects to checkout', function () {
    $response = $this->actingAs($this->user)->post(route('billing.subscribe'), [
        'plan_code' => 'pro',
        'months' => 1,
    ]);

    expect(Order::count())->toBe(1);

    $order = Order::firstOrFail();
    expect($order->status)->toBe('pending_payment')
        ->and($order->user_id)->toBe($this->user->id)
        ->and($order->duration_months)->toBe(1)
        ->and((float) $order->total_amount)->toBe(10000.00)
        ->and($order->currency)->toBe('XOF')
        ->and($order->expires_at->isFuture())->toBeTrue();

    $response->assertRedirect(route('billing.checkout', $order));
});

it('reuses the same payable order on an identical double submit (anti double-click)', function () {
    $payload = ['plan_code' => 'pro', 'months' => 1];

    $this->actingAs($this->user)->post(route('billing.subscribe'), $payload);
    $this->actingAs($this->user)->post(route('billing.subscribe'), $payload);

    expect(Order::count())->toBe(1);
});

it('applies the annual discount: 12 months billed as 10', function () {
    $this->actingAs($this->user)->post(route('billing.subscribe'), [
        'plan_code' => 'pro',
        'months' => 12,
    ]);

    expect((float) Order::firstOrFail()->total_amount)->toBe(100000.00);
});

it('stores a manual payment proof privately and moves the order to proof_submitted', function () {
    Storage::fake('local');

    $plan = Plan::where('code', 'pro')->firstOrFail();
    $order = createPendingOrder($this->user, $plan, months: 1);

    $response = $this->actingAs($this->user)->post(route('billing.proof', $order), [
        'provider' => 'orange_money',
        'sender_name' => 'Awa Traoré',
        'sender_number' => '+2250700000000',
        'provider_reference' => 'OM-REF-123456',
        'amount_declared' => 10000,
        'proof' => UploadedFile::fake()->image('recu.jpg'),
    ]);

    $response->assertRedirectContains('/billing/proof-status/');
    $response->assertSessionHas('success');

    expect($order->fresh()->status)->toBe('proof_submitted');

    $transaction = PaymentTransaction::where('order_id', $order->id)->firstOrFail();
    expect($transaction->status)->toBe('under_review')
        ->and($transaction->payment_provider)->toBe('orange_money')
        ->and($transaction->provider_reference)->toBe('OM-REF-123456')
        ->and((float) $transaction->amount_declared)->toBe(10000.00)
        ->and($transaction->internal_reference)->toStartWith('FP-');

    $proof = PaymentProof::where('transaction_id', $transaction->id)->firstOrFail();
    expect($proof->file_hash)->not->toBeNull()
        ->and(strlen($proof->file_hash))->toBe(64)
        ->and($proof->original_filename)->toBe('recu.jpg')
        ->and($proof->file_path)->toStartWith('private/proofs/');

    Storage::disk('local')->assertExists($proof->file_path);
});
