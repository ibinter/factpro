<?php

use App\Models\License;
use App\Models\OcrScan;
use App\Models\Plan;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use App\Models\User;
use App\Services\LicenseService;
use App\Services\OcrService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Helpers OCR (préfixés ocr_ pour éviter les collisions)
|--------------------------------------------------------------------------
*/

/** Crée une licence BUSINESS active pour un utilisateur. */
function ocrCreateBusinessLicense(User $user): License
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

/** Crée un fournisseur pour une société. */
function ocrCreateSupplier(User $user, array $attrs = []): Supplier
{
    return Supplier::create([
        'company_id' => $user->current_company_id,
        'name'       => 'SARL Dupont',
        ...$attrs,
    ]);
}

/*
|--------------------------------------------------------------------------
| Gate BUSINESS+
|--------------------------------------------------------------------------
*/

it('shows upsell for ocr index when on trial (PRO plan)', function () {
    $user = createUserWithCompanyAndTrial();

    $this->actingAs($user)
        ->get(route('purchases.ocr.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Purchases/OcrUpload')
            ->where('hasAccess', false));
});

it('forbids ocr upload for trial (PRO plan) user', function () {
    Storage::fake('private');
    $user = createUserWithCompanyAndTrial();

    $this->actingAs($user)
        ->post(route('purchases.ocr.upload'), [
            'file' => UploadedFile::fake()->create('facture.pdf', 100, 'application/pdf'),
        ])
        ->assertForbidden();
});

/*
|--------------------------------------------------------------------------
| Upload
|--------------------------------------------------------------------------
*/

it('uploads a pdf file for ocr scanning', function () {
    Storage::fake('private');
    Queue::fake();

    $user = createUserWithCompanyAndTrial();
    ocrCreateBusinessLicense($user);

    $file = UploadedFile::fake()->create('facture.pdf', 200, 'application/pdf');

    $response = $this->actingAs($user)
        ->post(route('purchases.ocr.upload'), ['file' => $file]);

    $response->assertStatus(201)
        ->assertJsonStructure(['id', 'status']);

    $this->assertDatabaseHas('ocr_scans', [
        'company_id'        => $user->current_company_id,
        'user_id'           => $user->id,
        'original_filename' => 'facture.pdf',
        'status'            => 'pending',
    ]);
});

it('rejects files larger than 10mb', function () {
    Storage::fake('private');
    $user = createUserWithCompanyAndTrial();
    ocrCreateBusinessLicense($user);

    // 11 Mo
    $file = UploadedFile::fake()->create('big.pdf', 11 * 1024, 'application/pdf');

    $this->actingAs($user)
        ->post(route('purchases.ocr.upload'), ['file' => $file])
        ->assertSessionHasErrors('file');
});

