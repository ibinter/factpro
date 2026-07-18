<?php

use App\Models\License;
use App\Models\Plan;
use App\Models\User;

beforeEach(function () {
    seedPlans();

    $this->admin = User::factory()->create(['is_superadmin' => true]);

    $this->client = createUserWithCompanyAndTrial();
    $plan = Plan::where('code', 'pro')->firstOrFail();
    $this->order = createPendingOrder($this->client, $plan, months: 1, attributes: ['status' => 'proof_submitted']);
    $this->transaction = createUnderReviewTransaction($this->order);
    $this->proof = createProofFor($this->transaction);
});

it('returns 403 on the payments console for a non-superadmin user', function () {
    $this->actingAs($this->client)
        ->get(route('admin.payments'))
        ->assertForbidden();
});

it('shows the payments console to a superadmin', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.payments'))
        ->assertOk();
});

it('validates a manual payment: transaction validated, order paid, license activated', function () {
    $response = $this->actingAs($this->admin)
        ->from(route('admin.payments'))
        ->post(route('admin.payments.validate', $this->transaction), [
            'amount_received' => 10000,
            'note' => 'Reçu Orange Money conforme',
        ]);

    $response->assertRedirect(route('admin.payments'));
    $response->assertSessionHas('success');

    $transaction = $this->transaction->fresh();
    expect($transaction->status)->toBe('manually_validated')
        ->and((float) $transaction->amount_received)->toBe(10000.00)
        ->and($transaction->validated_by)->toBe($this->admin->id)
        ->and($transaction->confirmed_at)->not->toBeNull();

    expect($this->order->fresh()->status)->toBe('paid');
    expect($this->proof->fresh()->verification_status)->toBe('approved');

    $license = License::where('user_id', $this->client->id)
        ->where('type', 'paid')
        ->firstOrFail();
    expect($license->status)->toBe('active')
        ->and($license->transaction_id)->toBe($this->transaction->id)
        ->and($license->activated_by)->toBe($this->admin->id)
        ->and($license->isUsable())->toBeTrue();
});

it('refuses a rejection without a reason (mandatory)', function () {
    $this->actingAs($this->admin)
        ->from(route('admin.payments'))
        ->post(route('admin.payments.reject', $this->transaction), [])
        ->assertSessionHasErrors('reason');

    expect($this->transaction->fresh()->status)->toBe('under_review');
});

it('rejects a manual payment with a reason: transaction and order rejected', function () {
    $response = $this->actingAs($this->admin)
        ->from(route('admin.payments'))
        ->post(route('admin.payments.reject', $this->transaction), [
            'reason' => 'Référence introuvable côté opérateur',
        ]);

    $response->assertRedirect(route('admin.payments'));

    $transaction = $this->transaction->fresh();
    expect($transaction->status)->toBe('rejected')
        ->and($transaction->rejection_reason)->toBe('Référence introuvable côté opérateur');

    expect($this->order->fresh()->status)->toBe('rejected');
    expect($this->proof->fresh()->verification_status)->toBe('rejected');
    expect(License::where('user_id', $this->client->id)->where('type', 'paid')->count())->toBe(0);
});
