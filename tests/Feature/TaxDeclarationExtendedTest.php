<?php

use App\Models\Document;
use App\Models\TaxConfig;
use App\Services\TaxDeclarationService;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

/** Crée une facture finalisée pour la société donnée (dédiée à ces tests). */
function createExtInvoice(\App\Models\Company $company, array $attrs = []): Document
{
    return Document::create([
        'company_id'     => $company->id,
        'type'           => 'invoice',
        'number'         => 'FAC-EXT-' . strtoupper(Str::random(6)),
        'status'         => 'sent',
        'issue_date'     => now()->toDateString(),
        'due_date'       => now()->addDays(30)->toDateString(),
        'currency'       => 'XOF',
        'subtotal'       => 100000,
        'discount_amount'=> 0,
        'tax_amount'     => 18000,
        'total'          => 118000,
        'amount_paid'    => 0,
        'finalized_at'   => now(),
        ...$attrs,
    ]);
}

/*
|--------------------------------------------------------------------------
| Tests Sénégal
|--------------------------------------------------------------------------
*/

it('generates senegal tva declaration with 18 percent rate', function () {
    $user    = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;

    TaxConfig::create(array_merge(
        TaxConfig::defaultsForRegime('ohada_sn'),
        ['company_id' => $company->id]
    ));

    createExtInvoice($company, [
        'subtotal'   => 100000,
        'tax_amount' => 18000,   // TVA 18%
        'total'      => 118000,
    ]);

    $service = app(TaxDeclarationService::class);
    $decl    = $service->generateSenegalDeclaration($company->id, now()->month, now()->year);

    expect($decl['country'])->toBe('SN')
        ->and($decl['tva_collectee'])->toBe(18000.0)
        ->and($decl['ca_ttc'])->toBe(118000.0)
        ->and($decl['tva_nette'])->toBe(18000.0)
        ->and($decl)->toHaveKeys(['ca_ttc', 'tva_collectee', 'tva_deductible', 'tva_nette', 'ras_prestataires']);
});

it('generates senegal declaration with zero rate for exports', function () {
    $user    = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;

    TaxConfig::create(array_merge(
        TaxConfig::defaultsForRegime('ohada_sn'),
        ['company_id' => $company->id]
    ));

    // Facture export : TVA 0%
    createExtInvoice($company, [
        'subtotal'   => 200000,
        'tax_amount' => 0,
        'total'      => 200000,
    ]);

    $service = app(TaxDeclarationService::class);
    $decl    = $service->generateSenegalDeclaration($company->id, now()->month, now()->year);

    expect($decl['tva_collectee'])->toBe(0.0)
        ->and($decl['tva_nette'])->toBe(0.0)
        ->and($decl['ca_ttc'])->toBe(200000.0);
});

it('senegal ninea format validation', function () {
    // NINEA = 9 chiffres + 1 lettre (ex: 123456789A)
    $valid   = preg_match('/^\d{9}[A-Z]$/i', '123456789A');
    $invalid = preg_match('/^\d{9}[A-Z]$/i', '12345');

    expect($valid)->toBe(1)
        ->and($invalid)->toBe(0);
});

/*
|--------------------------------------------------------------------------
| Tests Côte d'Ivoire
|--------------------------------------------------------------------------
*/

it('generates cote_ivoire quarterly declaration with tps and oca', function () {
    $user    = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;

    TaxConfig::create([
        'company_id'           => $company->id,
        'country'              => 'CI',
        'tax_regime'           => 'ohada_ci',
        'tva_rates'            => [['rate' => 18, 'label' => 'TVA 18%']],
        'has_tps'              => true,
        'tps_rate'             => 1.00,
        'has_oca'              => true,
        'oca_rate'             => 0.50,
        'has_timbre'           => false,
        'timbre_amount'        => 0,
        'declaration_frequency'=> 'quarterly',
    ]);

    // Facture dans le trimestre courant
    createExtInvoice($company, [
        'subtotal'   => 100000,
        'tax_amount' => 18000,
        'total'      => 118000,
        'issue_date' => now()->toDateString(),
    ]);

    $quarter = (int) ceil(now()->month / 3);
    $service = app(TaxDeclarationService::class);
    $decl    = $service->generateCoteIvoireDeclaration($company->id, $quarter, now()->year);

    expect($decl['country'])->toBe('CI')
        ->and($decl['ca_ht'])->toBe(100000.0)
        ->and($decl['tva_collectee'])->toBe(18000.0)
        ->and($decl['tps'])->toBe(1000.0)    // 1% de 100 000
        ->and($decl['oca'])->toBe(500.0)     // 0.5% de 100 000
        ->and($decl)->toHaveKeys(['ca_ht', 'tva_collectee', 'tps', 'oca', 'tva_nette', 'total_a_payer']);
});

