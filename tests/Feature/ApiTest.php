<?php

/*
|--------------------------------------------------------------------------
| API REST publique v1 (cahier §20) — tokens Sanctum, quotas par forfait
| (§22.1 : BUSINESS 1000 req/h, ENTERPRISE illimité), scoping société.
*/

use App\Models\Customer;
use App\Models\Document;
use App\Models\License;
use App\Models\Plan;
use App\Models\Product;
use App\Models\User;
use App\Services\LicenseService;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Testing\AssertableInertia as Assert;

/** Crée un utilisateur + société + licence active sur le plan donné. */
function createApiUserWithPlan(string $planCode): User
{
    seedPlans();

    $user = createUserWithCompany();
    $plan = Plan::where('code', $planCode)->firstOrFail();

    License::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'license_key' => app(LicenseService::class)->generateKey(),
        'type' => 'paid',
        'status' => 'active',
        'starts_at' => now(),
        'ends_at' => now()->addMonth(),
        'limits' => $plan->limits,
        'activation_source' => 'manual',
    ]);

    return $user->fresh();
}

/** En-tête Authorization: Bearer pour un utilisateur (nouveau token). */
function bearerFor(User $user, array $abilities = ['read', 'write']): array
{
    return ['Authorization' => 'Bearer '.$user->createToken('test-api', $abilities)->plainTextToken];
}

it('expose la documentation publique sans authentification', function () {
    $this->getJson('/api/v1/docs')
        ->assertOk()
        ->assertJsonPath('version', 'v1')
        ->assertJsonStructure(['base_url', 'authentication', 'endpoints', 'example']);
});

it('refuse les requêtes sans token (401)', function () {
    $this->getJson('/api/v1/customers')->assertUnauthorized();
    $this->getJson('/api/v1/me')->assertUnauthorized();
});

it('refuse un utilisateur au forfait PRO (403 forfait BUSINESS requis)', function () {
    $user = createApiUserWithPlan('pro');

    $this->withHeaders(bearerFor($user))
        ->getJson('/api/v1/customers')
        ->assertForbidden()
        ->assertJsonPath('message', "L'API REST est disponible à partir du forfait BUSINESS.");
});

it('refuse un utilisateur sans licence utilisable (403 licence inactive)', function () {
    seedPlans();
    $user = createUserWithCompany();

    $this->withHeaders(bearerFor($user))
        ->getJson('/api/v1/customers')
        ->assertForbidden()
        ->assertJsonPath('message', 'Licence inactive.');
});

it('renvoie les infos du compte sur /me pour un forfait BUSINESS', function () {
    $user = createApiUserWithPlan('business');

    $this->withHeaders(bearerFor($user))
        ->getJson('/api/v1/me')
        ->assertOk()
        ->assertJsonPath('user.email', $user->email)
        ->assertJsonPath('company.id', $user->current_company_id)
        ->assertJsonPath('plan.code', 'business')
        ->assertJsonPath('plan.api_quota', '1000/h');
});

it('liste les clients scopés à la société courante (BUSINESS)', function () {
    $user = createApiUserWithPlan('business');
    createCustomerFor($user->currentCompany, ['name' => 'Client Interne']);

    $other = createApiUserWithPlan('business');
    createCustomerFor($other->currentCompany, ['name' => 'Client Étranger']);

    $this->withHeaders(bearerFor($user))
        ->getJson('/api/v1/customers')
        ->assertOk()
        ->assertJsonFragment(['name' => 'Client Interne'])
        ->assertJsonMissing(['name' => 'Client Étranger'])
        ->assertJsonCount(1, 'data')
        ->assertHeader('X-RateLimit-Limit', '1000');
});

