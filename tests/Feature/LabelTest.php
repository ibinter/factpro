<?php

use App\Models\Company;
use App\Models\License;
use App\Models\Plan;
use App\Models\Product;
use App\Models\User;
use App\Services\LicenseService;

/** Crée une licence active sur un plan donné (helper local au module étiquettes). */
function createLabelLicenseFor(User $user, string $planCode): License
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

/** Crée un produit actif rattaché à une société. */
function createLabelProductFor(Company $company, array $attributes = []): Product
{
    return Product::create([
        'company_id' => $company->id,
        'type' => 'product',
        'name' => 'Produit Étiquette',
        'sku' => 'ETQ-001',
        'barcode' => '6111242100992',
        'unit' => 'unité',
        'price' => 45000,
        'is_active' => true,
        'track_stock' => false,
        'stock_quantity' => 0,
        ...$attributes,
    ]);
}

it('shows the labels page without access for a PRO (trial) user', function () {
    $user = createUserWithCompanyAndTrial(); // essai = plan PRO

    $this->actingAs($user)
        ->get(route('labels.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Labels/Index')
            ->where('hasAccess', false));
});

it('forbids PDF generation for a PRO (trial) user', function () {
    $user = createUserWithCompanyAndTrial();
    $product = createLabelProductFor($user->currentCompany);

    $this->actingAs($user)
        ->post(route('labels.pdf'), [
            'format' => 'avery-l7160',
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ])
        ->assertForbidden();
});

it('shows the labels page with access and products for a BUSINESS user', function () {
    $user = createUserWithCompany();
    createLabelLicenseFor($user, 'business');
    createLabelProductFor($user->currentCompany);

    $this->actingAs($user)
        ->get(route('labels.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Labels/Index')
            ->where('hasAccess', true)
            ->has('products', 1)
            ->has('formats', 3));
});

it('generates a labels PDF for a BUSINESS user', function () {
    $user = createUserWithCompany();
    createLabelLicenseFor($user, 'business');
    $product = createLabelProductFor($user->currentCompany);

    $response = $this->actingAs($user)->post(route('labels.pdf'), [
        'format' => 'avery-l7160',
        'items' => [['product_id' => $product->id, 'quantity' => 3]],
        'show_qr' => false,
    ]);

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});

it('rejects products belonging to another company', function () {
    $user = createUserWithCompany();
    createLabelLicenseFor($user, 'business');

    $other = createUserWithCompany();
    $foreignProduct = createLabelProductFor($other->currentCompany, ['sku' => 'ETQ-FOREIGN']);

    $this->actingAs($user)
        ->post(route('labels.pdf'), [
            'format' => 'avery-l7160',
            'items' => [['product_id' => $foreignProduct->id, 'quantity' => 1]],
        ])
        ->assertSessionHasErrors('items.0.product_id');
});
