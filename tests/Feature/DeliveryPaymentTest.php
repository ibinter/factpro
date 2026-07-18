<?php

use App\Models\DeliveryAgent;
use App\Models\DeliveryOrder;
use App\Models\License;
use App\Models\PaymentTransaction;
use App\Models\Plan;
use App\Models\User;
use App\Services\DeliveryPaymentService;

beforeEach(function () {
    seedPlans();
    $this->user  = createUserWithCompanyAndTrial();
    $this->plan  = Plan::where('code', 'pro')->firstOrFail();
    $this->order = createPendingOrder($this->user, $this->plan, months: 1);
});

// ── Création d'une commande COD ───────────────────────────────────────────

it('can place delivery order with address', function () {
    $response = $this->actingAs($this->user)->post(route('billing.initiate.delivery'), [
        'order_id'     => $this->order->id,
        'contact_name' => 'Kouassi Brou',
        'phone'        => '+22507000000',
        'address'      => 'Cocody Riviera 3, près du carrefour',
        'city'         => 'Abidjan',
        'country'      => 'CI',
    ]);

    $response->assertRedirectContains('/billing/delivery-status/');
    $response->assertSessionHas('success');
});

it('delivery order creates delivery record', function () {
    $this->actingAs($this->user)->post(route('billing.initiate.delivery'), [
        'order_id'     => $this->order->id,
        'contact_name' => 'Kouassi Brou',
        'phone'        => '+22507000000',
        'address'      => 'Cocody Riviera 3',
        'city'         => 'Abidjan',
    ]);

    expect(DeliveryOrder::count())->toBe(1);

    $delivery = DeliveryOrder::firstOrFail();
    expect($delivery->order_id)->toBe($this->order->id)
        ->and($delivery->contact_name)->toBe('Kouassi Brou')
        ->and($delivery->delivery_city)->toBe('Abidjan')
        ->and($delivery->status)->toBe('pending')
        ->and($delivery->confirmation_code)->toHaveLength(6)
        ->and((float) $delivery->cod_amount)->toBe((float) $this->order->total_amount);
});

it('delivery order updates order status to awaiting delivery', function () {
    $this->actingAs($this->user)->post(route('billing.initiate.delivery'), [
        'order_id'     => $this->order->id,
        'contact_name' => 'Kouassi Brou',
        'phone'        => '+22507000000',
        'address'      => 'Cocody Riviera 3',
        'city'         => 'Abidjan',
    ]);

    expect($this->order->fresh()->status)->toBe('awaiting_delivery')
        ->and($this->order->fresh()->payment_method)->toBe('cod');
});

it('delivery status page is accessible to order owner', function () {
    $service = app(DeliveryPaymentService::class);
    $service->createDeliveryOrder($this->order, [
        'contact_name' => 'Test User',
        'phone'        => '+22500000000',
        'address'      => '123 rue test',
        'city'         => 'Abidjan',
    ]);

    $this->actingAs($this->user)
        ->get(route('billing.delivery-status', $this->order))
        ->assertOk()
        ->assertInertia(fn ($p) => $p
            ->component('Billing/DeliveryStatus')
            ->has('delivery')
            ->has('order')
        );
});

// ── Confirmation du paiement ───────────────────────────────────────────────

it('agent can confirm payment received', function () {
    $service = app(DeliveryPaymentService::class);
    $delivery = $service->createDeliveryOrder($this->order, [
        'contact_name' => 'Test User',
        'phone'        => '+22500000000',
        'address'      => '123 rue test',
        'city'         => 'Abidjan',
    ]);

    $admin = createUserWithCompany();
    $license = $service->confirmPaymentReceived($delivery, 10000.0, $admin->id, 'OK reçu');

    expect($delivery->fresh()->status)->toBe('payment_received')
        ->and((float) $delivery->fresh()->amount_received)->toBe(10000.0)
        ->and($delivery->fresh()->payment_confirmed_at)->not->toBeNull();
});

it('confirmation activates license', function () {
    $service = app(DeliveryPaymentService::class);
    $delivery = $service->createDeliveryOrder($this->order, [
        'contact_name' => 'Test User',
        'phone'        => '+22500000000',
        'address'      => '123',
        'city'         => 'Abidjan',
    ]);

    $admin = createUserWithCompany();
    $license = $service->confirmPaymentReceived($delivery, 10000.0, $admin->id);

    expect($license)->toBeInstanceOf(License::class)
        ->and($license->status)->toBe('active')
        ->and($license->user_id)->toBe($this->user->id);
});

