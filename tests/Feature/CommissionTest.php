<?php

use App\Models\CommissionPayout;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Document;
use App\Models\License;
use App\Models\Plan;
use App\Models\SalesAgent;
use App\Models\User;
use App\Services\CommissionService;
use App\Services\LicenseService;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Helpers locaux au module commissions (préfixés createCommission…).
|--------------------------------------------------------------------------
*/

/** Crée une licence active sur un plan donné. */
function createCommissionLicenseFor(User $user, string $planCode = 'business'): License
{
    seedPlans();
    $plan = Plan::where('code', $planCode)->firstOrFail();

    return License::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'license_key' => app(LicenseService::class)->generateKey(),
        'type' => 'paid',
        'status' => 'active',
        'starts_at' => now(),
        'ends_at' => now()->addYear(),
        'limits' => $plan->limits,
        'activation_source' => 'manual',
    ]);
}

/** Crée un vendeur rattaché à une société. */
function createCommissionAgentFor(Company $company, array $attributes = []): SalesAgent
{
    return SalesAgent::create([
        'company_id' => $company->id,
        'name' => 'Vendeur Test',
        'commission_rate' => 5,
        'is_active' => true,
        ...$attributes,
    ]);
}

/** Crée un document (facture payée finalisée par défaut) pour un client. */
function createCommissionDocument(Customer $customer, array $attributes = []): Document
{
    return Document::create([
        'company_id' => $customer->company_id,
        'customer_id' => $customer->id,
        'type' => 'invoice',
        'number' => 'DOC-'.strtoupper(Str::random(8)),
        'status' => 'paid',
        'issue_date' => now()->toDateString(),
        'finalized_at' => now(),
        'total' => 100000,
        'amount_paid' => 100000,
        ...$attributes,
    ]);
}

/*
|--------------------------------------------------------------------------
| Gate BUSINESS/ENTERPRISE
|--------------------------------------------------------------------------
*/

it('shows the commissions page as an upsell for a PRO (trial) user', function () {
    $user = createUserWithCompanyAndTrial(); // essai = plan PRO

    $this->actingAs($user)
        ->get(route('commissions.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Commissions/Index')
            ->where('hasAccess', false));
});

it('forbids commission mutations for a PRO (trial) user', function () {
    $user = createUserWithCompanyAndTrial();

    $this->actingAs($user)
        ->post(route('commissions.agents.store'), ['name' => 'Commercial'])
        ->assertForbidden();
});

/*
|--------------------------------------------------------------------------
| CRUD vendeurs
|--------------------------------------------------------------------------
*/

