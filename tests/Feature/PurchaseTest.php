<?php

use App\Models\Company;
use App\Models\License;
use App\Models\Plan;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use App\Models\User;
use App\Services\LicenseService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Helpers locaux au module achats (préfixés pour éviter les collisions).
|--------------------------------------------------------------------------
*/

/** Crée une licence active sur un plan donné. */
function createPurchaseLicenseFor(User $user, string $planCode = 'business'): License
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

/** Crée un fournisseur rattaché à une société. */
function createSupplierFor(Company $company, array $attributes = []): Supplier
{
    return Supplier::create([
        'company_id' => $company->id,
        'name' => 'Fournisseur Test',
        'country' => 'CI',
        ...$attributes,
    ]);
}

/** Crée une facture d'achat pour un fournisseur. */
function createPurchaseInvoiceFor(Supplier $supplier, array $attributes = []): SupplierInvoice
{
    return SupplierInvoice::create([
        'company_id' => $supplier->company_id,
        'supplier_id' => $supplier->id,
        'number' => 'ACH-'.strtoupper(Str::random(6)),
        'invoice_date' => now()->toDateString(),
        'amount_ht' => 100000,
        'vat_amount' => 18000,
        'amount_ttc' => 118000,
        'currency' => 'XOF',
        'category' => 'marchandises',
        'status' => 'unpaid',
        'amount_paid' => 0,
        ...$attributes,
    ]);
}

/*
|--------------------------------------------------------------------------
| Gate BUSINESS/ENTERPRISE
|--------------------------------------------------------------------------
*/

it('shows the purchases page without access for a PRO (trial) user', function () {
    $user = createUserWithCompanyAndTrial(); // essai = plan PRO

    $this->actingAs($user)
        ->get(route('purchases.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Purchases/Index')
            ->where('hasAccess', false));
});

it('forbids purchase mutations for a PRO (trial) user', function () {
    $user = createUserWithCompanyAndTrial();

    $this->actingAs($user)
        ->post(route('purchases.suppliers.store'), ['name' => 'ACME'])
        ->assertForbidden();

    $this->actingAs($user)
        ->post(route('purchases.invoices.store'), [
            'supplier_id' => 1,
            'number' => 'X-1',
            'invoice_date' => now()->toDateString(),
            'amount_ht' => 1000,
            'vat_amount' => 180,
            'amount_ttc' => 1180,
            'category' => 'marchandises',
        ])
        ->assertForbidden();
});

/*
|--------------------------------------------------------------------------
| CRUD fournisseurs
|--------------------------------------------------------------------------
*/

