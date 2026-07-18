<?php

use App\Models\Document;
use App\Services\DocumentService;

beforeEach(function () {
    $this->user = createUserWithCompanyAndTrial();
    $this->company = $this->user->currentCompany;
});

it('creates a quittance document', function () {
    $response = $this->actingAs($this->user)->post('/documents', [
        'type' => 'quittance',
        'issue_date' => now()->toDateString(),
        'currency' => 'XOF',
        'lines' => [
            ['description' => 'Solde de tout compte', 'quantity' => 1, 'unit_price' => 100000, 'tax_rate' => 0],
        ],
    ]);

    $document = Document::where('company_id', $this->company->id)->where('type', 'quittance')->firstOrFail();

    $response->assertRedirect(route('documents.show', $document));
    expect($document->type)->toBe('quittance')
        ->and($document->type_label)->toBe('Quittance');
});

it('creates an rma document', function () {
    $response = $this->actingAs($this->user)->post('/documents', [
        'type' => 'rma',
        'issue_date' => now()->toDateString(),
        'currency' => 'XOF',
        'lines' => [
            ['description' => 'Retour produit défectueux', 'quantity' => 1, 'unit_price' => 50000, 'tax_rate' => 18],
        ],
    ]);

    $document = Document::where('company_id', $this->company->id)->where('type', 'rma')->firstOrFail();

    $response->assertRedirect(route('documents.show', $document));
    expect($document->type)->toBe('rma')
        ->and($document->type_label)->toBe('Bon de Retour RMA');
});

it('creates a remittance document', function () {
    $response = $this->actingAs($this->user)->post('/documents', [
        'type' => 'remittance',
        'issue_date' => now()->toDateString(),
        'currency' => 'XOF',
        'lines' => [
            ['description' => 'Dépôt chèques', 'quantity' => 1, 'unit_price' => 200000, 'tax_rate' => 0],
        ],
    ]);

    $document = Document::where('company_id', $this->company->id)->where('type', 'remittance')->firstOrFail();

    $response->assertRedirect(route('documents.show', $document));
    expect($document->type)->toBe('remittance')
        ->and($document->type_label)->toBe('Bordereau de Remise');
});

it('generates correct number prefix for rma (RMA-XXXX)', function () {
    $service = app(DocumentService::class);
    $document = $service->create($this->company, $this->user, [
        'type' => 'rma',
        'issue_date' => now()->toDateString(),
        'currency' => 'XOF',
    ], [
        ['description' => 'Retour article', 'quantity' => 1, 'unit_price' => 10000],
    ]);

    expect($document->number)->toStartWith('RMA-');
});

it('clones an invoice into a draft', function () {
    $service = app(DocumentService::class);
    $original = $service->create($this->company, $this->user, [
        'type' => 'invoice',
        'issue_date' => now()->toDateString(),
        'currency' => 'XOF',
        'notes' => 'Notes originales',
    ], [
        ['description' => 'Prestation', 'quantity' => 2, 'unit_price' => 50000, 'tax_rate' => 18],
    ]);

    $response = $this->actingAs($this->user)
        ->post(route('documents.clone', $original));

    $clone = Document::where('company_id', $this->company->id)
        ->where('type', 'invoice')
        ->where('id', '!=', $original->id)
        ->firstOrFail();

    $response->assertRedirect(route('documents.edit', $clone));
    $response->assertSessionHas('success', 'Document dupliqué.');

    expect($clone->type)->toBe('invoice')
        ->and($clone->notes)->toBe('Notes originales')
        ->and($clone->lines()->count())->toBe(1);
});

it('clone resets status to draft', function () {
    $service = app(DocumentService::class);
    $original = $service->create($this->company, $this->user, [
        'type' => 'invoice',
        'issue_date' => now()->toDateString(),
        'currency' => 'XOF',
    ], [
        ['description' => 'Article', 'quantity' => 1, 'unit_price' => 10000],
    ]);
    $original->update(['status' => 'sent']);

    $this->actingAs($this->user)->post(route('documents.clone', $original));

    $clone = Document::where('company_id', $this->company->id)
        ->where('type', 'invoice')
        ->where('id', '!=', $original->id)
        ->firstOrFail();

    expect($clone->status)->toBe('draft');
});

it('clone is isolated between companies', function () {
    $service = app(DocumentService::class);
    $original = $service->create($this->company, $this->user, [
        'type' => 'invoice',
        'issue_date' => now()->toDateString(),
        'currency' => 'XOF',
    ], [
        ['description' => 'Article', 'quantity' => 1, 'unit_price' => 10000],
    ]);

    $otherUser = createUserWithCompanyAndTrial();

    $this->actingAs($otherUser)
        ->post(route('documents.clone', $original))
        ->assertForbidden();
});
