<?php

use App\Models\AutoReorderRule;
use App\Models\Document;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\AutoReorderService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ─── Helpers ──────────────────────────────────────────────────────────────────

function makeProduct(\App\Models\Company $company, array $attrs = []): Product
{
    return Product::create([
        'company_id'    => $company->id,
        'type'          => 'product',
        'name'          => 'Produit '.uniqid(),
        'sku'           => 'SKU-'.uniqid(),
        'price'         => 1000,
        'cost'          => 600,
        'stock_quantity' => $attrs['stock_quantity'] ?? 50,
        'track_stock'   => true,
        ...$attrs,
    ]);
}

function makeSupplier(\App\Models\Company $company, array $attrs = []): Supplier
{
    return Supplier::create([
        'company_id' => $company->id,
        'name'       => 'Fournisseur '.uniqid(),
        ...$attrs,
    ]);
}

function makeRule(\App\Models\Company $company, Product $product, array $attrs = []): AutoReorderRule
{
    return AutoReorderRule::create([
        'company_id'        => $company->id,
        'product_id'        => $product->id,
        'trigger_threshold' => $attrs['trigger_threshold'] ?? 10,
        'order_quantity'    => $attrs['order_quantity'] ?? 20,
        'is_active'         => $attrs['is_active'] ?? true,
        'cooldown_hours'    => $attrs['cooldown_hours'] ?? 24,
        'auto_approve'      => $attrs['auto_approve'] ?? false,
        ...$attrs,
    ]);
}

// ─── Tests ────────────────────────────────────────────────────────────────────

