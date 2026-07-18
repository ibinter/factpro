<?php

use App\Models\Customer;
use App\Models\Document;
use App\Models\License;
use App\Models\Plan;
use App\Models\Product;
use App\Models\User;
use App\Services\LicenseService;
use PhpOffice\PhpSpreadsheet\IOFactory;

// -------------------------------------------------------------------------
// Helper : crée une licence BUSINESS active pour un utilisateur (Excel)
// -------------------------------------------------------------------------
function createExcelBusinessLicense(User $user): License
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

// -------------------------------------------------------------------------
// Setup
// -------------------------------------------------------------------------
beforeEach(function () {
    $this->user    = createUserWithCompany();
    $this->company = $this->user->currentCompany;
    createExcelBusinessLicense($this->user);
});

// -------------------------------------------------------------------------
// Tests
// -------------------------------------------------------------------------

it('requires auth to download excel files', function () {
    $this->get(route('export.excel.customers'))->assertRedirect(route('login'));
    $this->get(route('export.excel.products'))->assertRedirect(route('login'));
    $this->get(route('export.excel.documents'))->assertRedirect(route('login'));
    $this->get(route('export.excel.revenue'))->assertRedirect(route('login'));
    $this->get(route('export.excel.fec'))->assertRedirect(route('login'));
});

it('exports customers as xlsx file', function () {
    Customer::create([
        'company_id' => $this->company->id,
        'name'       => 'Client Excel Test',
        'country'    => 'CI',
        'currency'   => 'XOF',
    ]);

    $response = $this->actingAs($this->user)->get(route('export.excel.customers'));

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});

it('exports products as xlsx file', function () {
    Product::create([
        'company_id' => $this->company->id,
        'name'       => 'Produit Excel Test',
        'price'      => 1000,
        'tax_rate'   => 18,
        'unit'       => 'unité',
        'is_active'  => true,
    ]);

    $response = $this->actingAs($this->user)->get(route('export.excel.products'));

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});

it('exports documents as xlsx file', function () {
    $customer = createCustomerFor($this->company, ['name' => 'Client Docs']);

    Document::create([
        'company_id'   => $this->company->id,
        'customer_id'  => $customer->id,
        'type'         => 'invoice',
        'number'       => 'FAC-TEST-001',
        'status'       => 'sent',
        'issue_date'   => now()->toDateString(),
        'due_date'     => now()->addDays(30)->toDateString(),
        'currency'     => 'XOF',
        'subtotal'     => 100000,
        'discount_amount' => 0,
        'tax_amount'   => 18000,
        'total'        => 118000,
        'amount_paid'  => 0,
        'finalized_at' => now(),
    ]);

    $response = $this->actingAs($this->user)->get(route('export.excel.documents'));

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});

it('exports monthly revenue as xlsx file', function () {
    $response = $this->actingAs($this->user)->get(route('export.excel.revenue', ['year' => now()->year]));

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});

it('exports fec as xlsx file', function () {
    $response = $this->actingAs($this->user)->get(route('export.excel.fec', ['year' => now()->year]));

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});

it('excel file is not empty', function () {
    $response = $this->actingAs($this->user)->get(route('export.excel.customers'));
    $response->assertStatus(200);

    // Capture the streamed content
    ob_start();
    $response->sendContent();
    $content = ob_get_clean();

    expect(strlen($content))->toBeGreaterThan(0);
});

it('excel file contains company name in header', function () {
    $response = $this->actingAs($this->user)->get(route('export.excel.customers'));
    $response->assertStatus(200);

    // Write to a temp file and read back with PhpSpreadsheet
    $tmpFile = tempnam(sys_get_temp_dir(), 'excel_test_') . '.xlsx';

    ob_start();
    $response->sendContent();
    $content = ob_get_clean();

    file_put_contents($tmpFile, $content);

    $spreadsheet = IOFactory::load($tmpFile);
    $sheet       = $spreadsheet->getActiveSheet();
    $cellA1      = (string) $sheet->getCell('A1')->getValue();

    unlink($tmpFile);

    expect($cellA1)->toContain($this->company->name);
});
