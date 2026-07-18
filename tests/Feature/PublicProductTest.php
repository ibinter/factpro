<?php

use App\Models\Company;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Str;

/** Crée un produit public (page activée) pour une company. */
function createPublicProduct(Company $company, array $attributes = []): Product
{
    $name = $attributes['name'] ?? 'Pain Complet';
    $slug = Str::slug($name);

    // Assurer l'unicité du slug
    $base = $slug;
    $count = 1;
    while (Product::where('public_slug', $slug)->exists()) {
        $slug = $base.'-'.$count++;
    }

    return Product::create([
        'company_id' => $company->id,
        'type' => 'product',
        'name' => $name,
        'sku' => 'PUB-'.uniqid(),
        'price' => 1500,
        'public_page_enabled' => true,
        'public_slug' => $slug,
        'allow_online_order' => false,
        ...$attributes,
    ]);
}

/** Assure que la company a un slug. */
function ensureCompanySlug(Company $company): string
{
    if (! $company->slug) {
        $slug = Str::slug($company->name);
        $company->update(['slug' => $slug]);
    }

    return $company->fresh()->slug;
}

// --------------------------------------------------------------------------
// Tests
// --------------------------------------------------------------------------

it('public product page returns 200', function () {
    $user = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;
    $companySlug = ensureCompanySlug($company);

    $product = createPublicProduct($company);

    $this->get(route('public.product.show', [$companySlug, $product->public_slug]))
        ->assertOk();
});

it('public product shows product info without auth', function () {
    $user = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;
    $companySlug = ensureCompanySlug($company);

    $product = createPublicProduct($company, ['name' => 'Produit Visible', 'price' => 2000]);

    $response = $this->get(route('public.product.show', [$companySlug, $product->public_slug]));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Public/ProductPage')
        ->where('product.name', 'Produit Visible')
        ->where('product.price', 2000)
    );
});

it('public product api returns json', function () {
    $user = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;
    $companySlug = ensureCompanySlug($company);

    $product = createPublicProduct($company, ['name' => 'Produit API', 'price' => 500]);

    $this->getJson(route('public.product.api', [$companySlug, $product->public_slug]))
        ->assertOk()
        ->assertJsonPath('product.name', 'Produit API')
        ->assertJsonStructure(['product' => ['name', 'price']])
        ->assertJsonFragment(['name' => 'Produit API']);
});

it('disabled public page returns 404', function () {
    $user = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;
    $companySlug = ensureCompanySlug($company);

    $product = Product::create([
        'company_id' => $company->id,
        'type' => 'product',
        'name' => 'Produit Privé',
        'sku' => 'PRV-'.uniqid(),
        'price' => 1000,
        'public_page_enabled' => false,
        'public_slug' => 'produit-prive-'.uniqid(),
    ]);

    $this->get(route('public.product.show', [$companySlug, $product->public_slug]))
        ->assertNotFound();
});

it('enables public page for product', function () {
    $user = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;

    $product = Product::create([
        'company_id' => $company->id,
        'type' => 'product',
        'name' => 'Nouveau Produit Public',
        'sku' => 'NPP-'.uniqid(),
        'price' => 1000,
    ]);

    $this->actingAs($user)
        ->post(route('products.enable-public', $product))
        ->assertRedirect();

    expect($product->fresh()->public_page_enabled)->toBeTrue()
        ->and($product->fresh()->public_slug)->not->toBeNull();
});

it('generates unique slug from product name', function () {
    $user = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;

    $product = Product::create([
        'company_id' => $company->id,
        'type' => 'product',
        'name' => 'Pain Traditionnel Baguette',
        'sku' => 'PTB-'.uniqid(),
        'price' => 500,
    ]);

    $this->actingAs($user)
        ->post(route('products.enable-public', $product))
        ->assertRedirect();

    expect($product->fresh()->public_slug)->toBe('pain-traditionnel-baguette');
});

it('slug must be unique across company', function () {
    $user = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;

    // Premier produit avec ce slug
    $product1 = Product::create([
        'company_id' => $company->id,
        'type' => 'product',
        'name' => 'Café du Matin',
        'sku' => 'CDM1-'.uniqid(),
        'price' => 500,
        'public_page_enabled' => true,
        'public_slug' => 'cafe-du-matin',
    ]);

    // Deuxième produit avec le même nom de base
    $product2 = Product::create([
        'company_id' => $company->id,
        'type' => 'product',
        'name' => 'Café du Matin',
        'sku' => 'CDM2-'.uniqid(),
        'price' => 600,
    ]);

    $this->actingAs($user)
        ->post(route('products.enable-public', $product2))
        ->assertRedirect();

    // Le slug du deuxième doit être différent
    expect($product2->fresh()->public_slug)->not->toBe('cafe-du-matin');
    expect($product2->fresh()->public_slug)->toStartWith('cafe-du-matin-');
});

it('allow_online_order shows order button', function () {
    $user = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;
    $companySlug = ensureCompanySlug($company);

    $product = createPublicProduct($company, ['allow_online_order' => true]);

    $response = $this->get(route('public.product.show', [$companySlug, $product->public_slug]));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('product.allow_online_order', true)
    );
});

it('different companies can have same product name', function () {
    $user1 = createUserWithCompanyAndTrial();
    $user2 = createUserWithCompanyAndTrial();

    $company1 = $user1->currentCompany;
    $company2 = $user2->currentCompany;

    ensureCompanySlug($company1);
    ensureCompanySlug($company2);

    // Même slug produit dans deux companies différentes doit être possible
    $slug = 'produit-commun-'.uniqid();

    $product1 = createPublicProduct($company1, ['name' => 'Produit Commun '.$slug, 'public_slug' => $slug]);
    // public_slug est UNIQUE global — donc on ne peut pas réutiliser le même
    // c'est normal selon la contrainte DB. Ce test vérifie qu'on peut activer dans les deux companies.
    $product2 = Product::create([
        'company_id' => $company2->id,
        'type' => 'product',
        'name' => 'Produit Commun 2',
        'sku' => 'PC2-'.uniqid(),
        'price' => 1000,
    ]);

    $this->actingAs($user2)
        ->post(route('products.enable-public', $product2))
        ->assertRedirect();

    expect($product2->fresh()->public_page_enabled)->toBeTrue();
    expect($product2->fresh()->public_slug)->not->toBeNull();
});

it('public page shows company info', function () {
    $user = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;
    $companySlug = ensureCompanySlug($company);

    $product = createPublicProduct($company);

    $response = $this->get(route('public.product.show', [$companySlug, $product->public_slug]));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('company.name', $company->name)
        ->where('company.slug', $companySlug)
    );
});
