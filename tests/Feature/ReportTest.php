<?php

use App\Models\Customer;
use App\Models\Document;
use App\Models\DocumentPayment;
use App\Models\License;
use App\Models\Plan;
use App\Models\Product;
use App\Models\User;
use App\Services\LicenseService;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Helpers (préfixe createReport… pour éviter toute collision globale)
|--------------------------------------------------------------------------
*/

/** Crée un document finalisé (facture par défaut) pour la société de l'utilisateur. */
function createReportDocument(User $user, array $attributes = []): Document
{
    return Document::create([
        'company_id' => $user->current_company_id,
        'type' => 'invoice',
        'number' => 'RPT-'.strtoupper(Str::random(10)),
        'status' => 'sent',
        'issue_date' => now()->toDateString(),
        'currency' => 'XOF',
        'subtotal' => 0,
        'tax_amount' => 0,
        'total' => 0,
        'amount_paid' => 0,
        'finalized_at' => now(),
        ...$attributes,
    ]);
}

/** Facture finalisée d'un montant TTC donné. */
function createReportInvoice(User $user, float $total, array $attributes = []): Document
{
    return createReportDocument($user, ['type' => 'invoice', 'total' => $total, 'subtotal' => $total, ...$attributes]);
}

/** Avoir finalisé (stocké positif, déduit du CA par le service). */
function createReportCreditNote(User $user, float $total, array $attributes = []): Document
{
    return createReportDocument($user, ['type' => 'credit_note', 'total' => $total, 'subtotal' => $total, ...$attributes]);
}

/** Produit simple pour lignes de documents. */
function createReportProduct(User $user, string $name = 'Produit Rapport', array $attributes = []): Product
{
    return Product::create([
        'company_id' => $user->current_company_id,
        'type' => 'product',
        'name' => $name,
        'unit' => 'unité',
        'price' => 1000,
        'tax_rate' => 0,
        'is_active' => true,
        ...$attributes,
    ]);
}

/** Paiement encaissé sur un document. */
function createReportPayment(Document $document, float $amount, string $method = 'cash', array $attributes = []): DocumentPayment
{
    return DocumentPayment::create([
        'company_id' => $document->company_id,
        'document_id' => $document->id,
        'amount' => $amount,
        'currency' => 'XOF',
        'method' => $method,
        'paid_at' => now()->toDateString(),
        ...$attributes,
    ]);
}

/** Utilisateur + société + licence ACTIVE sur le plan starter (pas d'export). */
function createReportStarterUser(): User
{
    seedPlans();

    $user = createUserWithCompany();
    $plan = Plan::where('code', 'starter')->firstOrFail();

    License::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'license_key' => app(LicenseService::class)->generateKey(),
        'type' => 'paid',
        'status' => 'active',
        'starts_at' => now(),
        'ends_at' => now()->addMonth(),
        'limits' => $plan->limits,
        'activation_source' => 'manual',
    ]);

    return $user->fresh();
}

/*
|--------------------------------------------------------------------------
| ReportService — calculs
|--------------------------------------------------------------------------
*/

it('computes kpis and monthly revenue with credit notes deducted', function () {
    $user = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;
    $customer = createCustomerFor($company);

    $from = Carbon::parse('2026-05-01');
    $to = Carbon::parse('2026-06-30');

    $inv1 = createReportInvoice($user, 100000, ['customer_id' => $customer->id, 'issue_date' => '2026-05-10']);
    createReportInvoice($user, 50000, ['customer_id' => $customer->id, 'issue_date' => '2026-06-05']);
    createReportCreditNote($user, 30000, ['customer_id' => $customer->id, 'issue_date' => '2026-06-20']);

    // Un brouillon NON finalisé et une facture annulée ne comptent jamais
    createReportInvoice($user, 999999, ['issue_date' => '2026-06-10', 'finalized_at' => null, 'status' => 'draft']);
    createReportInvoice($user, 888888, ['issue_date' => '2026-06-11', 'status' => 'cancelled']);

    createReportPayment($inv1, 40000, 'cash', ['paid_at' => '2026-05-15']);
    $inv1->update(['amount_paid' => 40000, 'status' => 'partial']);

    $service = app(ReportService::class);

    $kpis = $service->kpis($company, $from, $to);
    expect($kpis['revenue'])->toBe(120000.0)          // 100 000 + 50 000 − 30 000
        ->and($kpis['collected'])->toBe(40000.0)
        ->and($kpis['outstanding'])->toBe(110000.0)   // (100 000 − 40 000) + 50 000
        ->and($kpis['average_basket'])->toBe(60000.0) // 120 000 / 2 factures
        ->and($kpis['new_customers'])->toBe(0);       // client créé hors période (aujourd'hui)

    $revenue = $service->revenueByMonth($company, $from, $to);
    expect($revenue['total'])->toBe(120000.0)
        ->and(collect($revenue['months'])->firstWhere('key', '2026-05')['total'])->toBe(100000.0)
        ->and(collect($revenue['months'])->firstWhere('key', '2026-06')['total'])->toBe(20000.0);

    $byType = collect($service->salesByType($company, $from, $to));
    expect($byType->firstWhere('type', 'invoice')['documents_count'])->toBe(2)
        ->and($byType->firstWhere('type', 'credit_note')['total'])->toBe(30000.0);
});

