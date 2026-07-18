<?php

use App\Models\Company;
use App\Models\Customer;
use App\Models\Document;
use App\Models\License;
use App\Models\Plan;
use App\Models\User;
use App\Services\AccountingExportService;
use App\Services\LicenseService;
use Carbon\Carbon;
use Illuminate\Support\Str;

// ---------------------------------------------------------------------------
// Helpers locaux
// ---------------------------------------------------------------------------

/** Crée une licence active business pour l'utilisateur. */
function createExportLicense(User $user): License
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

/** Crée une facture finalisée pour les tests d'export. */
function makeExportInvoice(Company $company, ?Customer $customer = null, array $attrs = []): Document
{
    return Document::create([
        'company_id'      => $company->id,
        'customer_id'     => $customer?->id,
        'type'            => 'invoice',
        'number'          => 'FAC-'.strtoupper(Str::random(8)),
        'status'          => 'sent',
        'issue_date'      => now()->toDateString(),
        'due_date'        => now()->addDays(30)->toDateString(),
        'currency'        => 'EUR',
        'subtotal'        => 1000.00,
        'discount_amount' => 0,
        'tax_amount'      => 180.00,
        'total'           => 1180.00,
        'amount_paid'     => 0,
        'finalized_at'    => now(),
        ...$attrs,
    ]);
}

// ---------------------------------------------------------------------------
// Setup
// ---------------------------------------------------------------------------

beforeEach(function () {
    $this->user     = createUserWithCompany();
    $this->company  = $this->user->currentCompany;
    $this->customer = createCustomerFor($this->company, ['name' => 'Dupont SA']);
    createExportLicense($this->user);
    $this->service  = app(AccountingExportService::class);
    $this->from     = Carbon::now()->startOfMonth();
    $this->to       = Carbon::now()->endOfMonth();
});

// ---------------------------------------------------------------------------
// Service tests
// ---------------------------------------------------------------------------

it('exports sage format with correct structure', function () {
    makeExportInvoice($this->company, $this->customer);

    $csv = $this->service->exportSage($this->company->id, $this->from, $this->to);

    expect($csv)
        ->toContain('JournalCode;JournalLib;EcritureNum;EcritureDate')
        ->toContain('VT;Ventes;')
        ->toContain('411');
});

it('exports quickbooks iif format', function () {
    makeExportInvoice($this->company, $this->customer);

    $iif = $this->service->exportQuickBooks($this->company->id, $this->from, $this->to);

    expect($iif)
        ->toContain('!TRNS')
        ->toContain('!SPL')
        ->toContain('TRNS')
        ->toContain('ENDTRNS')
        ->toContain('INVOICE')
        ->toContain('Accounts Receivable');
});

it('exports pennylane json format', function () {
    makeExportInvoice($this->company, $this->customer);

    $data = $this->service->exportPennylane($this->company->id, $this->from, $this->to);

    expect($data)->toHaveKey('ledger_entries');
    expect($data['ledger_entries'])->toHaveCount(1);

    $entry = $data['ledger_entries'][0];
    expect($entry)
        ->toHaveKeys(['date', 'label', 'reference', 'currency', 'lines']);
    expect($entry['lines'])->not->toBeEmpty();
});

it('export contains correct debit credit amounts', function () {
    makeExportInvoice($this->company, $this->customer, [
        'subtotal'   => 1000.00,
        'tax_amount' => 180.00,
        'total'      => 1180.00,
    ]);

    $data  = $this->service->exportPennylane($this->company->id, $this->from, $this->to);
    $entry = $data['ledger_entries'][0];

    // Client line: debit 1180
    $clientLine = collect($entry['lines'])->first(fn ($l) => str_starts_with($l['account_number'], '411'));
    expect($clientLine['debit'])->toBe(1180.0);
    expect($clientLine['credit'])->toBe(0);

    // Sales line: credit 1000
    $salesLine = collect($entry['lines'])->first(fn ($l) => str_starts_with($l['account_number'], '701'));
    expect($salesLine['credit'])->toBe(1000.0);
    expect($salesLine['debit'])->toBe(0);

    // VAT line: credit 180
    $vatLine = collect($entry['lines'])->first(fn ($l) => $l['account_number'] === '445710');
    expect($vatLine['credit'])->toBe(180.0);
});

it('export filters by date range', function () {
    // Invoice dans la plage
    makeExportInvoice($this->company, $this->customer, [
        'issue_date' => now()->toDateString(),
    ]);
    // Invoice hors plage
    makeExportInvoice($this->company, $this->customer, [
        'issue_date' => now()->subYear()->toDateString(),
    ]);

    $data = $this->service->exportPennylane($this->company->id, $this->from, $this->to);
    expect($data['ledger_entries'])->toHaveCount(1);
});

