<?php

use App\Models\License;
use App\Models\Plan;
use App\Models\User;
use App\Services\AiAssistService;
use App\Services\LicenseService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/*
|--------------------------------------------------------------------------
| Helpers locaux
|--------------------------------------------------------------------------
*/

function aiCreateBusinessLicense(User $user): License
{
    seedPlans();
    $plan = Plan::where('code', 'business')->firstOrFail();

    return License::create([
        'user_id'           => $user->id,
        'plan_id'           => $plan->id,
        'license_key'       => app(LicenseService::class)->generateKey(),
        'type'              => 'paid',
        'status'            => 'active',
        'starts_at'         => now(),
        'ends_at'           => now()->addYear(),
        'limits'            => $plan->limits,
        'activation_source' => 'manual',
    ]);
}

function fakeAnthropicResponse(string $text = 'Description générée par IA'): void
{
    Http::fake([
        'api.anthropic.com/*' => Http::response([
            'content' => [['type' => 'text', 'text' => $text]],
        ], 200),
    ]);
}

/*
|--------------------------------------------------------------------------
| Tests de statut
|--------------------------------------------------------------------------
*/

it('returns ai status endpoint', function () {
    $user = createUserWithCompanyAndTrial();
    aiCreateBusinessLicense($user);

    config(['services.anthropic.api_key' => 'test-key']);

    $this->actingAs($user)
        ->getJson(route('ai.status'))
        ->assertOk()
        ->assertJsonStructure(['available', 'plan_ok']);
});

it('rejects non-business plan with 403', function () {
    // Utilisateur avec un plan trial uniquement (pas business)
    $user = createUserWithCompanyAndTrial();

    config(['services.anthropic.api_key' => 'test-key']);

    $this->actingAs($user)
        ->postJson(route('ai.suggest-description'), ['name' => 'Produit Test'])
        ->assertStatus(403)
        ->assertJson(['error' => 'Fonctionnalité IA réservée au forfait BUSINESS+']);
});

it('returns unavailable when api key not configured', function () {
    $user = createUserWithCompanyAndTrial();
    aiCreateBusinessLicense($user);

    config(['services.anthropic.api_key' => '']);

    $this->actingAs($user)
        ->postJson(route('ai.suggest-description'), ['name' => 'Produit'])
        ->assertOk()
        ->assertJson(['available' => false]);
});

/*
|--------------------------------------------------------------------------
| Tests de suggestion de description
|--------------------------------------------------------------------------
*/

it('suggests product description via mocked http', function () {
    $user = createUserWithCompanyAndTrial();
    aiCreateBusinessLicense($user);

    config(['services.anthropic.api_key' => 'test-key']);
    fakeAnthropicResponse('Description générée par IA');

    $this->actingAs($user)
        ->postJson(route('ai.suggest-description'), ['name' => 'Stylo bille premium'])
        ->assertOk()
        ->assertJson(['description' => 'Description générée par IA']);
});

it('returns cached description on second call', function () {
    $user = createUserWithCompanyAndTrial();
    aiCreateBusinessLicense($user);

    config(['services.anthropic.api_key' => 'test-key']);
    Cache::flush();

    Http::fake([
        'api.anthropic.com/*' => Http::response([
            'content' => [['type' => 'text', 'text' => 'Description mise en cache']],
        ], 200),
    ]);

    // Premier appel
    $this->actingAs($user)
        ->postJson(route('ai.suggest-description'), ['name' => 'Article cache test'])
        ->assertOk()
        ->assertJson(['description' => 'Description mise en cache']);

    // Le second appel doit utiliser le cache (pas de nouvel appel HTTP)
    Http::fake([
        'api.anthropic.com/*' => Http::response([
            'content' => [['type' => 'text', 'text' => 'Nouvelle description']],
        ], 200),
    ]);

    $this->actingAs($user)
        ->postJson(route('ai.suggest-description'), ['name' => 'Article cache test'])
        ->assertOk()
        ->assertJson(['description' => 'Description mise en cache']);
});

/*
|--------------------------------------------------------------------------
| Tests de suggestion de prix
|--------------------------------------------------------------------------
*/

it('suggests price for a product', function () {
    $user = createUserWithCompanyAndTrial();
    aiCreateBusinessLicense($user);

    config(['services.anthropic.api_key' => 'test-key']);
    fakeAnthropicResponse('15000');

    $this->actingAs($user)
        ->postJson(route('ai.suggest-price'), ['name' => 'Consultation juridique', 'currency' => 'XOF'])
        ->assertOk()
        ->assertJson(['price' => 15000]);
});

/*
|--------------------------------------------------------------------------
| Tests de résumé de document
|--------------------------------------------------------------------------
*/

it('summarizes a document', function () {
    $user = createUserWithCompanyAndTrial();
    aiCreateBusinessLicense($user);

    config(['services.anthropic.api_key' => 'test-key']);
    fakeAnthropicResponse('Facture pour ACME Corp totalisant 150 000 XOF pour des services informatiques.');

    $this->actingAs($user)
        ->postJson(route('ai.summarize-document'), [
            'customer_name' => 'ACME Corp',
            'total'         => 150000,
            'currency'      => 'XOF',
            'items'         => [
                ['description' => 'Maintenance serveur', 'quantity' => 1, 'total' => 150000],
            ],
        ])
        ->assertOk()
        ->assertJsonStructure(['summary']);
});

/*
|--------------------------------------------------------------------------
| Tests de détection de doublons
|--------------------------------------------------------------------------
*/

it('detects duplicate customer names', function () {
    $user = createUserWithCompanyAndTrial();
    aiCreateBusinessLicense($user);

    config(['services.anthropic.api_key' => 'test-key']);

    Http::fake([
        'api.anthropic.com/*' => Http::response([
            'content' => [['type' => 'text', 'text' => '[[0,2]]']],
        ], 200),
    ]);

    $this->actingAs($user)
        ->postJson(route('ai.detect-duplicates'), [
            'names' => ['SARL Dupont', 'Dupont SARL', 'Entreprise Martin'],
        ])
        ->assertOk()
        ->assertJsonStructure(['duplicates']);
});
