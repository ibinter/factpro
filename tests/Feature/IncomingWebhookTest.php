<?php

/*
|--------------------------------------------------------------------------
| Tests Webhooks Entrants — Zapier / Make (Phase 10)
|--------------------------------------------------------------------------
*/

use App\Models\Customer;
use App\Models\Document;
use App\Models\IncomingWebhook;

/** Crée un IncomingWebhook pour la company de l'utilisateur. */
function createIncomingWebhook($user, array $attrs = []): IncomingWebhook
{
    return IncomingWebhook::create(array_merge([
        'company_id' => $user->current_company_id,
        'name' => 'Test Zap',
        'source' => 'zapier',
        'secret_token' => IncomingWebhook::generateToken(),
        'allowed_actions' => ['create_customer', 'create_document', 'register_payment'],
        'is_active' => true,
    ], $attrs));
}

/** Headers avec Bearer token du webhook. */
function webhookHeaders(IncomingWebhook $wh): array
{
    return [
        'Authorization' => 'Bearer ' . $wh->secret_token,
        'Accept' => 'application/json',
    ];
}

// -----------------------------------------------------------------------
// UI — gestion des webhooks entrants
// -----------------------------------------------------------------------

it('creates an incoming webhook with auto token', function () {
    $user = createUserWithCompanyAndTrial();

    $response = $this->actingAs($user)->post(route('incoming-webhooks.store'), [
        'name' => 'Mon Zap #1',
        'source' => 'zapier',
        'allowed_actions' => ['create_customer', 'create_document'],
    ]);

    $response->assertRedirect();

    $webhook = IncomingWebhook::where('company_id', $user->current_company_id)->first();
    expect($webhook)->not->toBeNull();
    expect($webhook->name)->toBe('Mon Zap #1');
    expect($webhook->source)->toBe('zapier');
    expect(strlen($webhook->secret_token))->toBe(64);
    expect($webhook->allowed_actions)->toContain('create_customer');
});

it('regenerates webhook token', function () {
    $user = createUserWithCompanyAndTrial();
    $webhook = createIncomingWebhook($user);
    $oldToken = $webhook->secret_token;

    $this->actingAs($user)
        ->post(route('incoming-webhooks.regenerate', $webhook->id))
        ->assertRedirect();

    $webhook->refresh();
    expect($webhook->secret_token)->not->toBe($oldToken);
    expect(strlen($webhook->secret_token))->toBe(64);
});

it('can delete an incoming webhook', function () {
    $user = createUserWithCompanyAndTrial();
    $webhook = createIncomingWebhook($user);

    $this->actingAs($user)
        ->delete(route('incoming-webhooks.destroy', $webhook->id))
        ->assertRedirect();

    expect(IncomingWebhook::find($webhook->id))->toBeNull();
});

// -----------------------------------------------------------------------
// Auth middleware
// -----------------------------------------------------------------------

it('rejects requests with invalid token', function () {
    $this->withHeaders(['Authorization' => 'Bearer invalide_token', 'Accept' => 'application/json'])
        ->getJson('/api/zapier/triggers/new-invoice')
        ->assertUnauthorized();
});

it('rejects requests without token', function () {
    $this->withHeaders(['Accept' => 'application/json'])
        ->getJson('/api/zapier/triggers/new-invoice')
        ->assertUnauthorized();
});

it('rejects inactive webhook token', function () {
    $user = createUserWithCompanyAndTrial();
    $webhook = createIncomingWebhook($user, ['is_active' => false]);

    $this->withHeaders(webhookHeaders($webhook))
        ->getJson('/api/zapier/triggers/new-invoice')
        ->assertUnauthorized();
});

it('rejects action not in allowed_actions', function () {
    $user = createUserWithCompanyAndTrial();
    // Seule action : create_customer — pas register_payment
    $webhook = createIncomingWebhook($user, ['allowed_actions' => ['create_customer']]);

    $this->withHeaders(webhookHeaders($webhook))
        ->postJson('/api/zapier/payments', [
            'document_id' => 1,
            'amount' => 1000,
            'method' => 'cash',
        ])
        ->assertForbidden();
});

// -----------------------------------------------------------------------
// Zapier — Customers
// -----------------------------------------------------------------------

it('creates a customer via zapier endpoint', function () {
    $user = createUserWithCompanyAndTrial();
    $webhook = createIncomingWebhook($user);

    $response = $this->withHeaders(webhookHeaders($webhook))
        ->postJson('/api/zapier/customers', [
            'type' => 'company',
            'name' => 'Acme Corp',
            'email' => 'contact@acme.com',
            'country' => 'CI',
            'currency' => 'XOF',
        ]);

    $response->assertCreated()
        ->assertJsonPath('name', 'Acme Corp');

    expect(Customer::where('company_id', $user->current_company_id)->where('name', 'Acme Corp')->exists())->toBeTrue();
});

// -----------------------------------------------------------------------
// Zapier — Documents
// -----------------------------------------------------------------------

