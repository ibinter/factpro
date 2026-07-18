<?php

use App\Models\Customer;
use App\Models\Deal;
use App\Models\DealActivity;
use App\Models\License;
use App\Models\Plan;
use App\Models\User;
use App\Services\LicenseService;

/** Crée une licence BUSINESS active pour un utilisateur. */
function createBusinessLicenseFor(User $user): License
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

/** Crée un deal prospect pour un test. */
function createDealFor(int $companyId, array $attributes = []): Deal
{
    return Deal::create([
        'company_id'    => $companyId,
        'prospect_name' => 'Prospect Test',
        'stage'         => 'prospect',
        ...$attributes,
    ]);
}

beforeEach(function () {
    $this->user    = createUserWithCompany();
    $this->company = $this->user->currentCompany;
    createBusinessLicenseFor($this->user);
});

/*
|--------------------------------------------------------------------------
| Création de deals
|--------------------------------------------------------------------------
*/

it('creates a deal with prospect name', function () {
    $this->actingAs($this->user)
        ->post(route('crm.store'), [
            'prospect_name'  => 'Acme Corp',
            'prospect_email' => 'contact@acme.com',
            'stage'          => 'prospect',
            'value'          => 500000,
            'probability'    => 40,
            'source'         => 'website',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('deals', [
        'company_id'    => $this->company->id,
        'prospect_name' => 'Acme Corp',
        'stage'         => 'prospect',
        'value'         => 500000,
    ]);
});

it('creates a deal linked to existing customer', function () {
    $customer = createCustomerFor($this->company);

    $this->actingAs($this->user)
        ->post(route('crm.store'), [
            'customer_id' => $customer->id,
            'stage'       => 'contacted',
            'value'       => 200000,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('deals', [
        'company_id'  => $this->company->id,
        'customer_id' => $customer->id,
        'stage'       => 'contacted',
    ]);
});

/*
|--------------------------------------------------------------------------
| Changement de stage
|--------------------------------------------------------------------------
*/

it('moves deal to next stage and records activity', function () {
    $deal = createDealFor($this->company->id, ['stage' => 'prospect']);

    $this->actingAs($this->user)
        ->post(route('crm.stage', $deal->id), ['stage' => 'contacted'])
        ->assertRedirect();

    expect($deal->fresh()->stage)->toBe('contacted');

    $this->assertDatabaseHas('deal_activities', [
        'deal_id' => $deal->id,
        'type'    => 'stage_change',
    ]);
});

/*
|--------------------------------------------------------------------------
| Marquer Gagné / conversion prospect
|--------------------------------------------------------------------------
*/

it('marks deal as won and converts prospect to customer', function () {
    $deal = createDealFor($this->company->id, [
        'prospect_name'  => 'Nouveau Client',
        'prospect_email' => 'nc@test.com',
    ]);

    $this->actingAs($this->user)
        ->post(route('crm.won', $deal->id))
        ->assertRedirect();

    $fresh = $deal->fresh();
    expect($fresh->stage)->toBe('won');
    expect($fresh->won_at)->not->toBeNull();
    expect($fresh->customer_id)->not->toBeNull();

    $this->assertDatabaseHas('customers', [
        'company_id' => $this->company->id,
        'name'       => 'Nouveau Client',
    ]);
});

/*
|--------------------------------------------------------------------------
| Marquer Perdu
|--------------------------------------------------------------------------
*/

it('marks deal as lost with reason', function () {
    $deal = createDealFor($this->company->id);

    $this->actingAs($this->user)
        ->post(route('crm.lost', $deal->id), ['lost_reason' => 'Prix trop élevé'])
        ->assertRedirect();

    $fresh = $deal->fresh();
    expect($fresh->stage)->toBe('lost');
    expect($fresh->lost_reason)->toBe('Prix trop élevé');
    expect($fresh->lost_at)->not->toBeNull();
});

/*
|--------------------------------------------------------------------------
| Activités
|--------------------------------------------------------------------------
*/

it('adds activity to deal', function () {
    $deal = createDealFor($this->company->id);

    $this->actingAs($this->user)
        ->post(route('crm.activities.store', $deal->id), [
            'type'    => 'call',
            'content' => 'Appel de découverte réalisé.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('deal_activities', [
        'deal_id' => $deal->id,
        'type'    => 'call',
        'content' => 'Appel de découverte réalisé.',
    ]);
});

/*
|--------------------------------------------------------------------------
| Pipeline
|--------------------------------------------------------------------------
*/

it('returns pipeline grouped by stage', function () {
    createDealFor($this->company->id, ['stage' => 'prospect']);
    createDealFor($this->company->id, ['stage' => 'contacted']);
    createDealFor($this->company->id, ['stage' => 'won', 'value' => 100000]);

    $this->actingAs($this->user)
        ->get(route('crm.pipeline'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Crm/Pipeline')
            ->where('hasAccess', true)
            ->has('stages.prospect')
            ->has('stages.contacted')
            ->has('stages.won')
        );
});

/*
|--------------------------------------------------------------------------
| Stats JSON
|--------------------------------------------------------------------------
*/

it('returns deal stats', function () {
    createDealFor($this->company->id, ['stage' => 'prospect', 'value' => 500000]);
    createDealFor($this->company->id, ['stage' => 'won', 'value' => 200000, 'won_at' => now()]);

    $this->actingAs($this->user)
        ->getJson(route('crm.stats'))
        ->assertOk()
        ->assertJsonStructure(['by_stage', 'total_pipeline', 'closing_rate_30', 'won_value_month']);
});

/*
|--------------------------------------------------------------------------
| Isolation entre sociétés
|--------------------------------------------------------------------------
*/

it('isolates deals between companies', function () {
    $other = createUserWithCompany();
    createBusinessLicenseFor($other);
    $otherCompany = $other->currentCompany;

    createDealFor($otherCompany->id, ['prospect_name' => 'Deal Confidentiel']);
    createDealFor($this->company->id, ['prospect_name' => 'Mon Deal']);

    $this->actingAs($this->user)
        ->get(route('crm.pipeline'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Crm/Pipeline')
            ->where('hasAccess', true)
        );

    // Les deals de l'autre société ne doivent pas apparaître
    $deals = Deal::forCompany($this->company->id)->get();
    expect($deals->where('prospect_name', 'Deal Confidentiel')->count())->toBe(0);
    expect($deals->where('prospect_name', 'Mon Deal')->count())->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Gate BUSINESS+
|--------------------------------------------------------------------------
*/

it('requires business plan', function () {
    // Utilisateur avec seulement un essai PRO (pas BUSINESS)
    $proUser = createUserWithCompanyAndTrial();

    $this->actingAs($proUser)
        ->get(route('crm.pipeline'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Crm/Pipeline')
            ->where('hasAccess', false)
        );
});
