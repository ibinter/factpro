<?php

use App\Models\License;
use App\Models\Order;
use App\Models\PaymentAuditLog;
use App\Models\PaymentTransaction;
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

/*
|--------------------------------------------------------------------------
| Accès
|--------------------------------------------------------------------------
*/

it('superadmin can view payment queue', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.payment-queue'))
        ->assertOk();
});

it('regular user cannot access payment queue', function () {
    $this->actingAs($this->client)
        ->get(route('admin.payment-queue'))
        ->assertForbidden();
});

it('financial dashboard shows correct totals', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.financial-dashboard'))
        ->assertOk();
});

it('license manager lists all licenses', function () {
    $plan = Plan::where('code', 'pro')->firstOrFail();
    License::create([
        'user_id' => $this->client->id,
        'plan_id' => $plan->id,
        'license_key' => 'FP-TEST-MNGR-0001-ABCD',
        'type' => 'paid',
        'status' => 'active',
        'starts_at' => now()->subMonth(),
        'ends_at' => now()->addMonth(),
        'activation_source' => 'manual',
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.license-manager'))
        ->assertOk();
});

/*
|--------------------------------------------------------------------------
| Validation de paiement
|--------------------------------------------------------------------------
*/

it('superadmin can validate payment and activate license', function () {
    $response = $this->actingAs($this->admin)
        ->from(route('admin.payments'))
        ->post(route('admin.payments.validate', $this->transaction), [
            'amount_received' => 10000,
            'note' => 'Reçu Orange Money conforme',
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect($this->transaction->fresh()->status)->toBe('manually_validated');
    expect($this->order->fresh()->status)->toBe('paid');

    $license = License::where('user_id', $this->client->id)->where('type', 'paid')->first();
    expect($license)->not->toBeNull();
    expect($license->status)->toBe('active');
});

it('superadmin can reject payment with reason', function () {
    $response = $this->actingAs($this->admin)
        ->from(route('admin.payments'))
        ->post(route('admin.payments.reject', $this->transaction), [
            'reason' => 'Montant insuffisant, référence introuvable',
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect($this->transaction->fresh()->status)->toBe('rejected');
});

it('rejects payment rejection without reason (422)', function () {
    $this->actingAs($this->admin)
        ->from(route('admin.payments'))
        ->post(route('admin.payments.reject', $this->transaction), [])
        ->assertSessionHasErrors('reason');

    expect($this->transaction->fresh()->status)->toBe('under_review');
});

/*
|--------------------------------------------------------------------------
| Complément
|--------------------------------------------------------------------------
*/

it('superadmin can request proof complement', function () {
    $response = $this->actingAs($this->admin)
        ->from(route('admin.payment-queue'))
        ->post(route('admin.payments.complement', $this->transaction), [
            'complement_note' => 'Veuillez fournir un reçu plus lisible.',
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect($this->transaction->fresh()->status)->toBe('missing_info');

    $log = PaymentAuditLog::where('action', 'complement_requested')
        ->where('entity_id', $this->transaction->id)
        ->first();
    expect($log)->not->toBeNull();
});

it('requires complement note to request complement', function () {
    $this->actingAs($this->admin)
        ->from(route('admin.payment-queue'))
        ->post(route('admin.payments.complement', $this->transaction), [])
        ->assertSessionHasErrors('complement_note');
});

/*
|--------------------------------------------------------------------------
| Activation provisoire
|--------------------------------------------------------------------------
*/

it('superadmin can activate provisional license', function () {
    $response = $this->actingAs($this->admin)
        ->from(route('admin.payment-queue'))
        ->post(route('admin.orders.provisional', $this->order), [
            'motif' => 'Virement en cours de traitement',
            'days' => 14,
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $license = License::where('user_id', $this->client->id)
        ->where('status', 'provisional')
        ->first();
    expect($license)->not->toBeNull();
    expect($license->ends_at->diffInDays(now()))->toBeLessThanOrEqual(14);
});

it('requires motif and valid days to activate provisionally', function () {
    $this->actingAs($this->admin)
        ->from(route('admin.payment-queue'))
        ->post(route('admin.orders.provisional', $this->order), [
            'motif' => '',
            'days' => 5,  // invalid — must be 7, 14, or 30
        ])
        ->assertSessionHasErrors(['motif', 'days']);
});

/*
|--------------------------------------------------------------------------
| Confirmation provisoire → active
|--------------------------------------------------------------------------
*/

it('provisional license converts to active on confirmation', function () {
    // Setup : activer provisoirement
    $plan = Plan::where('code', 'pro')->firstOrFail();
    $license = License::create([
        'user_id' => $this->client->id,
        'plan_id' => $plan->id,
        'order_id' => $this->order->id,
        'transaction_id' => $this->transaction->id,
        'license_key' => 'FP-PROV-TEST-1234-ZZZZ',
        'type' => 'paid',
        'status' => 'provisional',
        'starts_at' => now(),
        'ends_at' => now()->addDays(14),
        'activation_source' => 'provisional',
        'activated_by' => $this->admin->id,
    ]);

    $response = $this->actingAs($this->admin)
        ->from(route('admin.license-manager'))
        ->post(route('admin.licenses.confirm-provisional', $license), [
            'reason' => 'Virement reçu et vérifié en banque',
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect($license->fresh()->status)->toBe('active');

    $log = PaymentAuditLog::where('action', 'provisional_confirmed')
        ->where('entity_id', $license->id)
        ->first();
    expect($log)->not->toBeNull();
});

/*
|--------------------------------------------------------------------------
| Gestion des licences
|--------------------------------------------------------------------------
*/

it('superadmin can suspend a license', function () {
    $plan = Plan::where('code', 'pro')->firstOrFail();
    $license = License::create([
        'user_id' => $this->client->id,
        'plan_id' => $plan->id,
        'license_key' => 'FP-SUSP-TEST-1234-ABCD',
        'type' => 'paid',
        'status' => 'active',
        'starts_at' => now()->subMonth(),
        'ends_at' => now()->addMonth(),
        'activation_source' => 'manual',
    ]);

    $this->actingAs($this->admin)
        ->from(route('admin.license-manager'))
        ->post(route('admin.licenses.suspend', $license), ['reason' => 'Impayé signalé'])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($license->fresh()->status)->toBe('suspended');

    expect(
        PaymentAuditLog::where('action', 'license_suspended')->where('entity_id', $license->id)->exists()
    )->toBeTrue();
});

it('superadmin can extend a license with reason', function () {
    $plan = Plan::where('code', 'pro')->firstOrFail();
    $endsAt = now()->addDays(5);
    $license = License::create([
        'user_id' => $this->client->id,
        'plan_id' => $plan->id,
        'license_key' => 'FP-EXTD-TEST-5678-ABCD',
        'type' => 'paid',
        'status' => 'active',
        'starts_at' => now()->subMonth(),
        'ends_at' => $endsAt,
        'activation_source' => 'manual',
    ]);

    $this->actingAs($this->admin)
        ->from(route('admin.license-manager'))
        ->post(route('admin.licenses.extend', $license), ['months' => 3, 'reason' => 'Prolongation geste commercial'])
        ->assertRedirect()
        ->assertSessionHas('success');

    $newEnds = $license->fresh()->ends_at;
    expect($newEnds->isAfter($endsAt))->toBeTrue();
    expect((int) abs($newEnds->diffInMonths($endsAt)))->toBeGreaterThanOrEqual(2);
});

/*
|--------------------------------------------------------------------------
| Journal d'audit
|--------------------------------------------------------------------------
*/

it('audit log records all admin actions', function () {
    // Rejet (journalisé via PaymentService)
    $this->actingAs($this->admin)
        ->post(route('admin.payments.reject', $this->transaction), [
            'reason' => 'Test audit log',
        ]);

    expect(
        PaymentAuditLog::where('action', 'payment_rejected')
            ->where('entity_id', $this->transaction->id)
            ->exists()
    )->toBeTrue();
});