it('creates, updates and deletes a sales agent for a BUSINESS user', function () {
    $user = createUserWithCompany();
    createCommissionLicenseFor($user);

    $this->actingAs($user)
        ->post(route('commissions.agents.store'), [
            'name' => 'Awa Koné',
            'email' => 'awa@ventes.ci',
            'commission_rate' => 7.5,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $agent = SalesAgent::firstOrFail();
    expect($agent->name)->toBe('Awa Koné')
        ->and((float) $agent->commission_rate)->toBe(7.5)
        ->and($agent->company_id)->toBe($user->current_company_id);

    $this->actingAs($user)
        ->put(route('commissions.agents.update', $agent), [
            'name' => 'Awa Koné SARL',
            'commission_rate' => 10,
        ])
        ->assertRedirect();

    expect($agent->fresh()->name)->toBe('Awa Koné SARL')
        ->and((float) $agent->fresh()->commission_rate)->toBe(10.0);

    $this->actingAs($user)
        ->delete(route('commissions.agents.destroy', $agent))
        ->assertRedirect();

    expect($agent->fresh()->trashed())->toBeTrue();
});

it('validates the commission rate is between 0 and 100', function () {
    $user = createUserWithCompany();
    createCommissionLicenseFor($user);

    $this->actingAs($user)
        ->post(route('commissions.agents.store'), ['name' => 'X', 'commission_rate' => 150])
        ->assertSessionHasErrors('commission_rate');
});

/*
|--------------------------------------------------------------------------
| Affectation clients
|--------------------------------------------------------------------------
*/

it('assigns customers of the company to an agent', function () {
    $user = createUserWithCompany();
    createCommissionLicenseFor($user);
    $agent = createCommissionAgentFor($user->currentCompany);

    $c1 = createCustomerFor($user->currentCompany, ['name' => 'Client A']);
    $c2 = createCustomerFor($user->currentCompany, ['name' => 'Client B']);

    $this->actingAs($user)
        ->post(route('commissions.assign', $agent), ['customer_ids' => [$c1->id, $c2->id]])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($c1->fresh()->sales_agent_id)->toBe($agent->id)
        ->and($c2->fresh()->sales_agent_id)->toBe($agent->id);
});

it('does not assign customers from another company', function () {
    $user = createUserWithCompany();
    createCommissionLicenseFor($user);
    $agent = createCommissionAgentFor($user->currentCompany);

    $other = createUserWithCompany();
    $foreignCustomer = createCustomerFor($other->currentCompany);

    $this->actingAs($user)
        ->post(route('commissions.assign', $agent), ['customer_ids' => [$foreignCustomer->id]])
        ->assertRedirect();

    expect($foreignCustomer->fresh()->sales_agent_id)->toBeNull();
});

/*
|--------------------------------------------------------------------------
| Calcul du CA commissionnable
|--------------------------------------------------------------------------
*/

it('computes commissionable revenue as sum of paid invoices, credit notes deducted', function () {
    $user = createUserWithCompany();
    $company = $user->currentCompany;
    $agent = createCommissionAgentFor($company, ['commission_rate' => 5]);

    $customer = createCustomerFor($company, ['sales_agent_id' => $agent->id]);
    $otherCustomer = createCustomerFor($company); // non affecté

    // Facture payée finalisée du client affecté → comptée.
    createCommissionDocument($customer, ['total' => 200000]);
    // Autre facture payée → comptée.
    createCommissionDocument($customer, ['total' => 100000]);
    // Facture NON payée → exclue.
    createCommissionDocument($customer, ['status' => 'sent', 'total' => 500000]);
    // Facture non finalisée → exclue.
    createCommissionDocument($customer, ['finalized_at' => null, 'total' => 300000]);
    // Avoir finalisé → déduit.
    createCommissionDocument($customer, ['type' => 'credit_note', 'status' => 'paid', 'total' => 50000]);
    // Facture d'un client NON affecté → exclue.
    createCommissionDocument($otherCustomer, ['total' => 999000]);

    $service = app(CommissionService::class);
    $revenue = $service->commissionableRevenue($agent, now()->startOfMonth(), now()->endOfMonth());

    // 200000 + 100000 - 50000 = 250000
    expect($revenue)->toBe(250000.0);
});

it('excludes invoices outside the requested period', function () {
    $user = createUserWithCompany();
    $company = $user->currentCompany;
    $agent = createCommissionAgentFor($company);
    $customer = createCustomerFor($company, ['sales_agent_id' => $agent->id]);

    createCommissionDocument($customer, ['issue_date' => now()->toDateString(), 'total' => 100000]);
    createCommissionDocument($customer, ['issue_date' => now()->subMonths(3)->toDateString(), 'total' => 400000]);

    $service = app(CommissionService::class);
    $revenue = $service->commissionableRevenue($agent, now()->startOfMonth(), now()->endOfMonth());

    expect($revenue)->toBe(100000.0);
});

/*
|--------------------------------------------------------------------------
| Génération des décomptes
|--------------------------------------------------------------------------
*/

it('generates a payout with base × rate commission', function () {
    $user = createUserWithCompany();
    createCommissionLicenseFor($user);
    $company = $user->currentCompany;
    $agent = createCommissionAgentFor($company, ['commission_rate' => 5]);
    $customer = createCustomerFor($company, ['sales_agent_id' => $agent->id]);
    createCommissionDocument($customer, ['total' => 200000]);

    $this->actingAs($user)
        ->post(route('commissions.payouts.generate'), [
            'sales_agent_id' => $agent->id,
            'from' => now()->startOfMonth()->toDateString(),
            'to' => now()->endOfMonth()->toDateString(),
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $payout = CommissionPayout::firstOrFail();
    expect((float) $payout->base_amount)->toBe(200000.0)
        ->and((float) $payout->rate)->toBe(5.0)
        ->and((float) $payout->commission_amount)->toBe(10000.0) // 200000 × 5%
        ->and($payout->status)->toBe('pending');
});

it('does not duplicate a payout for the same agent and period but updates it', function () {
    $user = createUserWithCompany();
    $company = $user->currentCompany;
    $agent = createCommissionAgentFor($company, ['commission_rate' => 5]);
    $customer = createCustomerFor($company, ['sales_agent_id' => $agent->id]);
    $doc = createCommissionDocument($customer, ['total' => 100000]);

    $service = app(CommissionService::class);
    $from = now()->startOfMonth();
    $to = now()->endOfMonth();

    $first = $service->generatePayout($agent, $from, $to);
    expect((float) $first->commission_amount)->toBe(5000.0);

    // Une nouvelle facture augmente la base ; re-générer met à jour le même décompte.
    createCommissionDocument($customer, ['total' => 100000]);
    $second = $service->generatePayout($agent, $from, $to);

    expect(CommissionPayout::count())->toBe(1)
        ->and($second->id)->toBe($first->id)
        ->and((float) $second->base_amount)->toBe(200000.0)
        ->and((float) $second->commission_amount)->toBe(10000.0);
});

it('applies a rate override when generating a payout', function () {
    $user = createUserWithCompany();
    $company = $user->currentCompany;
    $agent = createCommissionAgentFor($company, ['commission_rate' => 5]);
    $customer = createCustomerFor($company, ['sales_agent_id' => $agent->id]);
    createCommissionDocument($customer, ['total' => 100000]);

    $payout = app(CommissionService::class)
        ->generatePayout($agent, now()->startOfMonth(), now()->endOfMonth(), 12.0);

    expect((float) $payout->rate)->toBe(12.0)
        ->and((float) $payout->commission_amount)->toBe(12000.0);
});

/*
|--------------------------------------------------------------------------
| Marquage payé
|--------------------------------------------------------------------------
*/

it('marks a payout as paid', function () {
    $user = createUserWithCompany();
    createCommissionLicenseFor($user);
    $company = $user->currentCompany;
    $agent = createCommissionAgentFor($company);
    $customer = createCustomerFor($company, ['sales_agent_id' => $agent->id]);
    createCommissionDocument($customer, ['total' => 100000]);

    $payout = app(CommissionService::class)
        ->generatePayout($agent, now()->startOfMonth(), now()->endOfMonth());

    $this->actingAs($user)
        ->post(route('commissions.payouts.pay', $payout))
        ->assertRedirect()
        ->assertSessionHas('success');

    $payout->refresh();
    expect($payout->status)->toBe('paid')
        ->and($payout->paid_at)->not->toBeNull();
});

/*
|--------------------------------------------------------------------------
| Isolation multi-sociétés
|--------------------------------------------------------------------------
*/

it('isolates agents and payouts between companies', function () {
    $user = createUserWithCompany();
    createCommissionLicenseFor($user);
    $agent = createCommissionAgentFor($user->currentCompany);

    $stranger = createUserWithCompany();
    createCommissionLicenseFor($stranger);

    // Un autre BUSINESS ne peut ni modifier ni supprimer le vendeur.
    $this->actingAs($stranger)
        ->put(route('commissions.agents.update', $agent), ['name' => 'Piraté'])
        ->assertNotFound();

    $this->actingAs($stranger)
        ->post(route('commissions.assign', $agent), ['customer_ids' => []])
        ->assertNotFound();

    // Sa page ne liste aucun vendeur.
    $this->actingAs($stranger)
        ->get(route('commissions.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('hasAccess', true)
            ->has('agents', 0));
});
