<?php

use App\Models\Company;
use App\Models\Document;
use App\Models\DocumentLine;
use App\Models\Product;
use App\Services\AbcAnalysisService;
use App\Services\DocumentService;

/** Crée un produit simple pour une company. */
function createProductForAbc(Company $company, array $attributes = []): Product
{
    return Product::create([
        'company_id' => $company->id,
        'type' => 'product',
        'name' => $attributes['name'] ?? 'Produit ABC '.uniqid(),
        'sku' => 'ABC-'.uniqid(),
        'price' => $attributes['price'] ?? 1000,
        'track_stock' => false,
        'stock_quantity' => $attributes['stock_quantity'] ?? 10,
        ...$attributes,
    ]);
}

/** Crée une facture finalisée avec des lignes produits. */
function createFinalizedInvoiceForAbc(Company $company, array $lineItems, ?string $issueDate = null): Document
{
    $docService = app(DocumentService::class);
    $user = \App\Models\User::find($company->owner_id);

    $document = $docService->create($company, $user, [
        'type' => 'invoice',
        'issue_date' => $issueDate ?? now()->toDateString(),
        'currency' => $company->currency ?? 'XOF',
    ], $lineItems);

    $docService->finalize($document);

    return $document->fresh();
}

// --------------------------------------------------------------------------
// Tests
// --------------------------------------------------------------------------

it('classifies products into abc categories', function () {
    $user = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;

    // Créer des produits avec des ventes très différentes
    $productA = createProductForAbc($company, ['name' => 'Top Vendeur', 'price' => 10000]);
    $productB = createProductForAbc($company, ['name' => 'Vendeur Moyen', 'price' => 500]);
    $productC = createProductForAbc($company, ['name' => 'Peu Vendu', 'price' => 100]);

    createFinalizedInvoiceForAbc($company, [
        ['product_id' => $productA->id, 'description' => $productA->name, 'quantity' => 10, 'unit_price' => 10000],
        ['product_id' => $productB->id, 'description' => $productB->name, 'quantity' => 2, 'unit_price' => 500],
        ['product_id' => $productC->id, 'description' => $productC->name, 'quantity' => 1, 'unit_price' => 100],
    ]);

    $service = app(AbcAnalysisService::class);
    $result = $service->analyze($company->id, 12);

    expect($result['products'])->not->toBeEmpty();

    $classes = collect($result['products'])->pluck('class')->unique()->sort()->values()->toArray();
    expect($classes)->toContain('A');
});

it('class a products represent 80 percent of revenue', function () {
    $user = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;

    $productA = createProductForAbc($company, ['name' => 'Blockbuster', 'price' => 50000]);
    $productB = createProductForAbc($company, ['name' => 'Secondaire', 'price' => 1000]);
    $productC = createProductForAbc($company, ['name' => 'Marginal', 'price' => 100]);

    createFinalizedInvoiceForAbc($company, [
        ['product_id' => $productA->id, 'description' => $productA->name, 'quantity' => 100, 'unit_price' => 50000],
        ['product_id' => $productB->id, 'description' => $productB->name, 'quantity' => 5, 'unit_price' => 1000],
        ['product_id' => $productC->id, 'description' => $productC->name, 'quantity' => 2, 'unit_price' => 100],
    ]);

    $service = app(AbcAnalysisService::class);
    $result = $service->analyze($company->id, 12);

    $classARevenue = $result['summary']['A']['revenue'];
    $totalRevenue = $result['total_revenue'];

    // Les produits A doivent représenter au moins 75% du CA (tolérance pour les seuils)
    expect($classARevenue / $totalRevenue)->toBeGreaterThan(0.75);
});