it('ranks top customers by signed revenue with outstanding amounts', function () {
    $user = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;

    $small = createCustomerFor($company, ['name' => 'Petit Client']);
    $big = createCustomerFor($company, ['name' => 'Gros Client']);

    createReportInvoice($user, 200000, ['customer_id' => $big->id, 'amount_paid' => 150000]);
    createReportInvoice($user, 50000, ['customer_id' => $small->id]);
    createReportCreditNote($user, 10000, ['customer_id' => $small->id]);

    $top = app(ReportService::class)->topCustomers($company, now()->subMonth(), now()->addDay());

    expect($top)->toHaveCount(2)
        ->and($top[0]['name'])->toBe('Gros Client')
        ->and($top[0]['revenue'])->toBe(200000.0)
        ->and($top[0]['documents_count'])->toBe(1)
        ->and($top[0]['outstanding'])->toBe(50000.0)
        ->and($top[1]['name'])->toBe('Petit Client')
        ->and($top[1]['revenue'])->toBe(40000.0); // 50 000 − 10 000
});

it('aggregates top products from document lines with signed credit note quantities', function () {
    $user = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;

    $star = createReportProduct($user, 'Produit Star');
    $second = createReportProduct($user, 'Produit Second');

    $inv1 = createReportInvoice($user, 50000);
    $inv1->lines()->create(['product_id' => $star->id, 'description' => 'Star', 'quantity' => 2, 'line_total' => 20000]);
    $inv1->lines()->create(['product_id' => $second->id, 'description' => 'Second', 'quantity' => 1, 'line_total' => 5000]);

    $inv2 = createReportInvoice($user, 30000);
    $inv2->lines()->create(['product_id' => $star->id, 'description' => 'Star', 'quantity' => 3, 'line_total' => 30000]);

    $credit = createReportCreditNote($user, 10000);
    $credit->lines()->create(['product_id' => $star->id, 'description' => 'Retour', 'quantity' => 1, 'line_total' => 10000]);

    // Ligne sans produit : ignorée
    $inv1->lines()->create(['product_id' => null, 'description' => 'Prestation libre', 'quantity' => 1, 'line_total' => 99999]);

    $top = app(ReportService::class)->topProducts($company, now()->subMonth(), now()->addDay());

    expect($top)->toHaveCount(2)
        ->and($top[0]['name'])->toBe('Produit Star')
        ->and($top[0]['quantity'])->toBe(4.0)     // 2 + 3 − 1
        ->and($top[0]['revenue'])->toBe(40000.0)  // 20 000 + 30 000 − 10 000
        ->and($top[1]['name'])->toBe('Produit Second')
        ->and($top[1]['revenue'])->toBe(5000.0);
});

it('computes quote conversion rate (1 converted out of 2 = 50%)', function () {
    $user = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;

    $converted = createReportDocument($user, [
        'type' => 'quote', 'status' => 'converted',
        'issue_date' => now()->subDays(4)->toDateString(), 'total' => 80000,
    ]);
    createReportDocument($user, ['type' => 'quote', 'status' => 'sent', 'total' => 20000]);

    // Facture enfant issue de la conversion
    createReportDocument($user, ['type' => 'invoice', 'parent_id' => $converted->id, 'total' => 80000]);

    $conversion = app(ReportService::class)->quoteConversion($company, now()->subMonth(), now()->addDay());

    expect($conversion['total'])->toBe(2)
        ->and($conversion['converted'])->toBe(1)
        ->and($conversion['rate'])->toBe(50.0)
        ->and($conversion['avg_days'])->toBeGreaterThanOrEqual(4.0);
});

it('groups payments by method with french labels', function () {
    $user = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;

    $invoice = createReportInvoice($user, 100000);
    createReportPayment($invoice, 10000, 'cash');
    createReportPayment($invoice, 20000, 'cash');
    createReportPayment($invoice, 50000, 'mobile_money');

    $methods = app(ReportService::class)->paymentsByMethod($company, now()->subMonth(), now()->addDay());

    expect($methods)->toHaveCount(2)
        ->and($methods[0]['label'])->toBe('Mobile Money') // trié montant desc
        ->and($methods[0]['amount'])->toBe(50000.0)
        ->and($methods[1]['label'])->toBe('Espèces')
        ->and($methods[1]['payments_count'])->toBe(2)
        ->and($methods[1]['amount'])->toBe(30000.0);
});

