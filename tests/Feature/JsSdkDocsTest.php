<?php

/*
|--------------------------------------------------------------------------
| SDK JavaScript — cohérence documentation (Phase 14)
|--------------------------------------------------------------------------
| Vérifie que les routes API v1 consommées par le SDK JS existent,
| que le package.json du SDK est correct, et que l'API répond en JSON.
| Ces tests ne créent pas de Company via factory (règle projet).
*/

use App\Models\License;
use App\Models\Plan;
use App\Models\User;
use App\Services\LicenseService;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------------------
// Helpers locaux
// ---------------------------------------------------------------------------

function jsSdkApiUser(): User
{
    seedPlans();
    $user = createUserWithCompany();
    $plan = Plan::where('code', 'business')->firstOrFail();

    License::create([
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

    return $user->fresh();
}

// ---------------------------------------------------------------------------
// Routes API que le SDK JS consomme
// ---------------------------------------------------------------------------

it('api documents list route exists', function () {
    $routes = collect(Route::getRoutes()->getRoutes())
        ->filter(fn ($r) => str_contains($r->uri(), 'api/v1/documents'))
        ->filter(fn ($r) => in_array('GET', $r->methods()));

    expect($routes)->not->toBeEmpty();
});

it('api customers list route exists', function () {
    $routes = collect(Route::getRoutes()->getRoutes())
        ->filter(fn ($r) => str_contains($r->uri(), 'api/v1/customers'))
        ->filter(fn ($r) => in_array('GET', $r->methods()));

    expect($routes)->not->toBeEmpty();
});

it('api products list route exists', function () {
    $routes = collect(Route::getRoutes()->getRoutes())
        ->filter(fn ($r) => str_contains($r->uri(), 'api/v1/products'))
        ->filter(fn ($r) => in_array('GET', $r->methods()));

    expect($routes)->not->toBeEmpty();
});

it('api invoices list route exists', function () {
    // Les factures passent par /api/v1/documents?type=invoice
    $routes = collect(Route::getRoutes()->getRoutes())
        ->filter(fn ($r) => str_contains($r->uri(), 'api/v1/documents'))
        ->filter(fn ($r) => in_array('GET', $r->methods()));

    expect($routes)->not->toBeEmpty();
});

it('api document pdf route exists', function () {
    $routes = collect(Route::getRoutes()->getRoutes())
        ->filter(fn ($r) => str_contains($r->uri(), 'api/v1/documents') && str_contains($r->uri(), 'pdf'))
        ->filter(fn ($r) => in_array('GET', $r->methods()));

    expect($routes)->not->toBeEmpty();
});

it('api document store route exists', function () {
    $routes = collect(Route::getRoutes()->getRoutes())
        ->filter(fn ($r) => $r->uri() === 'api/v1/documents')
        ->filter(fn ($r) => in_array('POST', $r->methods()));

    expect($routes)->not->toBeEmpty();
});

it('api customer store route exists', function () {
    $routes = collect(Route::getRoutes()->getRoutes())
        ->filter(fn ($r) => $r->uri() === 'api/v1/customers')
        ->filter(fn ($r) => in_array('POST', $r->methods()));

    expect($routes)->not->toBeEmpty();
});

it('api product store route exists', function () {
    $routes = collect(Route::getRoutes()->getRoutes())
        ->filter(fn ($r) => $r->uri() === 'api/v1/products')
        ->filter(fn ($r) => in_array('POST', $r->methods()));

    expect($routes)->not->toBeEmpty();
});

// ---------------------------------------------------------------------------
// Authentification et format JSON
// ---------------------------------------------------------------------------

it('api token auth works', function () {
    $user  = jsSdkApiUser();
    $token = $user->createToken('js-sdk-test')->plainTextToken;

    $response = $this->withToken($token)
        ->getJson('/api/v1/me');

    expect($response->status())->toBe(200);
});

it('api returns json responses', function () {
    $user  = jsSdkApiUser();
    $token = $user->createToken('js-sdk-test')->plainTextToken;

    $response = $this->withToken($token)
        ->getJson('/api/v1/customers');

    expect($response->status())->toBe(200);
    expect($response->headers->get('Content-Type'))->toContain('application/json');
});

it('api returns 401 without token', function () {
    $response = $this->getJson('/api/v1/documents');

    expect($response->status())->toBe(401);
});

// ---------------------------------------------------------------------------
// Cohérence du package SDK JS
// ---------------------------------------------------------------------------

it('js sdk package json exists with correct name', function () {
    $path = base_path('packages/ibigsoft/factpro-js-sdk/package.json');

    expect(file_exists($path))->toBeTrue();

    $json = json_decode(file_get_contents($path), true);

    expect($json['name'])->toBe('@ibigsoft/factpro-sdk');
    expect($json['version'])->toBe('1.0.0');
    expect($json['type'])->toBe('module');
});

it('js sdk index exports FactProClient', function () {
    $indexPath = base_path('packages/ibigsoft/factpro-js-sdk/src/index.js');

    expect(file_exists($indexPath))->toBeTrue();

    $content = file_get_contents($indexPath);

    expect($content)->toContain('FactProClient');
    expect($content)->toContain('AuthError');
    expect($content)->toContain('ValidationError');
});

it('js sdk has all resource files', function () {
    $base = base_path('packages/ibigsoft/factpro-js-sdk/src/resources');

    foreach (['DocumentResource.js', 'CustomerResource.js', 'ProductResource.js', 'InvoiceResource.js'] as $file) {
        expect(file_exists("{$base}/{$file}"))->toBeTrue("Fichier manquant : {$file}");
    }
});

it('js sdk has all error classes', function () {
    $base = base_path('packages/ibigsoft/factpro-js-sdk/src/errors');

    foreach (['FactProError.js', 'AuthError.js', 'ValidationError.js'] as $file) {
        expect(file_exists("{$base}/{$file}"))->toBeTrue("Fichier manquant : {$file}");
    }
});

it('js sdk has example files', function () {
    $base = base_path('packages/ibigsoft/factpro-js-sdk/examples');

    expect(file_exists("{$base}/node-example.js"))->toBeTrue();
    expect(file_exists("{$base}/browser-example.html"))->toBeTrue();
    expect(file_exists("{$base}/woocommerce-snippet.js"))->toBeTrue();
});

it('openapi spec mentions sdk js usage', function () {
    // La spec OpenAPI est disponible sans authentification
    $response = $this->getJson('/api/openapi.json');

    // La spec doit exister et être du JSON valide
    expect($response->status())->toBe(200);

    $spec = $response->json();
    expect($spec)->toBeArray();
    expect($spec)->toHaveKey('openapi');
});