it('crée, modifie et supprime un client via l\'API', function () {
    $user = createApiUserWithPlan('business');
    $headers = bearerFor($user);

    $id = $this->withHeaders($headers)->postJson('/api/v1/customers', [
        'type' => 'company',
        'name' => 'Société Nouvelle',
        'email' => 'contact@nouvelle.ci',
        'country' => 'CI',
        'currency' => 'XOF',
    ])->assertCreated()->assertJsonPath('data.name', 'Société Nouvelle')->json('data.id');

    $this->withHeaders($headers)->putJson("/api/v1/customers/{$id}", [
        'type' => 'company',
        'name' => 'Société Renommée',
    ])->assertOk()->assertJsonPath('data.name', 'Société Renommée');

    $this->withHeaders($headers)->deleteJson("/api/v1/customers/{$id}")->assertOk();
    $this->withHeaders($headers)->getJson("/api/v1/customers/{$id}")->assertNotFound();
});

it('renvoie 404 pour un client d\'une autre société', function () {
    $user = createApiUserWithPlan('business');
    $other = createApiUserWithPlan('business');
    $foreign = createCustomerFor($other->currentCompany, ['name' => 'Hors Périmètre']);

    $this->withHeaders(bearerFor($user))
        ->getJson("/api/v1/customers/{$foreign->id}")
        ->assertNotFound();
});

it('crée et liste des produits via l\'API', function () {
    $user = createApiUserWithPlan('business');
    $headers = bearerFor($user);

    $this->withHeaders($headers)->postJson('/api/v1/products', [
        'type' => 'product',
        'name' => 'Clavier mécanique',
        'price' => 45000,
        'tax_rate' => 18,
    ])->assertCreated()->assertJsonPath('data.price', 45000);

    $this->withHeaders($headers)->getJson('/api/v1/products?search=Clavier')
        ->assertOk()
        ->assertJsonFragment(['name' => 'Clavier mécanique']);
});

