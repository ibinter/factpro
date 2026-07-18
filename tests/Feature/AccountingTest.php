<?php

use App\Models\Company;
use App\Models\Customer;
use App\Models\Document;
use App\Models\DocumentPayment;
use App\Models\License;
use App\Models\Plan;
use App\Models\User;
use App\Services\LicenseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/** Crée une licence active sur un plan donné (helper local au module compta). */
function createAccountingLicenseFor(User $user, string $planCode): License
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

/** Crée un document comptable (facture finalisée par défaut). */
function createAccountingInvoice(Company $company, ?Customer $customer = null, array $attributes = []): Document
{
    return Document::create([
        'company_id' => $company->id,
        'customer_id' => $customer?->id,
        'type' => 'invoice',
        'number' => 'FAC-'.strtoupper(Str::random(8)),
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

beforeEach(function () {
    $this->user = createUserWithCompany();
    $this->company = $this->user->currentCompany;
    $this->customer = createCustomerFor($this->company, ['name' => 'Client Compta']);
});

/*
|--------------------------------------------------------------------------
| Gate BUSINESS/ENTERPRISE
|--------------------------------------------------------------------------
*/

it('shows the accounting page without access for a PRO (trial) user', function () {
    $user = createUserWithCompanyAndTrial();

    $this->actingAs($user)
        ->get(route('accounting.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Accounting/Index')
            ->where('hasAccess', false)
            ->where('data', null));
});

it('forbids FEC and CSV exports for a PRO (trial) user', function () {
    $user = createUserWithCompanyAndTrial();

    $this->actingAs($user)
        ->get(route('accounting.fec', ['year' => now()->year]))
        ->assertForbidden();

    $this->actingAs($user)
        ->get(route('accounting.journal.csv'))
        ->assertForbidden();
});

it('grants access to the accounting page for a BUSINESS user', function () {
    createAccountingLicenseFor($this->user, 'business');

    $this->actingAs($this->user)
        ->get(route('accounting.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Accounting/Index')
            ->where('hasAccess', true)
            ->where('tab', 'journal'));
});

/*
|--------------------------------------------------------------------------
| Journal des ventes
|--------------------------------------------------------------------------
*/

it('lists finalized invoices positive and credit notes negative with correct totals', function () {
    createAccountingLicenseFor($this->user, 'business');

    createAccountingInvoice($this->company, $this->customer, [
        'number' => 'FAC-0001',
        'issue_date' => now()->startOfYear()->addMonths(2)->toDateString(),
    ]);
    createAccountingInvoice($this->company, $this->customer, [
        'type' => 'credit_note',
        'number' => 'AV-0001',
        'issue_date' => now()->startOfYear()->addMonths(3)->toDateString(),
        'subtotal' => 20000,
        'tax_amount' => 3600,
        'total' => 23600,
    ]);

    $response = $this->actingAs($this->user)
        ->get(route('accounting.index', [
            'tab' => 'journal',
            'from' => now()->startOfYear()->toDateString(),
            'to' => now()->endOfYear()->toDateString(),
        ]))
        ->assertOk();

    $response->assertInertia(fn ($page) => $page
        ->component('Accounting/Index')
        ->where('tab', 'journal')
        ->where('data.lines.0.piece', 'FAC-0001')
        ->where('data.lines.0.ht', 100000)
        ->where('data.lines.0.tva', 18000)
        ->where('data.lines.0.ttc', 118000)
        ->where('data.lines.1.piece', 'AV-0001')
        ->where('data.lines.1.ht', -20000)
        ->where('data.lines.1.tva', -3600)
        ->where('data.lines.1.ttc', -23600)
        ->where('data.totals.ht', 80000)
        ->where('data.totals.tva', 14400)
        ->where('data.totals.ttc', 94400));
});

it('excludes drafts, non-finalized and cancelled documents from the journal', function () {
    createAccountingLicenseFor($this->user, 'business');

    // Brouillon non finalisé
    createAccountingInvoice($this->company, $this->customer, [
        'number' => 'FAC-DRAFT',
        'status' => 'draft',
        'finalized_at' => null,
    ]);
    // Facture non finalisée (envoyée mais sans horodatage certifié)
    createAccountingInvoice($this->company, $this->customer, [
        'number' => 'FAC-NOFINAL',
        'finalized_at' => null,
    ]);
    // Facture finalisée puis annulée
    createAccountingInvoice($this->company, $this->customer, [
        'number' => 'FAC-CANCEL',
        'status' => 'cancelled',
    ]);
    // Devis (non facturable)
    createAccountingInvoice($this->company, $this->customer, [
        'type' => 'quote',
        'number' => 'DEV-0001',
    ]);
    // La seule écriture attendue
    createAccountingInvoice($this->company, $this->customer, ['number' => 'FAC-OK']);

    $this->actingAs($this->user)
        ->get(route('accounting.index', ['tab' => 'journal']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('data.lines.0.piece', 'FAC-OK')
            ->has('data.lines', 1)
            ->where('data.totals.ttc', 118000));
});

/*
|--------------------------------------------------------------------------
| Balance âgée
|--------------------------------------------------------------------------
*/

it('buckets outstanding invoices by overdue age from due date', function () {
    createAccountingLicenseFor($this->user, 'business');

    createAccountingInvoice($this->company, $this->customer, [
        'number' => 'FAC-B30',
        'due_date' => now()->subDays(10)->toDateString(),
        'total' => 118000,
    ]);
    createAccountingInvoice($this->company, $this->customer, [
        'number' => 'FAC-B60',
        'due_date' => now()->subDays(45)->toDateString(),
        'subtotal' => 50000, 'tax_amount' => 9000, 'total' => 59000,
    ]);
    createAccountingInvoice($this->company, $this->customer, [
        'number' => 'FAC-B90P',
        'due_date' => now()->subDays(100)->toDateString(),
        'subtotal' => 10000, 'tax_amount' => 1800, 'total' => 11800,
    ]);
    // Non échue → current
    createAccountingInvoice($this->company, $this->customer, [
        'number' => 'FAC-CUR',
        'due_date' => now()->addDays(15)->toDateString(),
        'subtotal' => 5000, 'tax_amount' => 900, 'total' => 5900,
    ]);
    // Payée intégralement → exclue
    createAccountingInvoice($this->company, $this->customer, [
        'number' => 'FAC-PAID',
        'due_date' => now()->subDays(10)->toDateString(),
        'amount_paid' => 118000,
    ]);

    $this->actingAs($this->user)
        ->get(route('accounting.index', ['tab' => 'aged']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('tab', 'aged')
            ->has('data.rows', 1)
            ->where('data.rows.0.client', 'Client Compta')
            ->where('data.rows.0.current', 5900)
            ->where('data.rows.0.b0_30', 118000)
            ->where('data.rows.0.b31_60', 59000)
            ->where('data.rows.0.b61_90', 0)
            ->where('data.rows.0.b90_plus', 11800)
            ->where('data.rows.0.total', 194700)
            ->where('data.totals.total', 194700));
});

it('deducts credit notes from the aged balance and hides settled customers', function () {
    createAccountingLicenseFor($this->user, 'business');

    // Client soldé par un avoir → ne doit pas apparaître
    $settled = createCustomerFor($this->company, ['name' => 'Client Soldé']);
    createAccountingInvoice($this->company, $settled, [
        'number' => 'FAC-SET',
        'due_date' => now()->subDays(10)->toDateString(),
    ]);
    createAccountingInvoice($this->company, $settled, [
        'type' => 'credit_note',
        'number' => 'AV-SET',
        'due_date' => now()->subDays(10)->toDateString(),
    ]);

    // Client avec un encours réduit par un avoir partiel
    createAccountingInvoice($this->company, $this->customer, [
        'number' => 'FAC-NET',
        'due_date' => now()->subDays(10)->toDateString(),
    ]);
    createAccountingInvoice($this->company, $this->customer, [
        'type' => 'credit_note',
        'number' => 'AV-NET',
        'due_date' => now()->subDays(10)->toDateString(),
        'subtotal' => 20000, 'tax_amount' => 3600, 'total' => 23600,
    ]);

    $this->actingAs($this->user)
        ->get(route('accounting.index', ['tab' => 'aged']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('data.rows', 1)
            ->where('data.rows.0.client', 'Client Compta')
            ->where('data.rows.0.b0_30', 94400)
            ->where('data.rows.0.total', 94400));
});

/*
|--------------------------------------------------------------------------
| Récapitulatif TVA & compte de résultat
|--------------------------------------------------------------------------
*/

it('summarizes VAT by month over the period', function () {
    createAccountingLicenseFor($this->user, 'business');

    $january = now()->startOfYear();
    $february = now()->startOfYear()->addMonth();

    createAccountingInvoice($this->company, $this->customer, [
        'number' => 'FAC-JAN', 'issue_date' => $january->toDateString(),
    ]);
    createAccountingInvoice($this->company, $this->customer, [
        'number' => 'FAC-FEV', 'issue_date' => $february->toDateString(),
        'subtotal' => 50000, 'tax_amount' => 9000, 'total' => 59000,
    ]);
    createAccountingInvoice($this->company, $this->customer, [
        'type' => 'credit_note', 'number' => 'AV-FEV',
        'issue_date' => $february->addDays(5)->toDateString(),
        'subtotal' => 10000, 'tax_amount' => 1800, 'total' => 11800,
    ]);

    $this->actingAs($this->user)
        ->get(route('accounting.index', [
            'tab' => 'vat',
            'from' => now()->startOfYear()->toDateString(),
            'to' => now()->endOfYear()->toDateString(),
        ]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('data.rows', 2)
            ->where('data.rows.0.month', now()->startOfYear()->format('Y-m'))
            ->where('data.rows.0.tva', 18000)
            ->where('data.rows.1.month', now()->startOfYear()->addMonth()->format('Y-m'))
            ->where('data.rows.1.ht', 40000)
            ->where('data.rows.1.tva', 7200)
            ->where('data.totals.tva', 25200));
});

it('computes the simplified profit and loss', function () {
    createAccountingLicenseFor($this->user, 'business');

    createAccountingInvoice($this->company, $this->customer, ['number' => 'FAC-PNL']);
    createAccountingInvoice($this->company, $this->customer, [
        'type' => 'credit_note', 'number' => 'AV-PNL',
        'subtotal' => 20000, 'tax_amount' => 3600, 'total' => 23600,
    ]);

    $expectedExpenses = 0;
    if (Schema::hasTable('expenses')) {
        DB::table('expenses')->insert([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'category' => 'fournitures',
            'description' => 'Achat fournitures',
            'amount' => 30000,
            'currency' => 'XOF',
            'expense_date' => now()->toDateString(),
            'status' => 'approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        // Dépense refusée : ignorée
        DB::table('expenses')->insert([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'category' => 'repas',
            'description' => 'Note refusée',
            'amount' => 99999,
            'currency' => 'XOF',
            'expense_date' => now()->toDateString(),
            'status' => 'rejected',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $expectedExpenses = 30000;
    }

    $this->actingAs($this->user)
        ->get(route('accounting.index', ['tab' => 'pnl']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('data.revenue', 80000)
            ->where('data.expenses', $expectedExpenses)
            ->where('data.result', 80000 - $expectedExpenses));
});

/*
|--------------------------------------------------------------------------
| Export FEC
|--------------------------------------------------------------------------
*/

it('generates a balanced FEC file with the exact header and FR formats', function () {
    createAccountingLicenseFor($this->user, 'business');
    $this->company->update(['tax_id' => 'CI-123 456 789']);

    $invoice = createAccountingInvoice($this->company, $this->customer, [
        'number' => 'FAC-FEC1',
        'issue_date' => now()->year.'-03-15',
    ]);
    createAccountingInvoice($this->company, $this->customer, [
        'type' => 'credit_note',
        'number' => 'AV-FEC1',
        'issue_date' => now()->year.'-04-10',
        'subtotal' => 20000, 'tax_amount' => 3600, 'total' => 23600,
    ]);
    DocumentPayment::create([
        'company_id' => $this->company->id,
        'document_id' => $invoice->id,
        'amount' => 50000,
        'currency' => 'XOF',
        'method' => 'bank_transfer',
        'reference' => 'VIR-001',
        'paid_at' => now()->year.'-05-02',
    ]);

    $response = $this->actingAs($this->user)
        ->get(route('accounting.fec', ['year' => now()->year]))
        ->assertOk()
        ->assertHeader('content-type', 'text/plain; charset=UTF-8');

    $response->assertDownload('CI123456789FEC'.now()->year.'1231.txt');

    $content = $response->streamedContent();
    $lines = array_values(array_filter(explode("\r\n", $content), fn ($l) => $l !== ''));

    // En-tête réglementaire exact
    expect($lines[0])->toBe(
        "JournalCode\tJournalLib\tEcritureNum\tEcritureDate\tCompteNum\tCompteLib\t".
        "CompAuxNum\tCompAuxLib\tPieceRef\tPieceDate\tEcritureLib\tDebit\tCredit\t".
        "EcritureLet\tDateLet\tValidDate\tMontantdevise\tIdevise"
    );

    // 3 lignes par document (TVA ≠ 0) + 2 lignes par encaissement
    expect(count($lines))->toBe(1 + 3 + 3 + 2);

    // Équilibre global : Σ débits = Σ crédits
    $debits = 0.0;
    $credits = 0.0;
    foreach (array_slice($lines, 1) as $line) {
        $columns = explode("\t", $line);
        expect(count($columns))->toBe(18);
        expect($columns[3])->toMatch('/^\d{8}$/');  // EcritureDate AAAAMMJJ
        expect($columns[11])->toMatch('/^\d+,\d{2}$/'); // Débit "123,45"
        expect($columns[12])->toMatch('/^\d+,\d{2}$/'); // Crédit "123,45"
        $debits += (float) str_replace(',', '.', $columns[11]);
        $credits += (float) str_replace(',', '.', $columns[12]);
    }
    expect(round($debits, 2))->toBe(round($credits, 2))
        ->and(round($debits, 2))->toBe(118000.0 + 23600.0 + 50000.0);

    // Écriture de la facture : 411 au débit TTC, montants FR, compte auxiliaire client
    expect($content)
        ->toContain("VE\tJournal des ventes")
        ->toContain('118000,00')
        ->toContain(now()->year.'0315')
        ->toContain('CLI'.str_pad((string) $this->customer->id, 6, '0', STR_PAD_LEFT))
        ->toContain("707\tVentes de marchandises")
        ->toContain("44571\tTVA collectée")
        ->toContain("BQ\tBanque")
        ->toContain('50000,00');
});

it('excludes non-finalized documents from the FEC', function () {
    createAccountingLicenseFor($this->user, 'business');

    createAccountingInvoice($this->company, $this->customer, [
        'number' => 'FAC-NOFEC',
        'status' => 'draft',
        'finalized_at' => null,
    ]);

    $content = $this->actingAs($this->user)
        ->get(route('accounting.fec', ['year' => now()->year]))
        ->assertOk()
        ->streamedContent();

    $lines = array_values(array_filter(explode("\r\n", $content), fn ($l) => $l !== ''));

    expect(count($lines))->toBe(1) // en-tête seul
        ->and($content)->not->toContain('FAC-NOFEC');
});

/*
|--------------------------------------------------------------------------
| Export CSV
|--------------------------------------------------------------------------
*/

it('downloads the sales journal as a CSV with UTF-8 BOM and semicolons', function () {
    createAccountingLicenseFor($this->user, 'business');

    createAccountingInvoice($this->company, $this->customer, ['number' => 'FAC-CSV1']);

    $response = $this->actingAs($this->user)
        ->get(route('accounting.journal.csv', [
            'from' => now()->startOfYear()->toDateString(),
            'to' => now()->endOfYear()->toDateString(),
        ]))
        ->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');

    $content = $response->streamedContent();

    expect(str_starts_with($content, "\xEF\xBB\xBF"))->toBeTrue()
        ->and($content)->toContain('Date;Pièce;Client;Type;Statut;HT;TVA;TTC')
        ->and($content)->toContain('FAC-CSV1')
        ->and($content)->toContain('118000,00')
        ->and($content)->toContain('TOTAL;');
});