it('never mixes data from another company', function () {
    $user = createUserWithCompanyAndTrial();
    $other = createUserWithCompanyAndTrial();

    $otherCustomer = createCustomerFor($other->currentCompany, ['name' => 'Client Étranger']);
    $otherInvoice = createReportInvoice($other, 500000, ['customer_id' => $otherCustomer->id]);
    createReportPayment($otherInvoice, 500000, 'card');

    $service = app(ReportService::class);
    $from = now()->subMonth();
    $to = now()->addDay();

    expect($service->kpis($user->currentCompany, $from, $to)['revenue'])->toBe(0.0)
        ->and($service->topCustomers($user->currentCompany, $from, $to))->toBeEmpty()
        ->and($service->paymentsByMethod($user->currentCompany, $from, $to))->toBeEmpty();

    // Export : le numéro du document de l'autre société n'apparaît jamais
    $response = $this->actingAs($user)->get(route('reports.export', ['dataset' => 'documents']));
    expect($response->streamedContent())->not->toContain($otherInvoice->number);
});

/*
|--------------------------------------------------------------------------
| HTTP — page & exports
|--------------------------------------------------------------------------
*/

it('renders the reports page with export enabled for a pro trial', function () {
    $user = createUserWithCompanyAndTrial();
    createReportInvoice($user, 15000);

    $this->actingAs($user)
        ->get(route('reports.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Reports/Index')
            ->where('canExport', true)
            ->where('kpis.revenue', 15000)
            ->has('revenueByMonth.months')
            ->has('topCustomers')
            ->has('salesByType'));
});

it('streams a documents csv export with BOM, semicolons and an invoice line', function () {
    $user = createUserWithCompanyAndTrial();
    $customer = createCustomerFor($user->currentCompany, ['name' => 'Client Export']);
    createReportInvoice($user, 159300, ['customer_id' => $customer->id, 'number' => 'FAC-2026-0001']);

    $response = $this->actingAs($user)
        ->get(route('reports.export', ['dataset' => 'documents']))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

    expect($response->headers->get('Content-Disposition'))
        ->toContain('factpro_documents_'.now()->format('Y-m-d').'.csv');

    $content = $response->streamedContent();
    expect(str_starts_with($content, "\xEF\xBB\xBF"))->toBeTrue()
        ->and($content)->toContain('Type;Numéro;Client;Date;Échéance;Statut;HT;TVA;TTC;Payé;Reste;Devise;Finalisé')
        ->and($content)->toContain('FAC-2026-0001')
        ->and($content)->toContain('Client Export')
        ->and($content)->toContain('159300,00')
        ->and($content)->toContain('Oui');
});

it('exports customers, products and payments datasets', function () {
    $user = createUserWithCompanyAndTrial();
    $customer = createCustomerFor($user->currentCompany, ['name' => 'Client CSV']);
    createReportProduct($user, 'Produit CSV', ['sku' => 'SKU-CSV-1']);
    $invoice = createReportInvoice($user, 25000, ['customer_id' => $customer->id]);
    createReportPayment($invoice, 25000, 'mobile_money', ['reference' => 'MM-12345']);

    $customers = $this->actingAs($user)->get(route('reports.export', ['dataset' => 'customers']))->assertOk();
    expect($customers->streamedContent())->toContain('Client CSV')->toContain('25000,00');

    $products = $this->actingAs($user)->get(route('reports.export', ['dataset' => 'products']))->assertOk();
    expect($products->streamedContent())->toContain('Produit CSV')->toContain('SKU-CSV-1');

    $payments = $this->actingAs($user)->get(route('reports.export', ['dataset' => 'payments']))->assertOk();
    expect($payments->streamedContent())->toContain('Mobile Money')->toContain('MM-12345');
});

it('rejects an unknown export dataset', function () {
    $user = createUserWithCompanyAndTrial();

    $this->actingAs($user)
        ->get('/reports/export/secrets')
        ->assertNotFound();
});

it('blocks csv exports for the starter plan with a 403', function () {
    $user = createReportStarterUser();

    // La page rapports reste accessible (canExport = false)…
    $this->actingAs($user)
        ->get(route('reports.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Reports/Index')->where('canExport', false));

    // … mais chaque export est refusé
    foreach (['documents', 'customers', 'products', 'payments'] as $dataset) {
        $this->actingAs($user)
            ->get(route('reports.export', ['dataset' => $dataset]))
            ->assertForbidden();
    }
});
