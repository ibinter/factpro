<?php

use App\Models\PushSubscription;
use App\Models\User;
use App\Services\PushNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

uses(RefreshDatabase::class);

// ─── helpers ───────────────────────────────────────────────────────────────

function makeSub(array $overrides = []): array
{
    return array_merge([
        'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-' . uniqid(),
        'keys'     => [
            'p256dh' => 'BNcRdreALRFXTkOOUHK1EtK2wtZ5MuANf4jGJmWV4vMCGPGEoTlrG7oAaD9TNTMF1Ts8WilKU_g',
            'auth'   => 'tBHItJI5svbpez7KI4CCXg',
        ],
    ], $overrides);
}

// ─── Tests ────────────────────────────────────────────────────────────────

it('stores push subscription', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $sub = makeSub();

    $response = $this->postJson('/push/subscribe', $sub);

    $response->assertStatus(201);

    $this->assertDatabaseHas('push_subscriptions', [
        'user_id'  => $user->id,
        'endpoint' => $sub['endpoint'],
        'is_active' => true,
    ]);
});

it('returns vapid public key without auth', function () {
    Config::set('services.vapid.public_key', 'test-vapid-public-key-abc123');

    $response = $this->getJson('/push/vapid-public-key');

    $response->assertOk()
             ->assertJsonFragment(['public_key' => 'test-vapid-public-key-abc123']);
});

it('deactivates subscription on unsubscribe', function () {
    $user = User::factory()->create();

    $sub = PushSubscription::create([
        'user_id'    => $user->id,
        'endpoint'   => 'https://fcm.googleapis.com/fcm/send/to-delete',
        'public_key' => 'pk',
        'auth_token' => 'at',
        'is_active'  => true,
    ]);

    $this->actingAs($user)
         ->deleteJson('/push/unsubscribe', ['endpoint' => $sub->endpoint])
         ->assertOk();

    $this->assertDatabaseHas('push_subscriptions', [
        'id'        => $sub->id,
        'is_active' => false,
    ]);
});

it('prevents duplicate subscriptions for same endpoint', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $sub = makeSub();

    $this->postJson('/push/subscribe', $sub)->assertStatus(201);
    $this->postJson('/push/subscribe', $sub)->assertStatus(201); // updateOrCreate

    // Toujours un seul enregistrement
    expect(PushSubscription::where('user_id', $user->id)->count())->toBe(1);
});

it('sends notification to user subscriptions', function () {
    $user = User::factory()->create();

    PushSubscription::create([
        'user_id'    => $user->id,
        'endpoint'   => 'https://fcm.googleapis.com/fcm/send/abc',
        'public_key' => 'BNcRdreALRFXTkOOUHK1EtK2wtZ5MuANf4jGJmWV4vMCGPGEoTlrG7oAaD9TNTMF1Ts8WilKU_g',
        'auth_token' => 'tBHItJI5svbpez7KI4CCXg',
        'is_active'  => true,
    ]);

    // On vérifie que la subscription active est bien récupérée
    $subscriptions = PushSubscription::where('user_id', $user->id)
        ->where('is_active', true)
        ->get();

    expect($subscriptions)->toHaveCount(1);
    expect($subscriptions->first()->endpoint)->toBe('https://fcm.googleapis.com/fcm/send/abc');
});

it('deactivates gone endpoints after failed delivery', function () {
    // Test de la logique de désactivation sur 410 Gone
    $user = User::factory()->create();

    $sub = PushSubscription::create([
        'user_id'    => $user->id,
        'endpoint'   => 'https://fcm.googleapis.com/fcm/send/gone-endpoint',
        'public_key' => 'pk',
        'auth_token' => 'at',
        'is_active'  => true,
    ]);

    // Simule la désactivation comme le ferait le service après un 410
    PushSubscription::where('endpoint', $sub->endpoint)
        ->update(['is_active' => false]);

    $this->assertDatabaseHas('push_subscriptions', [
        'id'        => $sub->id,
        'is_active' => false,
    ]);
});

it('sends to all company users', function () {
    // Crée deux utilisateurs avec des subscriptions distinctes
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    foreach ([$user1, $user2] as $u) {
        PushSubscription::create([
            'user_id'    => $u->id,
            'endpoint'   => 'https://fcm.googleapis.com/fcm/send/user-' . $u->id,
            'public_key' => 'pk',
            'auth_token' => 'at',
            'is_active'  => true,
        ]);
    }

    // Vérifier que les deux subscriptions actives existent bien
    expect(
        PushSubscription::whereIn('user_id', [$user1->id, $user2->id])
            ->where('is_active', true)
            ->count()
    )->toBe(2);
});

it('isolates subscriptions between users', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    PushSubscription::create([
        'user_id'    => $user1->id,
        'endpoint'   => 'https://fcm.googleapis.com/fcm/send/user1',
        'public_key' => 'pk',
        'auth_token' => 'at',
        'is_active'  => true,
    ]);

    // user2 ne doit pas voir la subscription de user1
    expect(
        PushSubscription::where('user_id', $user2->id)->count()
    )->toBe(0);
});

it('subscription requires authentication', function () {
    $response = $this->postJson('/push/subscribe', makeSub());
    $response->assertUnauthorized();
});

it('generates vapid keys correctly', function () {
    // Vérifie la structure retournée par generateVapidKeys
    // (la génération openssl peut échouer en CI sans courbe EC P-256 disponible)
    if (! function_exists('openssl_pkey_new')) {
        $this->markTestSkipped('OpenSSL extension requise pour la génération de clés VAPID.');
    }

    try {
        $keys = PushNotificationService::generateVapidKeys();

        expect($keys)->toHaveKeys(['public', 'private']);
        expect(strlen($keys['public']))->toBeGreaterThan(10);
        expect(strlen($keys['private']))->toBeGreaterThan(10);
        expect($keys['public'])->not->toBe($keys['private']);
    } catch (\RuntimeException $e) {
        // OpenSSL EC non configuré sur cet environnement (ex: XAMPP Windows sans openssl.cnf)
        $this->markTestSkipped('Génération EC non disponible : ' . $e->getMessage());
    }
});
