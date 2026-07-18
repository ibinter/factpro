<?php

use App\Models\Document;
use App\Models\TaxConfig;
use App\Services\TaxDeclarationService;
use Carbon\Carbon;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Helpers locaux
|--------------------------------------------------------------------------
*/

/** Crée une facture finalisée pour la société donnée. */
function createTaxInvoice(\App\Models\Company $company, array $attributes = []): Document
{
    return Document::create([
        'company_id' => $company->id,
        'type' => 'invoice',
        'number' => 'FAC-TAX-'.strtoupper(Str::random(6)),
        'status' => 'sent',
        'issue_date' => now()->toDateString(),
        'due_date' => now()->addDays(30)->toDateString(),
        'currency' => 'XOF',
        'subtotal' => 100000,
        'discount_amount' => 0,
        'tax_amount' => 18000,
        'total' => 118000,
        'amount_paid' => 0,
        'finalized_at' => now(),
        ...$attributes,
    ]);
}

/*
|--------------------------------------------------------------------------
| Tests
|--------------------------------------------------------------------------
*/

it('creates a tax config for ohada_ci regime', function () {
    $user = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;

    $this->actingAs($user)
        ->post(route('tax-config.store'), [
            'tax_regime' => 'ohada_ci',
            'country' => 'CI',
            'tva_rates' => [
                ['rate' => 18, 'label' => 'TVA 18%'],
                ['rate' => 0, 'label' => 'Exonéré'],
            ],
            'has_tps' => true,
            'tps_rate' => 1,
            'has_oca' => true,
            'oca_rate' => 0.5,
            'has_timbre' => false,
            'timbre_amount' => 0,
            'declaration_frequency' => 'monthly',
        ])
        ->assertRedirect(route('tax-config.index'));

    $config = TaxConfig::where('company_id', $company->id)->first();
    expect($config)->not->toBeNull()
        ->and($config->tax_regime)->toBe('ohada_ci')
        ->and($config->country)->toBe('CI')
        ->and($config->has_tps)->toBeTrue()
        ->and($config->has_oca)->toBeTrue();
});

it('applies default rates for maroc regime (20/14/10/7/0)', function () {
    $defaults = TaxConfig::defaultsForRegime('maroc');

    $rates = array_column($defaults['tva_rates'], 'rate');
    expect($rates)->toContain(20)
        ->toContain(14)
        ->toContain(10)
        ->toContain(7)
        ->toContain(0);

    expect($defaults['country'])->toBe('MA');
    expect($defaults['has_tps'])->toBeFalse();
});

it('computes vat summary for a period', function () {
    $user = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;

    TaxConfig::create(array_merge(
        TaxConfig::defaultsForRegime('ohada_sn'),
        ['company_id' => $company->id]
    ));

    createTaxInvoice($company, [
        'subtotal' => 100000,
        'tax_amount' => 18000,
        'total' => 118000,
        'issue_date' => now()->toDateString(),
    ]);

    $service = app(TaxDeclarationService::class);
    $summary = $service->vatSummary($company, Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth());

    expect($summary['documents_count'])->toBe(1)
        ->and($summary['total_tax_due'])->toBeGreaterThan(0)
        ->and($summary['regime'])->toBe('ohada_sn');
});

it('includes tps and oca for ohada_ci', function () {
    $user = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;

    TaxConfig::create([
        'company_id' => $company->id,
        'country' => 'CI',
        'tax_regime' => 'ohada_ci',
        'tva_rates' => [['rate' => 18, 'label' => 'TVA 18%']],
        'has_tps' => true,
        'tps_rate' => 1.00,
        'has_oca' => true,
        'oca_rate' => 0.50,
        'has_timbre' => false,
        'timbre_amount' => 0,
        'declaration_frequency' => 'monthly',
    ]);

    createTaxInvoice($company, [
        'subtotal' => 100000,
        'tax_amount' => 18000,
        'total' => 118000,
    ]);

    $service = app(TaxDeclarationService::class);
    $summary = $service->vatSummary($company, Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth());

    expect($summary['tps_collected'])->toBe(1000.0)   // 1% de 100 000
        ->and($summary['oca_collected'])->toBe(500.0); // 0.5% de 100 000
});

it('exports csv with correct columns', function () {
    $user = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;

    TaxConfig::create(array_merge(
        TaxConfig::defaultsForRegime('ohada_ci'),
        ['company_id' => $company->id, 'has_tps' => true, 'has_oca' => true]
    ));

    createTaxInvoice($company);

    $response = $this->actingAs($user)
        ->get(route('tax-config.export'))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

    $csv = $response->getContent();
    expect($csv)->toContain('N° doc')
        ->toContain('TVA')
        ->toContain('TPS')
        ->toContain('OCA');
});

it('isolates tax config between companies', function () {
    $user1 = createUserWithCompanyAndTrial();
    $user2 = createUserWithCompanyAndTrial();

    $company1 = $user1->currentCompany;
    $company2 = $user2->currentCompany;

    TaxConfig::create(array_merge(
        TaxConfig::defaultsForRegime('ohada_ci'),
        ['company_id' => $company1->id]
    ));

    TaxConfig::create(array_merge(
        TaxConfig::defaultsForRegime('maroc'),
        ['company_id' => $company2->id]
    ));

    expect(TaxConfig::where('company_id', $company1->id)->value('tax_regime'))->toBe('ohada_ci');
    expect(TaxConfig::where('company_id', $company2->id)->value('tax_regime'))->toBe('maroc');
});

it('updates existing config', function () {
    $user = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;

    $config = TaxConfig::create(array_merge(
        TaxConfig::defaultsForRegime('ohada_ci'),
        ['company_id' => $company->id]
    ));

    $this->actingAs($user)
        ->put(route('tax-config.update', $config), [
            'tax_regime' => 'maroc',
            'country' => 'MA',
            'tva_rates' => [
                ['rate' => 20, 'label' => 'TVA 20%'],
                ['rate' => 0, 'label' => 'Exonéré'],
            ],
            'has_tps' => false,
            'tps_rate' => 0,
            'has_oca' => false,
            'oca_rate' => 0,
            'has_timbre' => false,
            'timbre_amount' => 0,
            'declaration_frequency' => 'quarterly',
        ])
        ->assertRedirect(route('tax-config.index'));

    $config->refresh();
    expect($config->tax_regime)->toBe('maroc')
        ->and($config->country)->toBe('MA')
        ->and($config->declaration_frequency)->toBe('quarterly');
});