it('returns sorted products by revenue descending', function () {
    $user = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;

    $p1 = createProductForAbc($company, ['name' => 'Haut CA', 'price' => 5000]);
    $p2 = createProductForAbc($company, ['name' => 'Bas CA', 'price' => 100]);

    createFinalizedInvoiceForAbc($company, [
        ['product_id' => $p1->id, 'description' => $p1->name, 'quantity' => 10, 'unit_price' => 5000],
        ['product_id' => $p2->id, 'description' => $p2->name, 'quantity' => 1, 'unit_price' => 100],
    ]);

    $service = app(AbcAnalysisService::class);
    $result = $service->analyze($company->id, 12);

    $revenues = collect($result['products'])->where('revenue', '>', 0)->pluck('revenue')->values();

    // Vérifier que les revenus sont décroissants
    for ($i = 0; $i < $revenues->count() - 1; $i++) {
        expect($revenues[$i])->toBeGreaterThanOrEqual($revenues[$i + 1]);
    }
});

it('handles company with no sales', function () {
    $user = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;

    createProductForAbc($company, ['name' => 'Produit Sans Vente']);

    $service = app(AbcAnalysisService::class);
    $result = $service->analyze($company->id, 12);

    expect($result['total_revenue'])->toBe(0.0);
    expect($result['products'])->not->toBeEmpty();
    expect($result['products'][0]['class'])->toBe('C');
});

it('filters by period months correctly', function () {
    $user = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;

    $product = createProductForAbc($company, ['name' => 'Produit Récent', 'price' => 1000]);
    $oldProduct = createProductForAbc($company, ['name' => 'Produit Ancien', 'price' => 1000]);

    // Vente récente (dans les 3 mois)
    createFinalizedInvoiceForAbc($company, [
        ['product_id' => $product->id, 'description' => $product->name, 'quantity' => 5, 'unit_price' => 1000],
    ], now()->subMonths(1)->toDateString());

    // Vente ancienne (plus de 6 mois)
    createFinalizedInvoiceForAbc($company, [
        ['product_id' => $oldProduct->id, 'description' => $oldProduct->name, 'quantity' => 10, 'unit_price' => 1000],
    ], now()->subMonths(8)->toDateString());

    $service = app(AbcAnalysisService::class);

    // Sur 3 mois : seul le produit récent a des ventes
    $result3m = $service->analyze($company->id, 3);
    $recent = collect($result3m['products'])->firstWhere('product_id', $product->id);
    $old = collect($result3m['products'])->firstWhere('product_id', $oldProduct->id);

    expect((float) $recent['revenue'])->toBeGreaterThan(0);
    expect((float) $old['revenue'])->toBe(0.0);

    // Sur 12 mois : les deux ont des ventes
    $result12m = $service->analyze($company->id, 12);
    $old12 = collect($result12m['products'])->firstWhere('product_id', $oldProduct->id);
    expect((float) $old12['revenue'])->toBeGreaterThan(0);
});

it('returns correct summary counts', function () {
    $user = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;

    $p1 = createProductForAbc($company, ['name' => 'Top 1', 'price' => 10000]);
    $p2 = createProductForAbc($company, ['name' => 'Top 2', 'price' => 8000]);
    $p3 = createProductForAbc($company, ['name' => 'Moyen', 'price' => 500]);
    $p4 = createProductForAbc($company, ['name' => 'Bas', 'price' => 50]);

    createFinalizedInvoiceForAbc($company, [
        ['product_id' => $p1->id, 'description' => $p1->name, 'quantity' => 10, 'unit_price' => 10000],
        ['product_id' => $p2->id, 'description' => $p2->name, 'quantity' => 8, 'unit_price' => 8000],
        ['product_id' => $p3->id, 'description' => $p3->name, 'quantity' => 3, 'unit_price' => 500],
        ['product_id' => $p4->id, 'description' => $p4->name, 'quantity' => 1, 'unit_price' => 50],
    ]);

    $service = app(AbcAnalysisService::class);
    $result = $service->analyze($company->id, 12);

    $totalCount = $result['summary']['A']['count']
        + $result['summary']['B']['count']
        + $result['summary']['C']['count'];

    // La somme des comptes = nombre total de produits actifs
    expect($totalCount)->toBe(count($result['products']));
});

