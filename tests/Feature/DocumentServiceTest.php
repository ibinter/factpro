<?php

use App\Models\Document;
use App\Models\DocumentLine;
use App\Services\DocumentIntegrityService;
use App\Services\DocumentService;

beforeEach(function () {
    $this->user = createUserWithCompanyAndTrial();
    $this->company = $this->user->currentCompany;
    $this->service = app(DocumentService::class);
    $this->integrity = app(DocumentIntegrityService::class);
});

function invoiceData(array $overrides = []): array
{
    return [
        'type' => 'invoice',
        'issue_date' => now()->toDateString(),
        'currency' => 'XOF',
        ...$overrides,
    ];
}

it('computes subtotal, prorated tax and total with line + global discounts', function () {
    // Ligne 1 : 2 × 1000 avec remise ligne 10% → 1800.00
    // Ligne 2 : 1 × 500 → 500.00
    // Sous-total : 2300.00 ; remise globale 5% → 115.00 ; base 2185.00
    // TVA 18% au prorata → 2185 × 18% = 393.30 ; total 2578.30
    $document = $this->service->create($this->company, $this->user, invoiceData([
        'discount_type' => 'percent',
        'discount_value' => 5,
    ]), [
        ['description' => 'Prestation A', 'quantity' => 2, 'unit_price' => 1000, 'discount_percent' => 10, 'tax_rate' => 18],
        ['description' => 'Prestation B', 'quantity' => 1, 'unit_price' => 500, 'tax_rate' => 18],
    ]);

    expect($document->lines)->toHaveCount(2)
        ->and((float) $document->lines[0]->line_total)->toBe(1800.00)
        ->and((float) $document->lines[1]->line_total)->toBe(500.00)
        ->and((float) $document->subtotal)->toBe(2300.00)
        ->and((float) $document->discount_amount)->toBe(115.00)
        ->and((float) $document->tax_amount)->toBe(393.30)
        ->and((float) $document->total)->toBe(2578.30);
});

it('numbers invoices sequentially per company and year (FAC-YYYY-0001, 0002…)', function () {
    $year = now()->year;

    $first = $this->service->create($this->company, $this->user, invoiceData(), [
        ['description' => 'A', 'quantity' => 1, 'unit_price' => 100],
    ]);
    $second = $this->service->create($this->company, $this->user, invoiceData(), [
        ['description' => 'B', 'quantity' => 1, 'unit_price' => 100],
    ]);

    expect($first->number)->toBe("FAC-{$year}-0001")
        ->and($second->number)->toBe("FAC-{$year}-0002");

    // Une autre société démarre sa propre séquence
    $other = createUserWithCompanyAndTrial();
    $otherDoc = $this->service->create($other->currentCompany, $other, invoiceData(), [
        ['description' => 'C', 'quantity' => 1, 'unit_price' => 100],
    ]);
    expect($otherDoc->number)->toBe("FAC-{$year}-0001");
});

it('seals a document on finalize (hash present, verify passes)', function () {
    $document = $this->service->create($this->company, $this->user, invoiceData(), [
        ['description' => 'Scellée', 'quantity' => 1, 'unit_price' => 5000, 'tax_rate' => 18],
    ]);

    expect($document->integrity_hash)->toBeNull();

    $this->service->finalize($document);
    $document->refresh();

    expect($document->integrity_hash)->not->toBeNull()
        ->and(strlen($document->integrity_hash))->toBe(64)
        ->and($document->finalized_at)->not->toBeNull()
        ->and($this->integrity->verify($document))->toBeTrue();
});

it('fails verification after a line is tampered with in database', function () {
    $document = $this->service->create($this->company, $this->user, invoiceData(), [
        ['description' => 'Intègre', 'quantity' => 1, 'unit_price' => 5000, 'tax_rate' => 18],
    ]);
    $this->service->finalize($document);

    // Falsification directe en base (contourne le service)
    DocumentLine::where('document_id', $document->id)->update(['unit_price' => 1]);

    $fresh = Document::with('lines')->findOrFail($document->id);

    expect($this->integrity->verify($fresh))->toBeFalse();
});

it('converts a quote into an invoice (lines copied, parent linked, quote marked converted)', function () {
    $year = now()->year;

    $quote = $this->service->create($this->company, $this->user, invoiceData([
        'type' => 'quote',
    ]), [
        ['description' => 'Ligne 1', 'quantity' => 2, 'unit_price' => 1000, 'tax_rate' => 18],
        ['description' => 'Ligne 2', 'quantity' => 3, 'unit_price' => 250, 'tax_rate' => 18],
    ]);
    expect($quote->number)->toBe("DEV-{$year}-0001");

    $invoice = $this->service->convert($quote, 'invoice', $this->user);

    expect($invoice->type)->toBe('invoice')
        ->and($invoice->status)->toBe('draft')
        ->and($invoice->parent_id)->toBe($quote->id)
        ->and($invoice->number)->toBe("FAC-{$year}-0001")
        ->and($invoice->lines)->toHaveCount(2)
        ->and($invoice->lines->pluck('description')->all())->toBe(['Ligne 1', 'Ligne 2'])
        ->and((float) $invoice->total)->toBe((float) $quote->fresh()->total);

    expect($quote->fresh()->status)->toBe('converted');
});