it('creates an auto reorder rule', function () {
    $user    = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;
    $product = makeProduct($company);

    $this->actingAs($user)
        ->post(route('stock.auto-reorder.store'), [
            'product_id'        => $product->id,
            'trigger_threshold' => 5,
            'order_quantity'    => 15,
            'cooldown_hours'    => 12,
            'is_active'         => true,
            'auto_approve'      => false,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('auto_reorder_rules', [
        'company_id'        => $company->id,
        'product_id'        => $product->id,
        'trigger_threshold' => 5,
        'order_quantity'    => 15,
    ]);
});

it('triggers purchase order when stock below threshold', function () {
    $user    = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;
    $product = makeProduct($company, ['stock_quantity' => 3]);
    $rule    = makeRule($company, $product, ['trigger_threshold' => 10, 'order_quantity' => 25]);

    $service = app(AutoReorderService::class);
    $result  = $service->checkAndTrigger($company->id);

    expect($result['triggered'])->toBe(1)
        ->and($result['skipped'])->toBe(0);

    $this->assertDatabaseHas('documents', [
        'company_id' => $company->id,
        'type'       => 'purchase_order',
    ]);
});

it('respects cooldown between triggers', function () {
    $user    = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;
    $product = makeProduct($company, ['stock_quantity' => 2]);
    $rule    = makeRule($company, $product, [
        'trigger_threshold' => 10,
        'cooldown_hours'    => 48,
        'last_triggered_at' => now()->subHours(10),
    ]);

    $service = app(AutoReorderService::class);
    $result  = $service->checkAndTrigger($company->id);

    expect($result['triggered'])->toBe(0)
        ->and($result['skipped'])->toBe(1);
});

it('creates draft purchase order by default', function () {
    $user    = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;
    $product = makeProduct($company, ['stock_quantity' => 1]);
    $rule    = makeRule($company, $product, ['auto_approve' => false]);

    $service  = app(AutoReorderService::class);
    $document = $service->createPurchaseOrder($rule);

    expect($document->status)->toBe('draft')
        ->and($document->type)->toBe('purchase_order')
        ->and($document->finalized_at)->toBeNull();
});

it('creates finalized order when auto_approve enabled', function () {
    $user    = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;
    $product = makeProduct($company, ['stock_quantity' => 1]);
    $rule    = makeRule($company, $product, ['auto_approve' => true]);

    $service  = app(AutoReorderService::class);
    $document = $service->createPurchaseOrder($rule);

    expect($document->finalized_at)->not->toBeNull()
        ->and($document->type)->toBe('purchase_order');
});

it('simulate returns products that would trigger', function () {
    $user    = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;

    $lowProduct  = makeProduct($company, ['stock_quantity' => 2]);
    $okProduct   = makeProduct($company, ['stock_quantity' => 100]);

    makeRule($company, $lowProduct, ['trigger_threshold' => 10]);
    makeRule($company, $okProduct,  ['trigger_threshold' => 10]);

    $service = app(AutoReorderService::class);
    $result  = $service->simulate($company->id);

    expect($result['would_trigger'])->toBe(1);

    $triggered = collect($result['products'])->where('would_trigger', true)->first();
    expect($triggered['product_id'])->toBe($lowProduct->id);
});

it('low stock products detection works', function () {
    $user    = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;

    $lowProduct = makeProduct($company, ['stock_quantity' => 3]);
    $okProduct  = makeProduct($company, ['stock_quantity' => 50]);

    makeRule($company, $lowProduct, ['trigger_threshold' => 10]);
    makeRule($company, $okProduct,  ['trigger_threshold' => 10]);

    $service  = app(AutoReorderService::class);
    $lowStock = $service->getLowStockProducts($company->id);

    expect($lowStock)->toHaveCount(1);
    expect($lowStock->first()->product_id)->toBe($lowProduct->id);
});

it('manual trigger creates purchase order', function () {
    $user    = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;
    $product = makeProduct($company, ['stock_quantity' => 50]);
    $rule    = makeRule($company, $product);

    $this->actingAs($user)
        ->post(route('stock.auto-reorder.trigger', $rule->id))
        ->assertRedirect();

    $this->assertDatabaseHas('documents', [
        'company_id' => $company->id,
        'type'       => 'purchase_order',
    ]);

    expect(AutoReorderRule::find($rule->id)->last_triggered_at)->not->toBeNull();
});

it('destroys rule', function () {
    $user    = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;
    $product = makeProduct($company);
    $rule    = makeRule($company, $product);

    $this->actingAs($user)
        ->delete(route('stock.auto-reorder.destroy', $rule->id))
        ->assertRedirect();

    $this->assertDatabaseMissing('auto_reorder_rules', ['id' => $rule->id]);
});

it('isolates rules between companies', function () {
    $user1 = createUserWithCompanyAndTrial();
    $user2 = createUserWithCompanyAndTrial();

    $company1 = $user1->currentCompany;
    $company2 = $user2->currentCompany;

    $product1 = makeProduct($company1, ['stock_quantity' => 2]);
    $product2 = makeProduct($company2, ['stock_quantity' => 2]);

    makeRule($company1, $product1, ['trigger_threshold' => 10]);
    makeRule($company2, $product2, ['trigger_threshold' => 10]);

    $service = app(AutoReorderService::class);

    $result1 = $service->checkAndTrigger($company1->id);
    $result2 = $service->checkAndTrigger($company2->id);

    expect($result1['triggered'])->toBe(1);
    expect($result2['triggered'])->toBe(1);

    // Company 1 ne doit pas avoir les documents de company 2
    $docs1 = Document::where('company_id', $company1->id)->where('type', 'purchase_order')->get();
    $docs2 = Document::where('company_id', $company2->id)->where('type', 'purchase_order')->get();

    expect($docs1)->toHaveCount(1);
    expect($docs2)->toHaveCount(1);
    expect($docs1->first()->company_id)->not->toBe($docs2->first()->company_id);
});

it('updates last_triggered_at after trigger', function () {
    $user    = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;
    $product = makeProduct($company, ['stock_quantity' => 1]);
    $rule    = makeRule($company, $product);

    expect($rule->last_triggered_at)->toBeNull();

    $service = app(AutoReorderService::class);
    $service->createPurchaseOrder($rule);

    expect($rule->fresh()->last_triggered_at)->not->toBeNull();
});
