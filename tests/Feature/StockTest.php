<?php

use App\Models\Product;
use App\Models\StockMovement;
use App\Notifications\LowStockAlert;
use App\Services\DocumentService;
use App\Services\StockService;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->user = createUserWithCompanyAndTrial();
    $this->company = $this->user->currentCompany;
    $this->documents = app(DocumentService::class);
    $this->stock = app(StockService::class);
});

function createStockedProduct(\App\Models\Company $company, array $attributes = []): Product
{
    return Product::create([
        'company_id' => $company->id,
        'type' => 'product',
        'name' => 'Produit Stocké',
        'sku' => 'STK-'.uniqid(),
        'price' => 1000,
        'cost' => 400,
        'track_stock' => true,
        'stock_quantity' => 50,
        ...$attributes,
    ]);
}

it('debits stock when an invoice with a tracked product is finalized (idempotent)', function () {
    $product = createStockedProduct($this->company, ['stock_quantity' => 50]);

    $invoice = $this->documents->create($this->company, $this->user, [
        'type' => 'invoice',
        'issue_date' => now()->toDateString(),
        'currency' => 'XOF',
    ], [
        ['product_id' => $product->id, 'description' => $product->name, 'quantity' => 3, 'unit_price' => 1000],
    ]);

    $this->documents->finalize($invoice);

    expect((float) $product->fresh()->stock_quantity)->toBe(47.0);

    $movement = StockMovement::where('document_id', $invoice->id)->first();
    expect($movement)->not->toBeNull()
        ->and($movement->type)->toBe('out')
        ->and((float) $movement->quantity)->toBe(3.0)
        ->and((float) $movement->stock_before)->toBe(50.0)
        ->and((float) $movement->stock_after)->toBe(47.0);

    // Une seconde finalisation ne débite pas deux fois
    $this->documents->finalize($invoice->fresh());

    expect((float) $product->fresh()->stock_quantity)->toBe(47.0)
        ->and(StockMovement::where('document_id', $invoice->id)->count())->toBe(1);
});

it('credits stock when a credit note is finalized', function () {
    $product = createStockedProduct($this->company, ['stock_quantity' => 10]);

    $creditNote = $this->documents->create($this->company, $this->user, [
        'type' => 'credit_note',
        'issue_date' => now()->toDateString(),
        'currency' => 'XOF',
    ], [
        ['product_id' => $product->id, 'description' => $product->name, 'quantity' => 4, 'unit_price' => 1000],
    ]);

    $this->documents->finalize($creditNote);

    expect((float) $product->fresh()->stock_quantity)->toBe(14.0);

    $movement = StockMovement::where('document_id', $creditNote->id)->first();
    expect($movement->type)->toBe('in')
        ->and((float) $movement->quantity)->toBe(4.0);
});

it('sends a low stock alert to the company owner when stock falls under the threshold', function () {
    Notification::fake();

    $product = createStockedProduct($this->company, [
        'stock_quantity' => 6,
        'stock_alert_threshold' => 5,
    ]);

    $invoice = $this->documents->create($this->company, $this->user, [
        'type' => 'invoice',
        'issue_date' => now()->toDateString(),
        'currency' => 'XOF',
    ], [
        ['product_id' => $product->id, 'description' => $product->name, 'quantity' => 2, 'unit_price' => 1000],
    ]);

    $this->documents->finalize($invoice);

    Notification::assertSentTo(
        $this->company->owner,
        LowStockAlert::class,
        fn ($notification) => $notification->product->id === $product->id
    );
});

it('records a manual adjustment to a target stock via StockService', function () {
    $this->actingAs($this->user);
    $product = createStockedProduct($this->company, ['stock_quantity' => 20]);

    $movement = $this->stock->record($product, 'adjustment', 0, [
        'target' => 12.5,
        'reason' => 'Casse constatée',
    ]);

    expect((float) $product->fresh()->stock_quantity)->toBe(12.5)
        ->and($movement->type)->toBe('adjustment')
        ->and((float) $movement->quantity)->toBe(7.5)
        ->and((float) $movement->stock_before)->toBe(20.0)
        ->and((float) $movement->stock_after)->toBe(12.5);
});

it('applies inventory gaps via the stock inventory endpoint', function () {
    $withGap = createStockedProduct($this->company, ['stock_quantity' => 30]);
    $noGap = createStockedProduct($this->company, ['name' => 'Produit Exact', 'stock_quantity' => 8]);

    $response = $this->actingAs($this->user)->post(route('stock.inventory.apply'), [
        'items' => [
            ['product_id' => $withGap->id, 'counted' => 27],
            ['product_id' => $noGap->id, 'counted' => 8],
        ],
    ]);

    $response->assertRedirect(route('stock.index'));

    expect((float) $withGap->fresh()->stock_quantity)->toBe(27.0)
        ->and((float) $noGap->fresh()->stock_quantity)->toBe(8.0);

    expect(StockMovement::where('product_id', $withGap->id)->where('type', 'inventory')->count())->toBe(1)
        ->and(StockMovement::where('product_id', $noGap->id)->count())->toBe(0);
});

it('records a manual movement via the stock adjust endpoint', function () {
    $product = createStockedProduct($this->company, ['stock_quantity' => 10, 'cost' => 100]);

    $response = $this->actingAs($this->user)->post(route('stock.adjust'), [
        'product_id' => $product->id,
        'type' => 'in',
        'quantity' => 10,
        'unit_cost' => 200,
        'reason' => 'Réception fournisseur',
    ]);

    $response->assertRedirect();

    $fresh = $product->fresh();
    // CMUP : (10×100 + 10×200) / 20 = 150
    expect((float) $fresh->stock_quantity)->toBe(20.0)
        ->and((float) $fresh->cost)->toBe(150.0);
});

it('shows the stock pages (index, inventory, valuation)', function () {
    createStockedProduct($this->company);

    $this->actingAs($this->user)->get(route('stock.index'))->assertOk();
    $this->actingAs($this->user)->get(route('stock.inventory'))->assertOk();
    $this->actingAs($this->user)->get(route('stock.valuation'))->assertOk();
});