it('confirmation creates transaction record', function () {
    $service = app(DeliveryPaymentService::class);
    $delivery = $service->createDeliveryOrder($this->order, [
        'contact_name' => 'Test User',
        'phone'        => '+22500000000',
        'address'      => '123',
        'city'         => 'Abidjan',
    ]);

    $admin = createUserWithCompany();
    $service->confirmPaymentReceived($delivery, 10000.0, $admin->id);

    $tx = PaymentTransaction::where('order_id', $this->order->id)->firstOrFail();
    expect($tx->payment_provider)->toBe('cod')
        ->and($tx->status)->toBe('manually_validated')
        ->and((float) $tx->amount_received)->toBe(10000.0)
        ->and($tx->internal_reference)->toStartWith('COD-');
});

it('prevents double confirmation', function () {
    $service = app(DeliveryPaymentService::class);
    $delivery = $service->createDeliveryOrder($this->order, [
        'contact_name' => 'Test User',
        'phone'        => '+22500000000',
        'address'      => '123',
        'city'         => 'Abidjan',
    ]);

    $admin = User::factory()->create(['is_superadmin' => true]);
    $service->confirmPaymentReceived($delivery, 10000.0, $admin->id);

    // Deuxième tentative via l'admin controller
    $response = $this->actingAs($admin)->post(route('admin.deliveries.confirm', $delivery->id), [
        'amount_received' => 10000,
    ]);

    $response->assertSessionHas('error');
    // Une seule transaction COD doit exister
    expect(PaymentTransaction::where('payment_provider', 'cod')->count())->toBe(1);
});

// ── Admin — assigner un agent ──────────────────────────────────────────────

it('admin can assign delivery to agent', function () {
    $service = app(DeliveryPaymentService::class);
    $delivery = $service->createDeliveryOrder($this->order, [
        'contact_name' => 'Test User',
        'phone'        => '+22500000000',
        'address'      => '123',
        'city'         => 'Abidjan',
    ]);

    $agent = DeliveryAgent::create([
        'name'  => 'Koné Dramane',
        'phone' => '+22501010101',
        'city'  => 'Abidjan',
    ]);

    $admin = createUserWithCompany(['email' => 'superadmin@test.com']);
    $admin->forceFill(['is_superadmin' => true])->save();

    $this->actingAs($admin)->post(route('admin.deliveries.assign', $delivery->id), [
        'delivery_agent_id' => $agent->id,
    ]);

    expect($delivery->fresh()->delivery_agent_id)->toBe($agent->id)
        ->and($delivery->fresh()->status)->toBe('assigned')
        ->and($delivery->fresh()->assigned_at)->not->toBeNull();
});

it('admin can view all pending deliveries', function () {
    $admin = createUserWithCompany(['email' => 'superadmin2@test.com']);
    $admin->forceFill(['is_superadmin' => true])->save();

    $service = app(DeliveryPaymentService::class);
    $service->createDeliveryOrder($this->order, [
        'contact_name' => 'Test User',
        'phone'        => '+22500000000',
        'address'      => '123',
        'city'         => 'Abidjan',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.deliveries.index'))
        ->assertOk()
        ->assertInertia(fn ($p) => $p
            ->component('Admin/DeliveryBoard')
            ->has('deliveries')
            ->has('agents')
        );
});

// ── CRUD agents ──────────────────────────────────────────────────────────

it('delivery agent management crud', function () {
    $admin = createUserWithCompany(['email' => 'agentadmin@test.com']);
    $admin->forceFill(['is_superadmin' => true])->save();

    // Create
    $this->actingAs($admin)->post(route('admin.delivery-agents.store'), [
        'name'  => 'Sékou Touré',
        'phone' => '+22507777777',
        'city'  => 'Abidjan',
    ]);

    expect(DeliveryAgent::count())->toBe(1);
    $agent = DeliveryAgent::firstOrFail();
    expect($agent->name)->toBe('Sékou Touré');

    // Update
    $this->actingAs($admin)->put(route('admin.delivery-agents.update', $agent->id), [
        'is_active' => false,
    ]);
    expect($agent->fresh()->is_active)->toBeFalse();

    // Delete (soft)
    $this->actingAs($admin)->delete(route('admin.delivery-agents.destroy', $agent->id));
    expect(DeliveryAgent::withTrashed()->count())->toBe(1);
    expect(DeliveryAgent::count())->toBe(0);
});