it('rejects invalid file types', function () {
    Storage::fake('private');
    $user = createUserWithCompanyAndTrial();
    ocrCreateBusinessLicense($user);

    $file = UploadedFile::fake()->create('doc.docx', 50, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

    $this->actingAs($user)
        ->post(route('purchases.ocr.upload'), ['file' => $file])
        ->assertSessionHasErrors('file');
});

/*
|--------------------------------------------------------------------------
| Traitement OCR (avec mock)
|--------------------------------------------------------------------------
*/

it('processes ocr scan and returns extracted data', function () {
    Storage::fake('private');
    Queue::fake();

    $user = createUserWithCompanyAndTrial();
    ocrCreateBusinessLicense($user);

    $this->mock(OcrService::class, function ($mock) {
        $mock->shouldReceive('extractText')->andReturn(
            "Fournisseur: SARL Dupont\nN° Facture: F-2026-001\nTotal TTC: 150 000 FCFA\nTVA: 25 000 FCFA"
        );
        $mock->shouldReceive('parseInvoiceData')->andReturn([
            'supplier_name'  => 'SARL Dupont',
            'invoice_number' => 'F-2026-001',
            'invoice_date'   => '2026-07-18',
            'total_amount'   => 150000,
            'tax_amount'     => 25000,
            'line_items'     => [],
        ]);
    });

    // Créer le scan directement
    $scan = OcrScan::create([
        'company_id'        => $user->current_company_id,
        'user_id'           => $user->id,
        'original_filename' => 'facture.pdf',
        'storage_path'      => 'ocr-scans/fake.pdf',
        'status'            => 'pending',
    ]);

    Storage::disk('private')->put('ocr-scans/fake.pdf', '%PDF-1.4 test content');

    $response = $this->actingAs($user)
        ->post(route('purchases.ocr.process', $scan->id));

    $response->assertOk()
        ->assertJson(['status' => 'done']);

    $this->assertDatabaseHas('ocr_scans', [
        'id'     => $scan->id,
        'status' => 'done',
    ]);
});

/*
|--------------------------------------------------------------------------
| Tests unitaires OcrService (sans HTTP)
|--------------------------------------------------------------------------
*/

it('parses invoice number from text', function () {
    $service = new OcrService();

    $text = "SARL Dupont\nN° Facture: F-2026-001\nDate: 18/07/2026\nTotal TTC: 150000";
    $data = $service->parseInvoiceData($text);

    expect($data['invoice_number'])->toBe('F-2026-001');
});

it('parses total amount from text', function () {
    $service = new OcrService();

    $text = "Fournisseur: Test\nTotal TTC: 150000\nTVA: 25000";
    $data = $service->parseInvoiceData($text);

    expect($data['total_amount'])->toBe(150000.0);
});

it('parses tax amount from text', function () {
    $service = new OcrService();

    $text = "Fournisseur: Test\nTotal TTC: 150000\nTVA: 25000";
    $data = $service->parseInvoiceData($text);

    expect($data['tax_amount'])->toBe(25000.0);
});

/*
|--------------------------------------------------------------------------
| Conversion en achat
|--------------------------------------------------------------------------
*/

it('converts ocr scan to purchase', function () {
    Storage::fake('private');
    Queue::fake();

    $user = createUserWithCompanyAndTrial();
    ocrCreateBusinessLicense($user);
    $supplier = ocrCreateSupplier($user);

    $scan = OcrScan::create([
        'company_id'        => $user->current_company_id,
        'user_id'           => $user->id,
        'original_filename' => 'facture.pdf',
        'storage_path'      => 'ocr-scans/fake.pdf',
        'status'            => 'done',
        'extracted_data'    => [
            'supplier_name'  => 'SARL Dupont',
            'invoice_number' => 'F-2026-001',
            'invoice_date'   => '2026-07-18',
            'total_amount'   => 150000,
            'tax_amount'     => 25000,
            'line_items'     => [],
        ],
    ]);

    $response = $this->actingAs($user)
        ->post(route('purchases.ocr.convert', $scan->id), [
            'supplier_id'  => $supplier->id,
            'number'       => 'F-2026-001',
            'invoice_date' => '2026-07-18',
            'amount_ht'    => 125000,
            'vat_amount'   => 25000,
            'amount_ttc'   => 150000,
            'category'     => 'marchandises',
        ]);

    $response->assertRedirect(route('purchases.index'));

    $this->assertDatabaseHas('supplier_invoices', [
        'company_id' => $user->current_company_id,
        'number'     => 'F-2026-001',
        'amount_ttc' => 150000,
    ]);

    // Le scan doit avoir purchase_id renseigné
    $invoice = SupplierInvoice::where('number', 'F-2026-001')->first();
    $this->assertNotNull($invoice);
    $this->assertDatabaseHas('ocr_scans', [
        'id'          => $scan->id,
        'purchase_id' => $invoice->id,
    ]);
});

/*
|--------------------------------------------------------------------------
| Isolation multi-société
|--------------------------------------------------------------------------
*/

it('isolates scans between companies', function () {
    Storage::fake('private');

    $user1 = createUserWithCompanyAndTrial();
    $user2 = createUserWithCompanyAndTrial();
    ocrCreateBusinessLicense($user1);
    ocrCreateBusinessLicense($user2);

    // Scan appartenant à user2
    $scan = OcrScan::create([
        'company_id'        => $user2->current_company_id,
        'user_id'           => $user2->id,
        'original_filename' => 'autre.pdf',
        'storage_path'      => 'ocr-scans/autre.pdf',
        'status'            => 'pending',
    ]);

    // user1 ne doit pas pouvoir traiter le scan de user2
    $this->actingAs($user1)
        ->post(route('purchases.ocr.process', $scan->id))
        ->assertNotFound();
});
