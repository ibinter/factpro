<?php

use App\Models\GatewayConfig;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->user = createUserWithCompanyAndTrial();
    $this->superadmin = User::factory()->create(['is_superadmin' => true]);
});

// ── Helpers ─────────────────────────────────────────────────────────────────

function makeOrder(User $user): Order
{
    $plan = Plan::where('code', 'pro')->firstOrFail();

    return createPendingOrder($user, $plan, months: 1);
}

function enableGateway(string $name, array $config = []): GatewayConfig
{
    return GatewayConfig::updateOrCreate(
        ['gateway' => $name],
        ['is_active' => true, 'config' => $config ?: ['api_key' => 'test_key', 'site_id' => '12345', 'secret_key' => 'sk_test', 'secret_hash' => 'my_secret_hash']]
    );
}

// ── Admin: stockage & récupération ──────────────────────────────────────────

it('stores and retrieves gateway config', function () {
    $gc = enableGateway('cinetpay', ['api_key' => 'key123', 'site_id' => '9999']);

    expect($gc->config['api_key'])->toBe('key123')
        ->and($gc->config['site_id'])->toBe('9999')
        ->and($gc->is_active)->toBeTrue();
});

it('only allows superadmin to manage gateways', function () {
    // Utilisateur ordinaire → 403
    $this->actingAs($this->user)
        ->get(route('admin.gateways'))
        ->assertStatus(403);

    // Superadmin → 200
    $this->actingAs($this->superadmin)
        ->get(route('admin.gateways'))
        ->assertStatus(200);
});

it('superadmin can update gateway via PUT', function () {
    $gc = GatewayConfig::updateOrCreate(
        ['gateway' => 'cinetpay'],
        ['is_active' => false, 'config' => []]
    );

    $this->actingAs($this->superadmin)
        ->put(route('admin.gateways.update', $gc), [
            'is_active' => true,
            'config' => ['api_key' => 'new_key', 'site_id' => '8888'],
        ])
        ->assertRedirect();

    $gc->refresh();
    expect($gc->is_active)->toBeTrue()
        ->and($gc->config['api_key'])->toBe('new_key');
});

// ── CinetPay ────────────────────────────────────────────────────────────────

it('initiates cinetpay payment with fake http', function () {
    enableGateway('cinetpay');
    $order = makeOrder($this->user);

    Http::fake([
        'https://api-checkout.cinetpay.com/v2/payment' => Http::response([
            'code' => '201',
            'message' => 'CREATED',
            'data' => ['payment_url' => 'https://cinetpay.com/checkout/abc123'],
        ], 201),
    ]);

    $response = $this->actingAs($this->user)
        ->withHeaders(['X-Inertia' => 'true'])
        ->post(route('billing.cinetpay.initiate', $order));

    // Inertia::location → 409 avec X-Inertia-Location quand header X-Inertia est présent
    $response->assertStatus(409);
    expect($response->headers->get('X-Inertia-Location'))->toContain('cinetpay.com');
});

it('handles cinetpay return and activates license', function () {
    enableGateway('cinetpay');
    $order = makeOrder($this->user);
    $order->update(['status' => 'paid']);

    $this->actingAs($this->user)
        ->get(route('billing.cinetpay.return', $order))
        ->assertRedirect(route('billing.index'));
});

it('handles cinetpay return when payment is pending', function () {
    enableGateway('cinetpay');
    $order = makeOrder($this->user);
    // status reste pending_payment

    $this->actingAs($this->user)
        ->get(route('billing.cinetpay.return', $order))
        ->assertRedirect(route('billing.index'));
});

// ── Flutterwave webhook ──────────────────────────────────────────────────────

it('validates flutterwave webhook via secret hash', function () {
    enableGateway('flutterwave', ['secret_hash' => 'my_secret_hash', 'secret_key' => 'sk']);

    // Hash correct → 200
    $this->withHeaders(['verif-hash' => 'my_secret_hash'])
        ->post(route('webhooks.flutterwave'), ['event' => 'charge.completed'])
        ->assertStatus(200)
        ->assertJson(['ok' => true]);

    // Hash incorrect → 400
    $this->withHeaders(['verif-hash' => 'wrong_hash'])
        ->post(route('webhooks.flutterwave'), ['event' => 'charge.completed'])
        ->assertStatus(400);
});

// ── FedaPay ─────────────────────────────────────────────────────────────────

it('initiates fedapay payment', function () {
    enableGateway('fedapay', ['secret_key' => 'sk_sandbox_test']);
    $order = makeOrder($this->user);

    Http::fake([
        'https://api.fedapay.com/v1/transactions' => Http::response([
            'v1/transaction' => ['id' => 42],
        ], 200),
        'https://api.fedapay.com/v1/transactions/42/token' => Http::response([
            'url' => 'https://checkout.fedapay.com/pay/txn_42',
        ], 200),
    ]);

    $response = $this->actingAs($this->user)
        ->withHeaders(['X-Inertia' => 'true'])
        ->post(route('billing.fedapay.initiate', $order));

    $response->assertStatus(409);
    expect($response->headers->get('X-Inertia-Location'))->toContain('fedapay.com');
});

// ── Gateway inactive ─────────────────────────────────────────────────────────

it('rejects initiate when gateway is inactive', function () {
    GatewayConfig::updateOrCreate(
        ['gateway' => 'cinetpay'],
        ['is_active' => false, 'config' => []]
    );

    $order = makeOrder($this->user);

    $this->actingAs($this->user)
        ->post(route('billing.cinetpay.initiate', $order))
        ->assertStatus(404);
});

it('rejects flutterwave initiate when gateway is inactive', function () {
    GatewayConfig::updateOrCreate(
        ['gateway' => 'flutterwave'],
        ['is_active' => false, 'config' => []]
    );

    $order = makeOrder($this->user);

    $this->actingAs($this->user)
        ->post(route('billing.flutterwave.initiate', $order))
        ->assertStatus(404);
});
