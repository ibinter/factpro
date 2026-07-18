<?php

use App\Models\Product;

/** Crée un produit avec code-barres pour un utilisateur. */
function createBarcodeProduct($user, array $attributes = []): Product
{
    return Product::create([
        'company_id'     => $user->current_company_id,
        'type'           => 'product',
        'name'           => 'Produit Barcode',
        'price'          => 2500,
        'tax_rate'       => 18,
        'stock_quantity' => 100,
        'unit'           => 'unité',
        'is_active'      => true,
        ...$attributes,
    ]);
}

it('finds product by barcode', function () {
    $user = createUserWithCompanyAndTrial();
    createBarcodeProduct($user, ['barcode' => '3760168090128', 'name' => 'Coca-Cola 33cl']);

    $this->actingAs($user)
        ->getJson('/barcode/lookup?code=3760168090128')
        ->assertOk()
        ->assertJsonPath('found', true)
        ->assertJsonPath('product.name', 'Coca-Cola 33cl')
        ->assertJsonPath('product.barcode', '3760168090128');
});

it('finds product by sku', function () {
    $user = createUserWithCompanyAndTrial();
    createBarcodeProduct($user, ['sku' => 'CC-33CL', 'name' => 'Coca-Cola SKU']);

    $this->actingAs($user)
        ->getJson('/barcode/lookup?code=CC-33CL')
        ->assertOk()
        ->assertJsonPath('found', true)
        ->assertJsonPath('product.name', 'Coca-Cola SKU');
});

it('returns 404 for unknown barcode', function () {
    $user = createUserWithCompanyAndTrial();

    $this->actingAs($user)
        ->getJson('/barcode/lookup?code=9999999999999')
        ->assertStatus(404)
        ->assertJsonPath('found', false);
});

it('isolates lookup between companies', function () {
    $user1 = createUserWithCompanyAndTrial();
    $user2 = createUserWithCompanyAndTrial();

    createBarcodeProduct($user1, ['barcode' => 'BARCODE-COMPANY1']);

    // user2 ne doit pas voir le produit de user1
    $this->actingAs($user2)
        ->getJson('/barcode/lookup?code=BARCODE-COMPANY1')
        ->assertStatus(404)
        ->assertJsonPath('found', false);
});

it('requires authentication', function () {
    $this->getJson('/barcode/lookup?code=1234567890')
        ->assertUnauthorized();
});

it('assigns barcode to product', function () {
    $user = createUserWithCompanyAndTrial();
    $product = createBarcodeProduct($user, ['name' => 'Produit Sans Code']);

    $this->actingAs($user)
        ->postJson('/barcode/assign', [
            'product_id' => $product->id,
            'barcode'    => 'NEW-BARCODE-001',
        ])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('product.barcode', 'NEW-BARCODE-001');

    expect($product->fresh()->barcode)->toBe('NEW-BARCODE-001');
});

it('prevents duplicate barcode within company', function () {
    $user = createUserWithCompanyAndTrial();
    createBarcodeProduct($user, ['barcode' => 'DUPE-123', 'name' => 'Produit A']);
    $product2 = createBarcodeProduct($user, ['barcode' => null, 'name' => 'Produit B']);

    $this->actingAs($user)
        ->postJson('/barcode/assign', [
            'product_id' => $product2->id,
            'barcode'    => 'DUPE-123',
        ])
        ->assertStatus(422)
        ->assertJsonPath('error', 'duplicate');
});

it('lookup returns price and stock', function () {
    $user = createUserWithCompanyAndTrial();
    createBarcodeProduct($user, [
        'barcode'        => 'PRICE-CHECK-001',
        'price'          => 5000,
        'tax_rate'       => 18,
        'stock_quantity' => 42,
    ]);

    $response = $this->actingAs($user)
        ->getJson('/barcode/lookup?code=PRICE-CHECK-001')
        ->assertOk();

    $product = $response->json('product');
    expect((float) $product['price'])->toBe(5000.0);
    expect((float) $product['tax_rate'])->toBe(18.0);
    expect((float) $product['stock'])->toBe(42.0);
});

it('mobile pos route returns inertia page', function () {
    $user = createUserWithCompanyAndTrial();

    $this->actingAs($user)
        ->get('/pos/mobile')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Pos/MobilePos'));
});

it('barcode lookup works with leading zeros', function () {
    $user = createUserWithCompanyAndTrial();
    createBarcodeProduct($user, ['barcode' => '0012345678905', 'name' => 'Produit Leading Zero']);

    $this->actingAs($user)
        ->getJson('/barcode/lookup?code=0012345678905')
        ->assertOk()
        ->assertJsonPath('found', true)
        ->assertJsonPath('product.name', 'Produit Leading Zero');
});
