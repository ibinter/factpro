<?php

use App\Models\ManualPaymentMethod;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    seedPlans();
    $this->user = createUserWithCompanyAndTrial();
    $this->plan = Plan::where('code', 'pro')->firstOrFail();
});

// ─────────────────────────────────────────────────────────
// Soumission de déclaration chèque
// ─────────────────────────────────────────────────────────

it('can submit cheque payment declaration without proof', function () {
    $order = createPendingOrder($this->user, $this->plan, months: 1);

    $response = $this->actingAs($this->user)->post(route('billing.initiate.cheque'), [
        'order_id'        => $order->id,
        'cheque_number'   => '0012345',
        'issuing_bank'    => 'Société Générale CI',
        'account_holder'  => 'Kouassi Jean',
        'declared_amount' => 10000,
        'cheque_date'     => now()->format('Y-m-d'),
    ]);

    $response->assertRedirect(route('billing.proof-status', $order->id));
    $response->assertSessionHas('success');

    expect($order->fresh()->status)->toBe('proof_submitted');

    $tx = PaymentTransaction::where('order_id', $order->id)->firstOrFail();
    expect($tx->payment_provider)->toBe('cheque');
    expect($tx->provider_reference)->toBe('0012345');
});

it('can submit cheque payment declaration with proof', function () {
    Storage::fake('local');

    $order = createPendingOrder($this->user, $this->plan, months: 1);

    $response = $this->actingAs($this->user)->post(route('billing.initiate.cheque'), [
        'order_id'        => $order->id,
        'cheque_number'   => '0099876',
        'issuing_bank'    => 'BIAO-CI',
        'account_holder'  => 'Awa Traoré',
        'declared_amount' => 10000,
        'cheque_date'     => now()->format('Y-m-d'),
        'proof'           => UploadedFile::fake()->image('cheque.jpg'),
    ]);

    $response->assertRedirect(route('billing.proof-status', $order->id));
    expect($order->fresh()->status)->toBe('proof_submitted');

    $tx = PaymentTransaction::where('order_id', $order->id)->firstOrFail();
    expect($tx->proofs()->count())->toBe(1);
});

it('cheque payment validates required fields', function () {
    $order = createPendingOrder($this->user, $this->plan, months: 1);

    $this->actingAs($this->user)
        ->post(route('billing.initiate.cheque'), [
            'order_id' => $order->id,
            // missing: cheque_number, issuing_bank, account_holder, declared_amount, cheque_date
        ])
        ->assertSessionHasErrors(['cheque_number', 'issuing_bank', 'account_holder', 'declared_amount', 'cheque_date']);
});

it('cheque payment requires valid order', function () {
    $this->actingAs($this->user)
        ->post(route('billing.initiate.cheque'), [
            'order_id'        => 99999,
            'cheque_number'   => '0012345',
            'issuing_bank'    => 'Test Bank',
            'account_holder'  => 'John Doe',
            'declared_amount' => 10000,
            'cheque_date'     => now()->format('Y-m-d'),
        ])
        ->assertSessionHasErrors(['order_id']);
});

it('cheque payment is rejected for other user order', function () {
    $otherUser = createUserWithCompanyAndTrial();
    $order = createPendingOrder($otherUser, $this->plan, months: 1);

    $this->actingAs($this->user)
        ->post(route('billing.initiate.cheque'), [
            'order_id'        => $order->id,
            'cheque_number'   => '0012345',
            'issuing_bank'    => 'Test Bank',
            'account_holder'  => 'John Doe',
            'declared_amount' => 10000,
            'cheque_date'     => now()->format('Y-m-d'),
        ])
        ->assertForbidden();
});

// ─────────────────────────────────────────────────────────
// Visibilité dans Checkout
// ─────────────────────────────────────────────────────────

it('cheque method appears in checkout when active', function () {
    ManualPaymentMethod::create([
        'type'            => 'cheque',
        'label'           => 'Chèque bancaire',
        'account_name'    => 'IBIG SOFT SARL',
        'processing_time' => '3-5 jours ouvrables',
        'is_active'       => true,
    ]);

    $order = createPendingOrder($this->user, $this->plan, months: 1);

    $response = $this->actingAs($this->user)->get(route('billing.checkout', $order));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Billing/Checkout'));

    $methods = $response->original->getData()['page']['props']['manualMethods'] ?? [];
    expect(collect($methods)->contains(fn ($m) => $m['type'] === 'cheque'))->toBeTrue();
});

it('cheque method hidden when inactive', function () {
    ManualPaymentMethod::create([
        'type'            => 'cheque',
        'label'           => 'Chèque bancaire',
        'processing_time' => '3-5 jours ouvrables',
        'is_active'       => false,
    ]);

    $order = createPendingOrder($this->user, $this->plan, months: 1);

    $response = $this->actingAs($this->user)->get(route('billing.checkout', $order));
    $response->assertOk();

    $methods = $response->original->getData()['page']['props']['manualMethods'] ?? [];
    expect(collect($methods)->contains(fn ($m) => $m['type'] === 'cheque'))->toBeFalse();
});

// ─────────────────────────────────────────────────────────
// Actions admin
// ─────────────────────────────────────────────────────────

it('admin can validate cheque payment', function () {
    $admin = User::factory()->create(['is_superadmin' => true]);

    $order = createPendingOrder($this->user, $this->plan, months: 1, attributes: ['status' => 'proof_submitted']);
    $transaction = createUnderReviewTransaction($order, attributes: ['payment_provider' => 'cheque']);

    $this->actingAs($admin)
        ->from(route('admin.payments'))
        ->post(route('admin.payments.validate', $transaction), [
            'amount_received' => 10000,
            'note'            => 'Chèque encaissé — référence 0012345',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($transaction->fresh()->status)->toBe('manually_validated');
    expect($order->fresh()->status)->toBe('paid');
});

it('admin can reject cheque with reason', function () {
    $admin = User::factory()->create(['is_superadmin' => true]);

    $order = createPendingOrder($this->user, $this->plan, months: 1, attributes: ['status' => 'proof_submitted']);
    $transaction = createUnderReviewTransaction($order, attributes: ['payment_provider' => 'cheque']);

    $this->actingAs($admin)
        ->from(route('admin.payments'))
        ->post(route('admin.payments.reject', $transaction), [
            'reason' => 'Chèque sans provision — retourné impayé',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($transaction->fresh()->status)->toBe('rejected');
    expect($order->fresh()->status)->toBe('rejected');
});
