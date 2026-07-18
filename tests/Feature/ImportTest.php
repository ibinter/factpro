<?php

use App\Models\Customer;
use App\Models\Product;
use App\Services\ImportService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

// ─── Helper : crée un utilisateur BUSINESS+ ──────────────────────────────────
function createBusinessUser(): \App\Models\User
{
    seedPlans();
    $user = createUserWithCompany();
    $plan = \App\Models\Plan::where('code', 'business')->first();

    $licenseService = app(\App\Services\LicenseService::class);

    \App\Models\License::create([
        'user_id'           => $user->id,
        'plan_id'           => $plan->id,
        'license_key'       => $licenseService->generateKey(),
        'type'              => 'paid',
        'status'            => 'active',
        'starts_at'         => now(),
        'ends_at'           => now()->addYear(),
        'limits'            => $plan->limits,
        'activation_source' => 'payment',
    ]);

    return $user->fresh();
}

// ─── Helper : crée un CSV temporaire ─────────────────────────────────────────
function tmpCsv(string $content, string $filename = 'test.csv'): string
{
    $dir  = storage_path('testing');
    if (! is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $path = $dir . '/' . $filename;
    file_put_contents($path, $content);
    return $path;
}

// ─────────────────────────────────────────────────────────────────────────────
// ImportService unit tests
// ─────────────────────────────────────────────────────────────────────────────

it('parses a valid csv file', function () {
    $csv  = "Nom,Email,Téléphone\nDupont SARL,dupont@test.com,+225 07 00 00 00\n";
    $path = tmpCsv($csv, 'parse_test.csv');

    $service = app(ImportService::class);
    $result  = $service->parseCsv($path);

    expect($result['headers'])->toContain('Nom')
        ->and($result['rows'])->toHaveCount(1)
        ->and($result['rows'][0][0])->toBe('Dupont SARL');
});

it('imports customers from csv', function () {
    $user    = createBusinessUser();
    $company = $user->currentCompany;

    $rows      = [['Dupont SARL', 'dupont@test.com', '+225 07 00 00 00']];
    $columnMap = ['name' => 0, 'email' => 1, 'phone' => 2];

    $service = app(ImportService::class);
    $result  = $service->importCustomers($company, $rows, $columnMap);

    expect($result['imported'])->toBe(1)
        ->and($result['skipped'])->toBe(0)
        ->and(Customer::where('company_id', $company->id)->count())->toBe(1);
});

it('skips duplicate customers by email', function () {
    $user    = createBusinessUser();
    $company = $user->currentCompany;

    createCustomerFor($company, ['email' => 'dupont@test.com']);

    $rows      = [['Dupont SARL', 'dupont@test.com', '+225 07 00 00 00']];
    $columnMap = ['name' => 0, 'email' => 1, 'phone' => 2];

    $service = app(ImportService::class);
    $result  = $service->importCustomers($company, $rows, $columnMap);

    expect($result['imported'])->toBe(0)
        ->and($result['skipped'])->toBe(1)
        ->and($result['errors'])->toHaveCount(1);
});

it('imports products and upserts by reference', function () {
    $user    = createBusinessUser();
    $company = $user->currentCompany;

    // Première passe
    $rows      = [['Stylo BIC', 'STY-001', 'Stylo bleu', '500', 'unité', '18', '100']];
    $columnMap = [
        'name'          => 0,
        'sku'           => 1,
        'description'   => 2,
        'price'         => 3,
        'unit'          => 4,
        'tax_rate'      => 5,
        'stock_quantity'=> 6,
    ];

    $service = app(ImportService::class);
    $result  = $service->importProducts($company, $rows, $columnMap);
    expect($result['imported'])->toBe(1);

    // Deuxième passe : même SKU → upsert
    $rows2 = [['Stylo BIC v2', 'STY-001', 'Stylo rouge', '600', 'unité', '18', '50']];
    $result2 = $service->importProducts($company, $rows2, $columnMap);

    expect($result2['imported'])->toBe(1)
        ->and(Product::where('company_id', $company->id)->count())->toBe(1)
        ->and(Product::where('company_id', $company->id)->first()->name)->toBe('Stylo BIC v2');
});

it('handles missing optional columns gracefully', function () {
    $user    = createBusinessUser();
    $company = $user->currentCompany;

    // columnMap ne contient que 'name', tous les autres champs sont absents
    $rows      = [['Client sans email']];
    $columnMap = ['name' => 0];

    $service = app(ImportService::class);
    $result  = $service->importCustomers($company, $rows, $columnMap);

    expect($result['imported'])->toBe(1)
        ->and($result['skipped'])->toBe(0);
});

it('reports errors for invalid rows', function () {
    $user    = createBusinessUser();
    $company = $user->currentCompany;

    // Ligne sans nom (champ obligatoire)
    $rows      = [['', 'email@test.com', '']];
    $columnMap = ['name' => 0, 'email' => 1];

    $service = app(ImportService::class);
    $result  = $service->importCustomers($company, $rows, $columnMap);

    expect($result['imported'])->toBe(0)
        ->and($result['skipped'])->toBe(1)
        ->and($result['errors'])->toHaveCount(1);
});

// ─────────────────────────────────────────────────────────────────────────────
// HTTP controller tests
// ─────────────────────────────────────────────────────────────────────────────

it('downloads customer csv template', function () {
    $user = createBusinessUser();

    $response = $this->actingAs($user)->get(route('import.templates.customers'));

    $response->assertStatus(200)
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

    expect($response->getContent())->toContain('Nom');
});

it('downloads product csv template', function () {
    $user = createBusinessUser();

    $response = $this->actingAs($user)->get(route('import.templates.products'));

    $response->assertStatus(200)
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

    expect($response->getContent())->toContain('Référence');
});

it('rejects csv larger than 2mb', function () {
    $user = createBusinessUser();

    // Fichier > 2MB
    $bigFile = UploadedFile::fake()->create('gros.csv', 3000, 'text/csv');

    $response = $this->actingAs($user)
        ->withHeaders(['Accept' => 'application/json'])
        ->post(route('import.customers.upload'), [
            'file' => $bigFile,
        ]);

    $response->assertStatus(422);
});

it('requires business plan', function () {
    seedPlans();
    $user = createUserWithCompanyAndTrial(); // plan PRO (essai)

    $csv     = "Nom,Email\nTest,test@test.com\n";
    $path    = tmpCsv($csv, 'biz_test.csv');
    $csvFile = new UploadedFile($path, 'test.csv', 'text/csv', null, true);

    $response = $this->actingAs($user)->post(route('import.customers.upload'), [
        'file' => $csvFile,
    ]);

    $response->assertStatus(403);
});