it('recommendations differ by class', function () {
    $service = app(AbcAnalysisService::class);

    $recA = $service->getRecommendations('A');
    $recB = $service->getRecommendations('B');
    $recC = $service->getRecommendations('C');

    expect($recA)->not->toBe($recB)
        ->and($recB)->not->toBe($recC)
        ->and($recA)->not->toBeEmpty()
        ->and($recB)->not->toBeEmpty()
        ->and($recC)->not->toBeEmpty();
});

it('products with zero sales are class c', function () {
    $user = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;

    // Produit avec des ventes
    $pWithSales = createProductForAbc($company, ['name' => 'Avec Ventes', 'price' => 5000]);
    // Produit sans aucune vente
    $pNoSales = createProductForAbc($company, ['name' => 'Sans Ventes', 'price' => 1000]);

    createFinalizedInvoiceForAbc($company, [
        ['product_id' => $pWithSales->id, 'description' => $pWithSales->name, 'quantity' => 5, 'unit_price' => 5000],
    ]);

    $service = app(AbcAnalysisService::class);
    $result = $service->analyze($company->id, 12);

    $noSalesItem = collect($result['products'])->firstWhere('product_id', $pNoSales->id);

    expect($noSalesItem)->not->toBeNull()
        ->and($noSalesItem['revenue'])->toBe(0.0)
        ->and($noSalesItem['class'])->toBe('C');
});

it('isolates analysis between companies', function () {
    $user1 = createUserWithCompanyAndTrial();
    $user2 = createUserWithCompanyAndTrial();

    $company1 = $user1->currentCompany;
    $company2 = $user2->currentCompany;

    $p1 = createProductForAbc($company1, ['name' => 'Produit Company1', 'price' => 5000]);
    $p2 = createProductForAbc($company2, ['name' => 'Produit Company2', 'price' => 3000]);

    createFinalizedInvoiceForAbc($company1, [
        ['product_id' => $p1->id, 'description' => $p1->name, 'quantity' => 5, 'unit_price' => 5000],
    ]);

    $service = app(AbcAnalysisService::class);

    // L'analyse de company1 ne doit pas contenir le produit de company2
    $result1 = $service->analyze($company1->id, 12);
    $ids1 = collect($result1['products'])->pluck('product_id')->toArray();
    expect($ids1)->not->toContain($p2->id);

    // L'analyse de company2 ne doit pas contenir le produit de company1
    $result2 = $service->analyze($company2->id, 12);
    $ids2 = collect($result2['products'])->pluck('product_id')->toArray();
    expect($ids2)->not->toContain($p1->id);
});

it('pareto analysis cumulative percentage reaches 100', function () {
    $user = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;

    $p1 = createProductForAbc($company, ['name' => 'P1', 'price' => 8000]);
    $p2 = createProductForAbc($company, ['name' => 'P2', 'price' => 1500]);
    $p3 = createProductForAbc($company, ['name' => 'P3', 'price' => 500]);

    createFinalizedInvoiceForAbc($company, [
        ['product_id' => $p1->id, 'description' => $p1->name, 'quantity' => 5, 'unit_price' => 8000],
        ['product_id' => $p2->id, 'description' => $p2->name, 'quantity' => 3, 'unit_price' => 1500],
        ['product_id' => $p3->id, 'description' => $p3->name, 'quantity' => 2, 'unit_price' => 500],
    ]);

    $service = app(AbcAnalysisService::class);
    $result = $service->analyze($company->id, 12);

    $productsWithSales = collect($result['products'])->where('revenue', '>', 0);
    $lastCumulative = $productsWithSales->last()['cumulative_pct'] ?? 0;

    // Le dernier produit avec des ventes doit atteindre ~100%
    expect($lastCumulative)->toBeGreaterThan(99.0);
});