it('export includes vat lines', function () {
    makeExportInvoice($this->company, $this->customer, [
        'tax_amount' => 180.00,
        'total'      => 1180.00,
    ]);

    // Sage
    $csv = $this->service->exportSage($this->company->id, $this->from, $this->to);
    expect($csv)->toContain('445710');

    // Pennylane
    $data    = $this->service->exportPennylane($this->company->id, $this->from, $this->to);
    $vatLine = collect($data['ledger_entries'][0]['lines'])->first(fn ($l) => $l['account_number'] === '445710');
    expect($vatLine)->not->toBeNull();
});

it('preview returns first 10 lines', function () {
    // Créer plusieurs factures
    for ($i = 0; $i < 5; $i++) {
        makeExportInvoice($this->company, $this->customer);
    }

    $lines = $this->service->preview($this->company->id, $this->from, $this->to, 'sage');
    expect(count($lines))->toBeLessThanOrEqual(10);
    expect($lines)->not->toBeEmpty();
});

it('export handles documents without vat', function () {
    makeExportInvoice($this->company, $this->customer, [
        'subtotal'   => 1000.00,
        'tax_amount' => 0,
        'total'      => 1000.00,
    ]);

    $data  = $this->service->exportPennylane($this->company->id, $this->from, $this->to);
    $entry = $data['ledger_entries'][0];

    $vatLine = collect($entry['lines'])->first(fn ($l) => $l['account_number'] === '445710');
    expect($vatLine)->toBeNull();

    // 2 lignes seulement (client + vente)
    expect($entry['lines'])->toHaveCount(2);
});

it('isolates exports between companies', function () {
    $otherUser    = createUserWithCompany();
    $otherCompany = $otherUser->currentCompany;
    $otherCustomer = createCustomerFor($otherCompany, ['name' => 'Autre Client']);

    makeExportInvoice($this->company, $this->customer);
    makeExportInvoice($otherCompany, $otherCustomer);

    $data      = $this->service->exportPennylane($this->company->id, $this->from, $this->to);
    $otherData = $this->service->exportPennylane($otherCompany->id, $this->from, $this->to);

    expect($data['ledger_entries'])->toHaveCount(1);
    expect($otherData['ledger_entries'])->toHaveCount(1);
    // Les références ne se mélangent pas
    $ref1 = $data['ledger_entries'][0]['reference'];
    $ref2 = $otherData['ledger_entries'][0]['reference'];
    expect($ref1)->not->toBe($ref2);
});

it('export returns downloadable file response', function () {
    makeExportInvoice($this->company, $this->customer);

    $this->actingAs($this->user)
        ->post(route('accounting.export.sage'), [
            'from' => $this->from->toDateString(),
            'to'   => $this->to->toDateString(),
        ])
        ->assertOk()
        ->assertHeader('content-disposition');
});

// ---------------------------------------------------------------------------
// Controller tests (HTTP)
// ---------------------------------------------------------------------------

it('index page renders for authenticated user with license', function () {
    $this->actingAs($this->user)
        ->get(route('accounting.export.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Accounting/Export'));
});

it('quickbooks export returns iif file', function () {
    makeExportInvoice($this->company, $this->customer);

    $this->actingAs($this->user)
        ->post(route('accounting.export.quickbooks'), [
            'from' => $this->from->toDateString(),
            'to'   => $this->to->toDateString(),
        ])
        ->assertOk()
        ->assertHeader('content-disposition');
});

it('pennylane export returns json file', function () {
    makeExportInvoice($this->company, $this->customer);

    $this->actingAs($this->user)
        ->post(route('accounting.export.pennylane'), [
            'from' => $this->from->toDateString(),
            'to'   => $this->to->toDateString(),
        ])
        ->assertOk()
        ->assertHeader('content-disposition');
});

it('preview endpoint returns json lines', function () {
    makeExportInvoice($this->company, $this->customer);

    $this->actingAs($this->user)
        ->postJson(route('accounting.export.preview'), [
            'from'   => $this->from->toDateString(),
            'to'     => $this->to->toDateString(),
            'format' => 'sage',
        ])
        ->assertOk()
        ->assertJsonStructure(['lines']);
});

it('preview validates format parameter', function () {
    $this->actingAs($this->user)
        ->postJson(route('accounting.export.preview'), [
            'from'   => $this->from->toDateString(),
            'to'     => $this->to->toDateString(),
            'format' => 'invalid_format',
        ])
        ->assertUnprocessable();
});
