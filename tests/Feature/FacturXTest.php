<?php

use App\Models\Customer;
use App\Models\Document;
use App\Models\License;
use App\Models\Plan;
use App\Services\DocumentService;
use App\Services\FacturXService;
use App\Services\LicenseService;

/*
|--------------------------------------------------------------------------
| Helpers locaux
|--------------------------------------------------------------------------
*/

/** Crée une licence ENTERPRISE active pour l'utilisateur. */
function createEnterpriseLicense(\App\Models\User $user): License
{
    seedPlans();
    $plan = Plan::where('code', 'enterprise')->firstOrFail();

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

/** Utilisateur + société + licence ENTERPRISE. */
function createEnterpriseOwner(): \App\Models\User
{
    $user = createUserWithCompany();
    createEnterpriseLicense($user);
    return $user->fresh();
}

/** Crée une facture finalisée pour l'utilisateur donné. */
function createFinalizedInvoice(\App\Models\User $user, string $type = 'invoice'): Document
{
    $customer = createCustomerFor($user->currentCompany, ['name' => 'Client Acheteur']);

    $document = app(DocumentService::class)->create(
        $user->currentCompany,
        $user,
        [
            'type'        => $type,
            'customer_id' => $customer->id,
            'issue_date'  => now()->toDateString(),
            'currency'    => 'EUR',
        ],
        [[
            'description' => 'Prestation de service',
            'quantity'    => 1,
            'unit_price'  => 1000,
            'tax_rate'    => 20,
        ]]
    );

    // Finaliser
    $document->update(['finalized_at' => now()]);

    return $document->fresh();
}

/*
|--------------------------------------------------------------------------
| Tests FacturXService (unitaires via service direct)
|--------------------------------------------------------------------------
*/

it('generates valid facturx xml for a finalized invoice', function () {
    $user     = createEnterpriseOwner();
    $document = createFinalizedInvoice($user);

    $xml = app(FacturXService::class)->generateXml($document);

    expect($xml)
        ->toContain('CrossIndustryInvoice')
        ->toContain('urn:factur-x.eu:1p0:minimum')
        ->toContain('<ram:TypeCode>380</ram:TypeCode>');
});

it('xml contains seller and buyer names', function () {
    $user     = createEnterpriseOwner();
    $document = createFinalizedInvoice($user);

    $xml = app(FacturXService::class)->generateXml($document);

    expect($xml)
        ->toContain($user->currentCompany->name)
        ->toContain('Client Acheteur');
});

it('xml contains correct total amounts', function () {
    $user     = createEnterpriseOwner();
    $document = createFinalizedInvoice($user);

    $xml = app(FacturXService::class)->generateXml($document);

    // 1000 HT + 20% TVA = 1200 TTC
    expect($xml)
        ->toContain('<ram:LineTotalAmount>1000.00</ram:LineTotalAmount>')
        ->toContain('<ram:TaxTotalAmount>200.00</ram:TaxTotalAmount>')
        ->toContain('<ram:GrandTotalAmount>1200.00</ram:GrandTotalAmount>');
});

it('credit_note uses typecode 381', function () {
    $user     = createEnterpriseOwner();
    $document = createFinalizedInvoice($user, 'credit_note');

    $xml = app(FacturXService::class)->generateXml($document);

    expect($xml)->toContain('<ram:TypeCode>381</ram:TypeCode>');
});

/*
|--------------------------------------------------------------------------
| Tests HTTP
|--------------------------------------------------------------------------
*/

it('rejects non-finalized documents', function () {
    $user     = createEnterpriseOwner();
    $customer = createCustomerFor($user->currentCompany);

    $document = app(DocumentService::class)->create(
        $user->currentCompany,
        $user,
        [
            'type'        => 'invoice',
            'customer_id' => $customer->id,
            'issue_date'  => now()->toDateString(),
            'currency'    => 'EUR',
        ],
        [['description' => 'Service', 'quantity' => 1, 'unit_price' => 500, 'tax_rate' => 0]]
    );

    // document non finalisé
    $this->actingAs($user)
        ->get(route('documents.facturx', $document->id))
        ->assertStatus(422);
});

it('rejects non-enterprise plan with 403', function () {
    $user     = createUserWithCompanyAndTrial(); // plan PRO essai
    $document = createFinalizedInvoice($user);

    $this->actingAs($user)
        ->get(route('documents.facturx', $document->id))
        ->assertForbidden();
});

it('exports xml with correct content type header', function () {
    $user     = createEnterpriseOwner();
    $document = createFinalizedInvoice($user);

    $response = $this->actingAs($user)
        ->get(route('documents.facturx', $document->id));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/xml');
    $response->assertHeader('Content-Disposition', 'attachment; filename="factur-x.xml"');
    expect($response->getContent())->toContain('CrossIndustryInvoice');
});

it('preview returns json with xml key', function () {
    $user     = createEnterpriseOwner();
    $document = createFinalizedInvoice($user);

    $this->actingAs($user)
        ->getJson(route('documents.facturx.preview', $document->id))
        ->assertOk()
        ->assertJsonStructure(['xml']);
});