it('creates a document via zapier endpoint', function () {
    $user = createUserWithCompanyAndTrial();
    $webhook = createIncomingWebhook($user);

    $response = $this->withHeaders(webhookHeaders($webhook))
        ->postJson('/api/zapier/documents', [
            'type' => 'invoice',
            'issue_date' => '2026-07-18',
            'currency' => 'XOF',
            'lines' => [[
                'description' => 'Prestation',
                'quantity' => 1,
                'unit_price' => 50000,
            ]],
        ]);

    $response->assertCreated()
        ->assertJsonPath('type', 'invoice')
        ->assertJsonStructure(['id', 'type', 'number', 'total', 'status', 'created_at']);

    expect(Document::where('company_id', $user->current_company_id)->where('type', 'invoice')->exists())->toBeTrue();
});

// -----------------------------------------------------------------------
// Zapier — Triggers (polling)
// -----------------------------------------------------------------------

it('returns trigger list for new invoices', function () {
    $user = createUserWithCompanyAndTrial();
    $webhook = createIncomingWebhook($user);

    // Créer une facture pour la company
    $another_webhook = createIncomingWebhook($user);
    $this->withHeaders(webhookHeaders($webhook))
        ->postJson('/api/zapier/documents', [
            'type' => 'invoice',
            'issue_date' => '2026-07-18',
            'currency' => 'XOF',
            'lines' => [['description' => 'Test', 'quantity' => 1, 'unit_price' => 1000]],
        ]);

    $response = $this->withHeaders(webhookHeaders($webhook))
        ->getJson('/api/zapier/triggers/new-invoice');

    $response->assertOk()
        ->assertJsonStructure([['id', 'type', 'number', 'total', 'created_at']]);
});

it('filters triggers by since parameter', function () {
    $user = createUserWithCompanyAndTrial();
    $webhook = createIncomingWebhook($user);

    // Créer une facture passée (simulée 2h avant)
    Document::create([
        'company_id' => $user->current_company_id,
        'type' => 'invoice',
        'number' => 'FAC-OLD-001',
        'status' => 'draft',
        'issue_date' => now()->subDays(2),
        'currency' => 'XOF',
        'subtotal' => 1000,
        'discount_amount' => 0,
        'tax_amount' => 0,
        'total' => 1000,
        'amount_paid' => 0,
        'created_by' => $user->id,
        'created_at' => now()->subHours(3),
    ]);

    $since = now()->subMinutes(30)->toIso8601String();
    $response = $this->withHeaders(webhookHeaders($webhook))
        ->getJson('/api/zapier/triggers/new-invoice?since=' . urlencode($since));

    $response->assertOk();
    // La facture ancienne (3h) ne doit pas apparaître
    $ids = collect($response->json())->pluck('number');
    expect($ids)->not->toContain('FAC-OLD-001');
});

it('increments calls count on each request', function () {
    $user = createUserWithCompanyAndTrial();
    $webhook = createIncomingWebhook($user);

    expect($webhook->fresh()->calls_count)->toBe(0);

    $this->withHeaders(webhookHeaders($webhook))
        ->getJson('/api/zapier/triggers/new-invoice')
        ->assertOk();

    $this->withHeaders(webhookHeaders($webhook))
        ->getJson('/api/zapier/triggers/new-invoice')
        ->assertOk();

    $webhook->refresh();
    expect($webhook->calls_count)->toBe(2);
});

// -----------------------------------------------------------------------
// Make — alias endpoints
// -----------------------------------------------------------------------

it('creates a customer via make endpoint', function () {
    $user = createUserWithCompanyAndTrial();
    $webhook = createIncomingWebhook($user, ['source' => 'make']);

    $response = $this->withHeaders(webhookHeaders($webhook))
        ->postJson('/api/make/customers', [
            'type' => 'individual',
            'name' => 'Jean Dupont',
            'country' => 'CI',
        ]);

    $response->assertCreated()
        ->assertJsonPath('name', 'Jean Dupont');
});

it('returns trigger list for invoices via make endpoint', function () {
    $user = createUserWithCompanyAndTrial();
    $webhook = createIncomingWebhook($user, ['source' => 'make']);

    $response = $this->withHeaders(webhookHeaders($webhook))
        ->getJson('/api/make/triggers/invoices');

    $response->assertOk()
        ->assertJsonIsArray();
});

// -----------------------------------------------------------------------
// Isolation entre companies
// -----------------------------------------------------------------------

it('isolates webhooks between companies', function () {
    $user1 = createUserWithCompanyAndTrial();
    $user2 = createUserWithCompanyAndTrial();

    $webhook1 = createIncomingWebhook($user1);

    // Créer un client dans la company de user1 via webhook1
    $this->withHeaders(webhookHeaders($webhook1))
        ->postJson('/api/zapier/customers', [
            'type' => 'individual',
            'name' => 'Client Company 1',
        ]);

    // Vérifier que le client n'est pas visible dans la company de user2
    expect(Customer::where('company_id', $user2->current_company_id)->where('name', 'Client Company 1')->exists())->toBeFalse();
    expect(Customer::where('company_id', $user1->current_company_id)->where('name', 'Client Company 1')->exists())->toBeTrue();
});

it('prevents accessing another company webhook via UI', function () {
    $user1 = createUserWithCompanyAndTrial();
    $user2 = createUserWithCompanyAndTrial();

    $webhook = createIncomingWebhook($user1);

    // user2 essaie de supprimer le webhook de user1 → 403
    $this->actingAs($user2)
        ->delete(route('incoming-webhooks.destroy', $webhook->id))
        ->assertForbidden();
});