it('crée un document avec lignes et totaux calculés (POST /documents)', function () {
    $user = createApiUserWithPlan('business');
    $customer = createCustomerFor($user->currentCompany);

    $response = $this->withHeaders(bearerFor($user))->postJson('/api/v1/documents', [
        'type' => 'invoice',
        'customer_id' => $customer->id,
        'issue_date' => now()->toDateString(),
        'due_date' => now()->addDays(30)->toDateString(),
        'currency' => 'XOF',
        'lines' => [
            ['description' => 'Prestation conseil', 'quantity' => 2, 'unit_price' => 5000, 'tax_rate' => 18],
        ],
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.type', 'invoice')
        ->assertJsonPath('data.status', 'draft')
        ->assertJsonPath('data.subtotal', 10000)
        ->assertJsonPath('data.tax_amount', 1800)
        ->assertJsonPath('data.total', 11800)
        ->assertJsonCount(1, 'data.lines');

    expect($response->json('data.number'))->toStartWith('FAC');
});

it('finalise le document à la création quand finalize=true', function () {
    $user = createApiUserWithPlan('business');

    $this->withHeaders(bearerFor($user))->postJson('/api/v1/documents', [
        'type' => 'invoice',
        'issue_date' => now()->toDateString(),
        'currency' => 'XOF',
        'finalize' => true,
        'lines' => [
            ['description' => 'Abonnement annuel', 'quantity' => 1, 'unit_price' => 120000],
        ],
    ])->assertCreated()
        ->assertJsonPath('data.is_finalized', true)
        ->assertJsonPath('data.status', 'sent');
});

it('renvoie 404 pour le document d\'une autre société', function () {
    $user = createApiUserWithPlan('business');

    $other = createApiUserWithPlan('business');
    $foreign = app(\App\Services\DocumentService::class)->create(
        $other->currentCompany,
        $other,
        ['type' => 'invoice', 'issue_date' => now()->toDateString(), 'currency' => 'XOF'],
        [['description' => 'Ligne étrangère', 'quantity' => 1, 'unit_price' => 1000]],
    );

    $this->withHeaders(bearerFor($user))
        ->getJson("/api/v1/documents/{$foreign->uuid}")
        ->assertNotFound();

    $this->withHeaders(bearerFor($user))
        ->getJson("/api/v1/documents/{$foreign->uuid}/pdf")
        ->assertNotFound();
});

it('rejette une création de document invalide (422)', function () {
    $user = createApiUserWithPlan('business');

    $this->withHeaders(bearerFor($user))->postJson('/api/v1/documents', [
        'type' => 'invoice',
        'currency' => 'XOF',
        'lines' => [],
    ])->assertStatus(422)->assertJsonValidationErrors(['issue_date', 'lines']);
});

it('applique le quota BUSINESS de 1000 requêtes/heure (429)', function () {
    $user = createApiUserWithPlan('business');

    foreach (range(1, 1000) as $i) {
        RateLimiter::hit('api:'.$user->id, 3600);
    }

    $this->withHeaders(bearerFor($user))
        ->getJson('/api/v1/customers')
        ->assertStatus(429)
        ->assertHeader('Retry-After')
        ->assertHeader('X-RateLimit-Remaining', '0');
});

it('n\'applique aucun quota au forfait ENTERPRISE', function () {
    $user = createApiUserWithPlan('enterprise');

    foreach (range(1, 1000) as $i) {
        RateLimiter::hit('api:'.$user->id, 3600);
    }

    $this->withHeaders(bearerFor($user))
        ->getJson('/api/v1/customers')
        ->assertOk()
        ->assertHeaderMissing('X-RateLimit-Limit');
});

it('affiche la page de gestion des clés avec accès pour BUSINESS', function () {
    $user = createApiUserWithPlan('business');

    $this->actingAs($user)
        ->get(route('api-tokens.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('ApiTokens/Index')
            ->where('hasAccess', true)
            ->where('planCode', 'business'));
});

it('affiche l\'upsell (sans accès) pour un forfait PRO', function () {
    $user = createApiUserWithPlan('pro');

    $this->actingAs($user)
        ->get(route('api-tokens.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('ApiTokens/Index')
            ->where('hasAccess', false)
            ->where('planCode', 'pro'));
});

it('refuse la création de clé pour un forfait PRO', function () {
    $user = createApiUserWithPlan('pro');

    $this->actingAs($user)
        ->from(route('api-tokens.index'))
        ->post(route('api-tokens.store'), ['name' => 'Interdit'])
        ->assertRedirect(route('api-tokens.index'))
        ->assertSessionHas('error');

    expect($user->tokens()->count())->toBe(0);
});

it('crée une clé (BUSINESS) et flashe le token en clair une seule fois', function () {
    $user = createApiUserWithPlan('business');

    $this->actingAs($user)
        ->post(route('api-tokens.store'), ['name' => 'Boutique', 'abilities' => ['read', 'write']])
        ->assertRedirect(route('api-tokens.index'))
        ->assertSessionHas('plain_token');

    expect($user->tokens()->count())->toBe(1)
        ->and($user->tokens()->first()->name)->toBe('Boutique')
        ->and($user->tokens()->first()->abilities)->toBe(['read', 'write']);
});

it('révoque une clé : elle devient immédiatement inutilisable (401)', function () {
    $user = createApiUserWithPlan('business');

    $headers = bearerFor($user);
    $tokenId = $user->tokens()->first()->id;

    // Le token fonctionne…
    $this->withHeaders($headers)->getJson('/api/v1/me')->assertOk();

    // …on le révoque depuis la page de gestion…
    $this->actingAs($user)
        ->delete(route('api-tokens.destroy', $tokenId))
        ->assertRedirect();

    expect($user->tokens()->count())->toBe(0);

    // …et il est refusé ensuite (401).
    app('auth')->forgetGuards();
    $this->flushSession();

    $this->withHeaders($headers)->getJson('/api/v1/me')->assertUnauthorized();
});

it('ne permet pas de révoquer la clé d\'un autre utilisateur', function () {
    $user = createApiUserWithPlan('business');
    $other = createApiUserWithPlan('business');
    $other->createToken('clé-autrui');
    $foreignTokenId = $other->tokens()->first()->id;

    $this->actingAs($user)
        ->delete(route('api-tokens.destroy', $foreignTokenId))
        ->assertRedirect();

    expect($other->tokens()->count())->toBe(1);
});
