<?php

use App\Services\MobileMoney\MobileMoneyManager;
use App\Services\MobileMoney\MtnMomoService;
use App\Services\MobileMoney\OrangeMoneyService;
use App\Services\MobileMoney\WaveService;
use Illuminate\Support\Facades\Http;

// ─── Détection de driver ───────────────────────────────────────────────────

it('detects wave driver for senegal number', function () {
    $manager = new MobileMoneyManager();
    $driver  = $manager->detectDriver('+221701234567', 'SN');
    expect($driver)->toBe('wave');
});

it('detects mtn driver for ci number', function () {
    $manager = new MobileMoneyManager();
    $driver  = $manager->detectDriver('+2250512345678', 'CI');
    expect($driver)->toBe('mtn_momo');
});

it('detects orange money from prefix', function () {
    $manager = new MobileMoneyManager();
    $driver  = $manager->detectDriver('+2250712345678', 'CI');
    expect($driver)->toBe('orange_money');
});

it('returns null for unknown prefix', function () {
    $manager = new MobileMoneyManager();
    $driver  = $manager->detectDriver('+22599999999', 'CI');
    expect($driver)->toBeNull();
});

// ─── Wave ─────────────────────────────────────────────────────────────────

it('initiates wave payment with fake http', function () {
    Http::fake([
        'https://api.wave.com/v1/checkout/sessions' => Http::response([
            'wave_launch_url' => 'https://pay.wave.com/m/test/c/abc123',
            'id'              => 'wave-session-abc123',
        ], 200),
    ]);

    config(['services.wave.api_key' => 'test-wave-key']);

    $service = new WaveService();
    $result  = $service->initiate('+221701234567', 5000.0, 'XOF', 'REF-001', 'Test paiement');

    expect($result['checkout_url'])->toBe('https://pay.wave.com/m/test/c/abc123');
    expect($result['reference'])->toBe('wave-session-abc123');
    expect($result['status'])->toBe('pending');
    expect($result)->toHaveKey('instructions');
});

it('checks wave payment status', function () {
    Http::fake([
        'https://api.wave.com/v1/checkout/sessions/wave-session-abc123' => Http::response([
            'payment_status' => 'succeeded',
            'amount'         => '5000',
        ], 200),
    ]);

    config(['services.wave.api_key' => 'test-wave-key']);

    $service = new WaveService();
    $result  = $service->checkStatus('wave-session-abc123');

    expect($result['paid'])->toBeTrue();
    expect($result['status'])->toBe('paid');
});

// ─── MTN MoMo ─────────────────────────────────────────────────────────────

it('initiates mtn momo payment with fake http', function () {
    Http::fake([
        'https://sandbox.momodeveloper.mtn.com/collection/token/' => Http::response([
            'access_token' => 'fake-mtn-token',
            'token_type'   => 'access_token',
        ], 200),
        'https://sandbox.momodeveloper.mtn.com/collection/v1_0/requesttopay' => Http::response(null, 202),
    ]);

    config([
        'services.mtn_momo.subscription_key' => 'sub-key',
        'services.mtn_momo.api_user'         => 'api-user',
        'services.mtn_momo.api_key'          => 'api-key',
        'services.mtn_momo.environment'      => 'sandbox',
    ]);

    $service = new MtnMomoService();
    $result  = $service->initiate('+2250512345678', 1000.0, 'XOF', 'MTN-REF-001', 'Test MTN');

    expect($result['status'])->toBe('pending');
    expect($result['reference'])->toBe('MTN-REF-001');
    expect($result)->toHaveKey('instructions');
});

it('checks payment status via mtn momo', function () {
    Http::fake([
        'https://sandbox.momodeveloper.mtn.com/collection/token/' => Http::response([
            'access_token' => 'fake-token',
        ], 200),
        'https://sandbox.momodeveloper.mtn.com/collection/v1_0/requesttopay/*' => Http::response([
            'status'                 => 'SUCCESSFUL',
            'amount'                 => '1000',
            'financialTransactionId' => 'TXN-123',
        ], 200),
    ]);

    config([
        'services.mtn_momo.subscription_key' => 'sub-key',
        'services.mtn_momo.api_user'         => 'api-user',
        'services.mtn_momo.api_key'          => 'api-key',
        'services.mtn_momo.environment'      => 'sandbox',
    ]);

    $service = new MtnMomoService();
    $result  = $service->checkStatus('MTN-REF-001');

    expect($result['paid'])->toBeTrue();
    expect($result['status'])->toBe('paid');
});

// ─── Webhooks ─────────────────────────────────────────────────────────────

it('validates wave webhook signature', function () {
    config(['services.wave.secret' => 'wave-secret-key']);

    $payload   = ['event' => 'payment.success', 'reference' => 'REF-001'];
    $signature = hash_hmac('sha256', json_encode($payload), 'wave-secret-key');

    $service = new WaveService();
    expect($service->validateWebhook($payload, $signature))->toBeTrue();
});

it('rejects invalid webhook signature', function () {
    config(['services.wave.secret' => 'wave-secret-key']);

    $payload = ['event' => 'payment.success', 'reference' => 'REF-001'];

    $service = new WaveService();
    expect($service->validateWebhook($payload, 'bad-signature'))->toBeFalse();
});

// ─── Manager ──────────────────────────────────────────────────────────────

it('mobile money manager throws for unknown driver', function () {
    $manager = new MobileMoneyManager();
    expect(fn () => $manager->driver('unknown_driver'))
        ->toThrow(\InvalidArgumentException::class, 'Driver inconnu: unknown_driver');
});

it('mobile money manager returns correct driver instances', function () {
    $manager = new MobileMoneyManager();

    expect($manager->driver('wave'))->toBeInstanceOf(WaveService::class);
    expect($manager->driver('orange_money'))->toBeInstanceOf(OrangeMoneyService::class);
    expect($manager->driver('mtn_momo'))->toBeInstanceOf(MtnMomoService::class);
});

// ─── Routes / Controller ──────────────────────────────────────────────────

it('mobile money checkout page loads', function () {
    $user = \App\Models\User::factory()->create();

    $response = $this->actingAs($user)->get(route('mobile-money.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('MobileMoney/Checkout'));
});

it('mobile money detect driver endpoint works', function () {
    $user = \App\Models\User::factory()->create();

    $response = $this->actingAs($user)->postJson(route('mobile-money.detect'), [
        'phone'   => '+221701234567',
        'country' => 'SN',
    ]);

    $response->assertOk()->assertJsonPath('driver', 'wave');
});

it('mobile money webhook returns 400 for invalid signature', function () {
    config(['services.wave.secret' => 'secret-key']);

    $response = $this->postJson(
        route('mobile-money.webhook', ['driver' => 'wave']),
        ['event' => 'payment.success'],
        ['X-Signature' => 'bad-sig']
    );

    $response->assertStatus(400);
});

it('mobile money webhook returns 404 for unknown driver', function () {
    $response = $this->postJson(
        route('mobile-money.webhook', ['driver' => 'bad_driver']),
        ['event' => 'test']
    );

    $response->assertStatus(404);
});
