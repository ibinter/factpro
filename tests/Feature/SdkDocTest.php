<?php

/*
|--------------------------------------------------------------------------
| Phase 9 — SDK PHP & OpenAPI spec (cahier §24)
*/

use App\Models\License;
use App\Models\Plan;
use App\Models\User;
use App\Services\LicenseService;
use FactPro\FactProClient;

/** Crée un utilisateur avec un forfait BUSINESS actif. */
function createBusinessUserForSdk(): User
{
    seedPlans();

    $user = createUserWithCompany();
    $plan = Plan::where('code', 'business')->firstOrFail();

    License::create([
        'user_id'     => $user->id,
        'plan_id'     => $plan->id,
        'license_key' => app(LicenseService::class)->generateKey(),
        'status'      => 'active',
        'starts_at'   => now()->subDay(),
        'expires_at'  => now()->addYear(),
    ]);

    return $user;
}

it('returns openapi json spec', function () {
    $response = $this->getJson('/api/openapi.json');

    $response->assertOk()
        ->assertJsonStructure([
            'openapi',
            'info' => ['title', 'version'],
            'paths',
        ]);

    expect($response->json('openapi'))->toBe('3.0.3');
});

it('includes documents endpoint in spec', function () {
    $response = $this->getJson('/api/openapi.json');

    $response->assertOk();

    $paths = $response->json('paths');
    expect($paths)->toHaveKey('/documents');
    expect($paths['/documents'])->toHaveKey('get');
    expect($paths['/documents'])->toHaveKey('post');
});

it('includes customers endpoint in spec', function () {
    $response = $this->getJson('/api/openapi.json');

    $response->assertOk();

    $paths = $response->json('paths');
    expect($paths)->toHaveKey('/customers');
    expect($paths['/customers'])->toHaveKey('get');
    expect($paths['/customers'])->toHaveKey('post');
});

it('requires auth to access api docs page', function () {
    $response = $this->get('/api-docs');

    // Redirection vers la page de connexion si non authentifié
    $response->assertRedirect();
});

it('sdk client can be instantiated with token', function () {
    $client = new FactProClient('test-token');

    expect($client)->toBeInstanceOf(FactProClient::class);
});
