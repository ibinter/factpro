<?php

use App\Models\Customer;
use App\Models\Document;
use App\Models\Product;

/*
|--------------------------------------------------------------------------
| Tests OfflineSyncController (Phase 12 — Mode hors-ligne PWA)
|--------------------------------------------------------------------------
| Helpers disponibles (tests/Pest.php) :
|   createUserWithCompanyAndTrial()
|   createUserWithCompany()
|   createCustomerFor($company)
*/

it('returns cache data for offline use', function () {
    $user = createUserWithCompanyAndTrial();
    $company = $user->companies()->first();

    createCustomerFor($company, ['name' => 'Client Hors-Ligne', 'email' => 'offline@test.com']);

    Product::create([
        'company_id' => $company->id,
        'name'       => 'Produit Hors-Ligne',
        'price'      => 1500,
        'tax_rate'   => 18,
        'unit'       => 'pcs',
    ]);

    $response = $this->actingAs($user)->getJson(route('offline.cache-data'));

    $response->assertOk()
        ->assertJsonStructure([
            'customers' => [['id', 'name']],
            'products'  => [['id', 'name']],
            'cachedAt',
        ])
        ->assertJsonFragment(['name' => 'Client Hors-Ligne'])
        ->assertJsonFragment(['name' => 'Produit Hors-Ligne']);
});

it('returns customer list for offline cache', function () {
    $user    = createUserWithCompanyAndTrial();
    $company = $user->companies()->first();

    createCustomerFor($company, ['name' => 'Alice']);
    createCustomerFor($company, ['name' => 'Bob']);

    $response = $this->actingAs($user)->getJson(route('offline.cache-data'));

    $response->assertOk();

    $customers = $response->json('customers');
    expect($customers)->toHaveCount(2);
    expect(collect($customers)->pluck('name')->toArray())->toContain('Alice', 'Bob');
});

it('returns product list for offline cache', function () {
    $user    = createUserWithCompanyAndTrial();
    $company = $user->companies()->first();

    Product::create([
        'company_id' => $company->id,
        'name'       => 'Stylo',
        'price'      => 500,
        'tax_rate'   => 18,
        'unit'       => 'pcs',
    ]);

    Product::create([
        'company_id' => $company->id,
        'name'       => 'Cahier',
        'price'      => 1000,
        'tax_rate'   => 18,
        'unit'       => 'pcs',
    ]);

    $response = $this->actingAs($user)->getJson(route('offline.cache-data'));

    $response->assertOk();
    $products = $response->json('products');
    expect($products)->toHaveCount(2);
    expect(collect($products)->pluck('name')->toArray())->toContain('Stylo', 'Cahier');
});

it('flushes pending documents to server', function () {
    $user    = createUserWithCompanyAndTrial();
    $company = $user->companies()->first();
    $customer = createCustomerFor($company);

    $payload = [
        'documents' => [
            [
                'localId'     => 'local-uuid-001',
                'type'        => 'invoice',
                'customer_id' => $customer->id,
                'issue_date'  => now()->toDateString(),
                'currency'    => 'XOF',
                'subtotal'    => 10000,
                'tax_amount'  => 1800,
                'total'       => 11800,
            ],
        ],
    ];

    $response = $this->actingAs($user)->postJson(route('offline.flush'), $payload);

    $response->assertOk()
        ->assertJsonStructure([
            'results' => [['localId', 'serverId', 'status']],
        ]);

    $result = $response->json('results.0');
    expect($result['localId'])->toBe('local-uuid-001');
    expect($result['status'])->toBe('created');
    expect($result['serverId'])->toBeInt();

    // Vérifier que le document existe en base
    $this->assertDatabaseHas('documents', [
        'id'         => $result['serverId'],
        'company_id' => $company->id,
        'type'       => 'invoice',
    ]);
});

it('handles invalid document data gracefully', function () {
    $user = createUserWithCompanyAndTrial();

    // Envoyer un document avec des données invalides (type manquant, etc.)
    $payload = [
        'documents' => [
            [
                'localId' => 'local-uuid-bad',
                // Pas de type, pas de customer_id — le modèle peut accepter ou rejeter
            ],
        ],
    ];

    // Le controller doit répondre 200 avec status 'failed' ou 'created'
    $response = $this->actingAs($user)->postJson(route('offline.flush'), $payload);

    // Le contrôleur ne doit jamais planter (500) — il gère l'erreur gracieusement
    expect($response->status())->toBeIn([200, 422]);
});

it('isolates cache data between companies', function () {
    $user1    = createUserWithCompanyAndTrial();
    $company1 = $user1->companies()->first();

    $user2    = createUserWithCompanyAndTrial();
    $company2 = $user2->companies()->first();

    createCustomerFor($company1, ['name' => 'Client Société 1']);
    createCustomerFor($company2, ['name' => 'Client Société 2']);

    // user1 ne doit voir que ses clients
    $response1 = $this->actingAs($user1)->getJson(route('offline.cache-data'));
    $names1 = collect($response1->json('customers'))->pluck('name')->toArray();
    expect($names1)->toContain('Client Société 1');
    expect($names1)->not->toContain('Client Société 2');

    // user2 ne doit voir que ses clients
    $response2 = $this->actingAs($user2)->getJson(route('offline.cache-data'));
    $names2 = collect($response2->json('customers'))->pluck('name')->toArray();
    expect($names2)->toContain('Client Société 2');
    expect($names2)->not->toContain('Client Société 1');
});