it('creates, updates and deletes a supplier', function () {
    $user = createUserWithCompany();
    createPurchaseLicenseFor($user);

    $this->actingAs($user)
        ->post(route('purchases.suppliers.store'), [
            'name' => 'Grossiste Abidjan',
            'email' => 'contact@grossiste.ci',
            'city' => 'Abidjan',
            'country' => 'CI',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $supplier = Supplier::firstOrFail();
    expect($supplier->name)->toBe('Grossiste Abidjan')
        ->and($supplier->company_id)->toBe($user->current_company_id);

    $this->actingAs($user)
        ->put(route('purchases.suppliers.update', $supplier), [
            'name' => 'Grossiste Abidjan SARL',
            'country' => 'CI',
        ])
        ->assertRedirect();

    expect($supplier->fresh()->name)->toBe('Grossiste Abidjan SARL');

    $this->actingAs($user)
        ->delete(route('purchases.suppliers.destroy', $supplier))
        ->assertRedirect();

    expect($supplier->fresh()->trashed())->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| Saisie facture & justificatif privé
|--------------------------------------------------------------------------
*/

it('stores a purchase invoice with a private receipt', function () {
    Storage::fake(config('factpro.proofs.disk'));

    $user = createUserWithCompany();
    createPurchaseLicenseFor($user);
    $supplier = createSupplierFor($user->currentCompany);

    $this->actingAs($user)
        ->post(route('purchases.invoices.store'), [
            'supplier_id' => $supplier->id,
            'number' => 'FA-2026-001',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'amount_ht' => 100000,
            'vat_amount' => 18000,
            'amount_ttc' => 118000,
            'category' => 'marchandises',
            'receipt' => UploadedFile::fake()->create('facture.pdf', 200, 'application/pdf'),
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $invoice = SupplierInvoice::firstOrFail();
    expect($invoice->number)->toBe('FA-2026-001')
        ->and($invoice->status)->toBe('unpaid')
        ->and((float) $invoice->amount_ttc)->toBe(118000.0)
        ->and($invoice->created_by)->toBe($user->id)
        ->and($invoice->receipt_path)->toStartWith('private/purchase-receipts/');

    Storage::disk(config('factpro.proofs.disk'))->assertExists($invoice->receipt_path);
});

it('streams the receipt to its company and blocks another company', function () {
    Storage::fake(config('factpro.proofs.disk'));

    $user = createUserWithCompany();
    createPurchaseLicenseFor($user);
    $supplier = createSupplierFor($user->currentCompany);

    $path = 'private/purchase-receipts/'.Str::random(40).'.pdf';
    Storage::disk(config('factpro.proofs.disk'))->put($path, 'fake-pdf');

    $invoice = createPurchaseInvoiceFor($supplier, [
        'receipt_path' => $path,
        'receipt_original_name' => 'facture.pdf',
        'receipt_mime' => 'application/pdf',
    ]);

    $this->actingAs($user)
        ->get(route('purchases.invoices.receipt', $invoice))
        ->assertOk();

    $stranger = createUserWithCompany();
    createPurchaseLicenseFor($stranger);

    $this->actingAs($stranger)
        ->get(route('purchases.invoices.receipt', $invoice))
        ->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Cohérence des montants & unicité du numéro
|--------------------------------------------------------------------------
*/

it('rejects an invoice whose HT + VAT does not match the TTC', function () {
    $user = createUserWithCompany();
    createPurchaseLicenseFor($user);
    $supplier = createSupplierFor($user->currentCompany);

    $this->actingAs($user)
        ->post(route('purchases.invoices.store'), [
            'supplier_id' => $supplier->id,
            'number' => 'FA-INCO',
            'invoice_date' => now()->toDateString(),
            'amount_ht' => 100000,
            'vat_amount' => 18000,
            'amount_ttc' => 999999, // incohérent
            'category' => 'marchandises',
        ])
        ->assertSessionHasErrors('amount_ttc');

    expect(SupplierInvoice::count())->toBe(0);
});

it('enforces a unique invoice number per supplier', function () {
    $user = createUserWithCompany();
    createPurchaseLicenseFor($user);
    $supplier = createSupplierFor($user->currentCompany);

    createPurchaseInvoiceFor($supplier, ['number' => 'FA-DUP']);

    $this->actingAs($user)
        ->post(route('purchases.invoices.store'), [
            'supplier_id' => $supplier->id,
            'number' => 'FA-DUP',
            'invoice_date' => now()->toDateString(),
            'amount_ht' => 5000,
            'vat_amount' => 900,
            'amount_ttc' => 5900,
            'category' => 'services',
        ])
        ->assertSessionHasErrors('number');

    expect(SupplierInvoice::where('number', 'FA-DUP')->count())->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Règlement (partiel puis solde)
|--------------------------------------------------------------------------
*/

it('records a partial then a final payment', function () {
    $user = createUserWithCompany();
    createPurchaseLicenseFor($user);
    $supplier = createSupplierFor($user->currentCompany);
    $invoice = createPurchaseInvoiceFor($supplier); // 118000 TTC

    // Règlement partiel
    $this->actingAs($user)
        ->post(route('purchases.invoices.payment', $invoice), ['amount' => 50000])
        ->assertRedirect()
        ->assertSessionHas('success');

    $invoice->refresh();
    expect($invoice->status)->toBe('partial')
        ->and((float) $invoice->amount_paid)->toBe(50000.0)
        ->and($invoice->paid_at)->toBeNull();

    // Solde
    $this->actingAs($user)
        ->post(route('purchases.invoices.payment', $invoice), ['amount' => 68000])
        ->assertRedirect();

    $invoice->refresh();
    expect($invoice->status)->toBe('paid')
        ->and((float) $invoice->amount_paid)->toBe(118000.0)
        ->and($invoice->paid_at)->not->toBeNull();
});

it('refuses a payment above the remaining balance', function () {
    $user = createUserWithCompany();
    createPurchaseLicenseFor($user);
    $supplier = createSupplierFor($user->currentCompany);
    $invoice = createPurchaseInvoiceFor($supplier);

    $this->actingAs($user)
        ->post(route('purchases.invoices.payment', $invoice), ['amount' => 200000])
        ->assertSessionHasErrors('amount');

    expect((float) $invoice->fresh()->amount_paid)->toBe(0.0);
});

/*
|--------------------------------------------------------------------------
| Intégration comptabilité
|--------------------------------------------------------------------------
*/

it('exposes the purchases journal with totals in accounting', function () {
    $user = createUserWithCompany();
    createPurchaseLicenseFor($user);
    $supplier = createSupplierFor($user->currentCompany, ['name' => 'Fournisseur Compta']);

    createPurchaseInvoiceFor($supplier, [
        'number' => 'ACH-A',
        'invoice_date' => now()->startOfYear()->addMonths(2)->toDateString(),
    ]);
    createPurchaseInvoiceFor($supplier, [
        'number' => 'ACH-B',
        'invoice_date' => now()->startOfYear()->addMonths(3)->toDateString(),
        'amount_ht' => 50000, 'vat_amount' => 9000, 'amount_ttc' => 59000,
    ]);

    $this->actingAs($user)
        ->get(route('accounting.index', [
            'tab' => 'purchases',
            'from' => now()->startOfYear()->toDateString(),
            'to' => now()->endOfYear()->toDateString(),
        ]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('tab', 'purchases')
            ->has('data.lines', 2)
            ->where('data.lines.0.piece', 'ACH-A')
            ->where('data.lines.0.fournisseur', 'Fournisseur Compta')
            ->where('data.totals.ht', 150000)
            ->where('data.totals.tva', 27000)
            ->where('data.totals.ttc', 177000));
});

it('adds deductible VAT and net VAT to the VAT summary', function () {
    $user = createUserWithCompany();
    createPurchaseLicenseFor($user);
    $supplier = createSupplierFor($user->currentCompany);

    $month = now()->startOfYear()->addMonth();
    createPurchaseInvoiceFor($supplier, [
        'number' => 'ACH-TVA',
        'invoice_date' => $month->toDateString(),
        'amount_ht' => 100000, 'vat_amount' => 18000, 'amount_ttc' => 118000,
    ]);

    $this->actingAs($user)
        ->get(route('accounting.index', [
            'tab' => 'vat',
            'from' => now()->startOfYear()->toDateString(),
            'to' => now()->endOfYear()->toDateString(),
        ]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('tab', 'vat')
            ->where('data.rows.0.vat_deductible', 18000)
            ->where('data.rows.0.vat_net', -18000)
            ->where('data.totals.vat_deductible', 18000)
            ->where('data.totals.vat_net', -18000));
});

it('includes purchase HT in the profit and loss charges', function () {
    $user = createUserWithCompany();
    createPurchaseLicenseFor($user);
    $supplier = createSupplierFor($user->currentCompany);

    createPurchaseInvoiceFor($supplier, [
        'number' => 'ACH-PNL',
        'amount_ht' => 40000, 'vat_amount' => 7200, 'amount_ttc' => 47200,
    ]);

    $this->actingAs($user)
        ->get(route('accounting.index', ['tab' => 'pnl']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('data.revenue', 0)
            ->where('data.expenses', 40000)
            ->where('data.result', -40000));
});

/*
|--------------------------------------------------------------------------
| Isolation multi-sociétés
|--------------------------------------------------------------------------
*/

it('isolates suppliers and invoices between companies', function () {
    $user = createUserWithCompany();
    createPurchaseLicenseFor($user);
    $supplier = createSupplierFor($user->currentCompany);
    $invoice = createPurchaseInvoiceFor($supplier);

    // Un utilisateur BUSINESS d'une AUTRE société n'y accède pas.
    $stranger = createUserWithCompany();
    createPurchaseLicenseFor($stranger);

    $this->actingAs($stranger)
        ->put(route('purchases.suppliers.update', $supplier), ['name' => 'Piraté', 'country' => 'CI'])
        ->assertNotFound();

    $this->actingAs($stranger)
        ->post(route('purchases.invoices.payment', $invoice), ['amount' => 1000])
        ->assertNotFound();

    // Sa liste d'achats est vide.
    $this->actingAs($stranger)
        ->get(route('purchases.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('hasAccess', true)
            ->has('suppliers', 0)
            ->has('invoices.data', 0));
});
