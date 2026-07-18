<?php

use App\Models\Company;
use App\Models\Customer;
use App\Models\Document;
use App\Services\OssVatService;
use Illuminate\Support\Str;

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function makeOssInvoice(Company $company, Customer $customer, array $attrs = []): Document
{
    return Document::create([
        'company_id'      => $company->id,
        'customer_id'     => $customer->id,
        'type'            => 'invoice',
        'number'          => 'OSS-' . strtoupper(Str::random(8)),
        'status'          => 'sent',
        'issue_date'      => now()->toDateString(),
        'due_date'        => now()->addDays(30)->toDateString(),
        'currency'        => 'EUR',
        'subtotal'        => 1000.00,
        'discount_amount' => 0,
        'tax_amount'      => 200.00,
        'total'           => 1200.00,
        'amount_paid'     => 0,
        'finalized_at'    => now(),
        ...$attrs,
    ]);
}

// ---------------------------------------------------------------------------
// Service unit tests
// ---------------------------------------------------------------------------

it('returns correct vat rate for france', function () {
    $service = app(OssVatService::class);

    expect($service->getVatRate('FR'))->toBe(20.0);
    expect($service->getVatRate('FR', 'reduced'))->toBe(10.0);
    expect($service->getVatRate('FR', 'super_reduced'))->toBe(5.5);
});

it('returns correct vat rate for germany', function () {
    $service = app(OssVatService::class);

    expect($service->getVatRate('DE'))->toBe(19.0);
    expect($service->getVatRate('DE', 'reduced'))->toBe(7.0);
});

it('validates french vat number format', function () {
    $service = app(OssVatService::class);

    // Valid FR VAT: FR + 2 alphanum + 9 digits
    expect($service->validateVatNumber('FRAA123456789', 'FR'))->toBeTrue();
    expect($service->validateVatNumber('FR12345678901', 'FR'))->toBeTrue();
});

it('validates german vat number format', function () {
    $service = app(OssVatService::class);

    // Valid DE VAT: DE + 9 digits
    expect($service->validateVatNumber('DE123456789', 'DE'))->toBeTrue();
});

it('rejects invalid vat number', function () {
    $service = app(OssVatService::class);

    expect($service->validateVatNumber('DE12345', 'DE'))->toBeFalse();
    expect($service->validateVatNumber('FRTOOSHORT', 'FR'))->toBeFalse();
    expect($service->validateVatNumber('XX999', 'DE'))->toBeFalse();
});

// ---------------------------------------------------------------------------
// Declaration calculation tests
// ---------------------------------------------------------------------------

it('calculates oss declaration by country', function () {
    $user    = createUserWithCompany();
    $company = $user->currentCompany;

    $customerFR = createCustomerFor($company, ['country' => 'FR', 'currency' => 'EUR']);
    makeOssInvoice($company, $customerFR, ['subtotal' => 1000.00, 'tax_amount' => 200.00]);

    $service = app(OssVatService::class);
    $quarter = (int) ceil(now()->month / 3);
    $result  = $service->calculateOssDeclaration($company->id, $quarter, now()->year);

    expect($result)->toHaveKeys(['by_country', 'total_vat', 'period']);
    expect($result['by_country'])->not->toBeEmpty();

    $fr = collect($result['by_country'])->firstWhere('country', 'FR');
    expect($fr)->not->toBeNull();
    expect($fr['vat_rate'])->toBe(20.0);
    expect($fr['base_ht'])->toBe(1000.0);
    expect($result['total_vat'])->toBeGreaterThan(0);
});

it('groups invoices by customer country', function () {
    $user    = createUserWithCompany();
    $company = $user->currentCompany;

    $customerFR = createCustomerFor($company, ['country' => 'FR', 'currency' => 'EUR']);
    $customerDE = createCustomerFor($company, ['country' => 'DE', 'currency' => 'EUR']);

    makeOssInvoice($company, $customerFR, ['subtotal' => 500.00]);
    makeOssInvoice($company, $customerFR, ['subtotal' => 500.00]);
    makeOssInvoice($company, $customerDE, ['subtotal' => 1000.00]);

    $service = app(OssVatService::class);
    $quarter = (int) ceil(now()->month / 3);
    $result  = $service->calculateOssDeclaration($company->id, $quarter, now()->year);

    $countries = collect($result['by_country'])->pluck('country')->toArray();
    expect($countries)->toContain('FR');
    expect($countries)->toContain('DE');

    $fr = collect($result['by_country'])->firstWhere('country', 'FR');
    expect($fr['base_ht'])->toBe(1000.0);
});

it('declaration returns zero for no eu invoices', function () {
    $user    = createUserWithCompany();
    $company = $user->currentCompany;

    // Customer from a non-EU country
    $customerMA = createCustomerFor($company, ['country' => 'MA', 'currency' => 'MAD']);
    makeOssInvoice($company, $customerMA, ['subtotal' => 5000.00]);

    $service = app(OssVatService::class);
    $result  = $service->calculateOssDeclaration($company->id, 1, now()->year);

    expect($result['by_country'])->toBeEmpty();
    expect($result['total_vat'])->toBe(0.0);
});

it('detects below threshold status', function () {
    $user    = createUserWithCompany();
    $company = $user->currentCompany;

    // No EU invoices → below threshold
    $service = app(OssVatService::class);
    expect($service->isBelowThreshold($company->id, now()->year))->toBeTrue();

    // Add EU invoice above 10 000 €
    $customerFR = createCustomerFor($company, ['country' => 'FR', 'currency' => 'EUR']);
    makeOssInvoice($company, $customerFR, ['subtotal' => 15000.00]);

    expect($service->isBelowThreshold($company->id, now()->year))->toBeFalse();
});

it('isolates between companies', function () {
    $user1    = createUserWithCompany();
    $company1 = $user1->currentCompany;

    $user2    = createUserWithCompany();
    $company2 = $user2->currentCompany;

    $customerFR = createCustomerFor($company1, ['country' => 'FR', 'currency' => 'EUR']);
    makeOssInvoice($company1, $customerFR, ['subtotal' => 5000.00]);

    $service = app(OssVatService::class);
    $quarter = (int) ceil(now()->month / 3);

    $result1 = $service->calculateOssDeclaration($company1->id, $quarter, now()->year);
    $result2 = $service->calculateOssDeclaration($company2->id, $quarter, now()->year);

    expect($result1['total_vat'])->toBeGreaterThan(0);
    expect($result2['total_vat'])->toBe(0.0);
});