/*
|--------------------------------------------------------------------------
| Tests Algérie
|--------------------------------------------------------------------------
*/

it('algerie vat rate is 19 percent', function () {
    $defaults = TaxConfig::defaultsForRegime('algerie');
    $rates    = array_column($defaults['tva_rates'], 'rate');

    expect($rates)->toContain(19)
        ->toContain(9)
        ->toContain(0)
        ->and($defaults['country'])->toBe('DZ');
});

it('generates algerie monthly g50 with tap', function () {
    $user    = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;

    TaxConfig::create(array_merge(
        TaxConfig::defaultsForRegime('algerie'),
        ['company_id' => $company->id, 'declaration_frequency' => 'monthly']
    ));

    createExtInvoice($company, [
        'subtotal'   => 100000,
        'tax_amount' => 19000,   // TVA 19%
        'total'      => 119000,
        'issue_date' => now()->toDateString(),
    ]);

    $service = app(TaxDeclarationService::class);
    $decl    = $service->generateAlgerieDeclaration($company->id, now()->month, now()->year);

    expect($decl['country'])->toBe('DZ')
        ->and($decl['ca_ht'])->toBe(100000.0)
        ->and($decl['tva_collectee'])->toBe(19000.0)
        ->and($decl['tap_a_payer'])->toBe(2000.0)   // 2% de 100 000
        ->and($decl['total'])->toBe(21000.0)         // TVA nette + TAP
        ->and($decl)->toHaveKeys(['ca_ht', 'tva_collectee', 'tva_deductible', 'tap', 'tva_nette', 'tap_a_payer', 'total']);
});

it('algerie nif format validation', function () {
    // NIF Algérie = 15 chiffres
    $valid   = preg_match('/^\d{15}$/', '123456789012345');
    $invalid = preg_match('/^\d{15}$/', '1234');

    expect($valid)->toBe(1)
        ->and($invalid)->toBe(0);
});

/*
|--------------------------------------------------------------------------
| Tests transversaux
|--------------------------------------------------------------------------
*/

it('declaration filters only company documents', function () {
    $user1    = createUserWithCompanyAndTrial();
    $user2    = createUserWithCompanyAndTrial();
    $company1 = $user1->currentCompany;
    $company2 = $user2->currentCompany;

    // Facture company1 uniquement
    createExtInvoice($company1, ['subtotal' => 50000, 'tax_amount' => 9000, 'total' => 59000]);
    // Facture company2 uniquement
    createExtInvoice($company2, ['subtotal' => 80000, 'tax_amount' => 14400, 'total' => 94400]);

    $service = app(TaxDeclarationService::class);
    $decl1   = $service->generateSenegalDeclaration($company1->id, now()->month, now()->year);
    $decl2   = $service->generateSenegalDeclaration($company2->id, now()->month, now()->year);

    expect($decl1['ca_ttc'])->toBe(59000.0)
        ->and($decl2['ca_ttc'])->toBe(94400.0)
        ->and($decl1['ca_ttc'])->not->toBe($decl2['ca_ttc']);
});

it('zero rate documents excluded from vat payable', function () {
    $user    = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;

    TaxConfig::create(array_merge(
        TaxConfig::defaultsForRegime('algerie'),
        ['company_id' => $company->id]
    ));

    // Facture export taux zéro
    createExtInvoice($company, [
        'subtotal'   => 300000,
        'tax_amount' => 0,
        'total'      => 300000,
    ]);

    $service = app(TaxDeclarationService::class);
    $decl    = $service->generateAlgerieDeclaration($company->id, now()->month, now()->year);

    expect($decl['tva_collectee'])->toBe(0.0)
        ->and($decl['tva_nette'])->toBe(0.0);
});

it('declaration sums correct across multiple invoices', function () {
    $user    = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;

    TaxConfig::create(array_merge(
        TaxConfig::defaultsForRegime('ohada_sn'),
        ['company_id' => $company->id]
    ));

    // 3 factures
    createExtInvoice($company, ['subtotal' => 100000, 'tax_amount' => 18000, 'total' => 118000]);
    createExtInvoice($company, ['subtotal' => 200000, 'tax_amount' => 36000, 'total' => 236000]);
    createExtInvoice($company, ['subtotal' => 50000,  'tax_amount' => 0,     'total' => 50000]);

    $service = app(TaxDeclarationService::class);
    $decl    = $service->generateSenegalDeclaration($company->id, now()->month, now()->year);

    expect($decl['ca_ttc'])->toBe(404000.0)      // 118000 + 236000 + 50000
        ->and($decl['tva_collectee'])->toBe(54000.0);  // 18000 + 36000 + 0
});
