<?php

use App\Models\License;
use App\Models\Plan;
use App\Models\User;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use App\Services\OutgoingWebhookService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Helpers locaux au module webhooks sortants
|--------------------------------------------------------------------------
*/

/** Crée une licence BUSINESS active pour un utilisateur. */
function createWebhookLicenseFor(User $user, string $planCode = 'business'): License
{
    seedPlans();
    $plan = Plan::where('code', $planCode)->firstOrFail();

    return License::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'license_key' => 'FP-TEST-'.strtoupper(Str::random(8)),
        'type' => 'subscription',
        'status' => 'active',
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addYear(),
        'limits' => $plan->limits,
        'activation_source' => 'test',
    ]);
}

/*
|--------------------------------------------------------------------------
| Tests
|--------------------------------------------------------------------------
*/

it('creates a webhook endpoint', function () {
    $user = createUserWithCompany();
    createWebhookLicenseFor($user);

    $this->actingAs($user)
        ->post(route('outgoing-webhooks.store'), [
            'url' => 'https://example.com/hook',
            'events' => ['invoice.finalized', 'document.created'],
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('webhook_endpoints', [
        'company_id' => $user->current_company_id,
        'url' => 'https://example.com/hook',
        'is_active' => 1,
    ]);

    $endpoint = WebhookEndpoint::where('company_id', $user->current_company_id)->first();
    expect($endpoint->events)->toContain('invoice.finalized')
        ->and($endpoint->events)->toContain('document.created');
});

it('validates url is required', function () {
    $user = createUserWithCompany();
    createWebhookLicenseFor($user);

    $this->actingAs($user)
        ->post(route('outgoing-webhooks.store'), [
            'url' => '',
            'events' => ['document.created'],
        ])
        ->assertSessionHasErrors('url');
});

it('generates unique secret per endpoint', function () {
    $user = createUserWithCompany();
    createWebhookLicenseFor($user);
    $company = $user->currentCompany;

    $this->actingAs($user)->post(route('outgoing-webhooks.store'), [
        'url' => 'https://example.com/hook1',
        'events' => ['document.created'],
    ]);

    $this->actingAs($user)->post(route('outgoing-webhooks.store'), [
        'url' => 'https://example.com/hook2',
        'events' => ['document.created'],
    ]);

    $endpoints = WebhookEndpoint::where('company_id', $company->id)->get();
    expect($endpoints->count())->toBe(2)
        ->and($endpoints[0]->secret)->not->toBe($endpoints[1]->secret);
});

it('isolates endpoints between companies', function () {
    $user1 = createUserWithCompany();
    createWebhookLicenseFor($user1);

    $user2 = createUserWithCompany();
    createWebhookLicenseFor($user2);

    $this->actingAs($user1)->post(route('outgoing-webhooks.store'), [
        'url' => 'https://company1.com/hook',
        'events' => ['document.created'],
    ]);

    // user2 ne doit pas voir l'endpoint de user1
    $this->actingAs($user2)
        ->get(route('outgoing-webhooks.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Webhooks/Index')
            ->where('endpoints', []));
});

it('dispatches a delivery on invoice finalized', function () {
    Http::fake(['*' => Http::response('ok', 200)]);

    $user = createUserWithCompany();
    createWebhookLicenseFor($user);
    $company = $user->currentCompany;

    WebhookEndpoint::create([
        'company_id' => $company->id,
        'url' => 'https://example.com/hook',
        'secret' => 'testsecret',
        'events' => ['invoice.finalized'],
        'is_active' => true,
    ]);

    app(OutgoingWebhookService::class)->dispatch($company, 'invoice.finalized', [
        'event' => 'invoice.finalized',
        'document_id' => 99,
    ]);

    $this->assertDatabaseHas('webhook_deliveries', [
        'event' => 'invoice.finalized',
    ]);
});

it('signs the payload with hmac-sha256', function () {
    $service = app(OutgoingWebhookService::class);

    $secret = 'my-secret-key';
    $body = json_encode(['event' => 'test', 'data' => 'value']);

    $signature = $service->signature($secret, $body);

    expect($signature)->toStartWith('sha256=')
        ->and($signature)->toBe('sha256='.hash_hmac('sha256', $body, $secret));
});

it('deletes endpoint (soft delete)', function () {
    $user = createUserWithCompany();
    createWebhookLicenseFor($user);
    $company = $user->currentCompany;

    $endpoint = WebhookEndpoint::create([
        'company_id' => $company->id,
        'url' => 'https://example.com/hook',
        'secret' => 'testsecret',
        'events' => ['document.created'],
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->delete(route('outgoing-webhooks.destroy', $endpoint))
        ->assertRedirect();

    $this->assertSoftDeleted('webhook_endpoints', ['id' => $endpoint->id]);
});

it('sends a test ping', function () {
    Http::fake(['*' => Http::response('pong', 200)]);

    $user = createUserWithCompany();
    createWebhookLicenseFor($user);
    $company = $user->currentCompany;

    $endpoint = WebhookEndpoint::create([
        'company_id' => $company->id,
        'url' => 'https://example.com/hook',
        'secret' => 'testsecret',
        'events' => ['webhook.test'],
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->post(route('outgoing-webhooks.test', $endpoint))
        ->assertRedirect();

    $this->assertDatabaseHas('webhook_deliveries', [
        'webhook_endpoint_id' => $endpoint->id,
        'event' => 'webhook.test',
    ]);
});
