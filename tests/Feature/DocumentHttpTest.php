<?php

use App\Models\Document;
use App\Services\DocumentService;

beforeEach(function () {
    $this->user = createUserWithCompanyAndTrial();
    $this->company = $this->user->currentCompany;
});

it('lets a trial user create an invoice via POST /documents (with trial watermark)', function () {
    $customer = createCustomerFor($this->company);

    $response = $this->actingAs($this->user)->post('/documents', [
        'type' => 'invoice',
        'customer_id' => $customer->id,
        'issue_date' => now()->toDateString(),
        'currency' => 'XOF',
        'lines' => [
            ['description' => 'Prestation web', 'quantity' => 2, 'unit_price' => 50000, 'tax_rate' => 18],
        ],
    ]);

    $document = Document::where('company_id', $this->company->id)->firstOrFail();

    $response->assertRedirect(route('documents.show', $document));
    $response->assertSessionHas('success');

    expect($document->type)->toBe('invoice')
        ->and($document->customer_id)->toBe($customer->id)
        ->and($document->trial_watermark)->toBeTrue()
        ->and((float) $document->total)->toBe(118000.00)
        ->and($document->lines()->count())->toBe(1);
});

it('forbids viewing a document that belongs to another company', function () {
    $stranger = createUserWithCompanyAndTrial();
    $foreignDocument = app(DocumentService::class)->create(
        $stranger->currentCompany,
        $stranger,
        ['type' => 'invoice', 'issue_date' => now()->toDateString(), 'currency' => 'XOF'],
        [['description' => 'Secret', 'quantity' => 1, 'unit_price' => 1000]],
    );

    $this->actingAs($this->user)
        ->get(route('documents.show', $foreignDocument))
        ->assertForbidden();
});

it('refuses updating a finalized document and leaves it untouched', function () {
    $service = app(DocumentService::class);

    $document = $service->create($this->company, $this->user, [
        'type' => 'invoice',
        'issue_date' => now()->toDateString(),
        'currency' => 'XOF',
    ], [
        ['description' => 'Originale', 'quantity' => 1, 'unit_price' => 10000, 'tax_rate' => 18],
    ]);
    $service->finalize($document);

    $originalTotal = (float) $document->fresh()->total;

    $response = $this->actingAs($this->user)
        ->from(route('documents.show', $document))
        ->put(route('documents.update', $document), [
            'type' => 'invoice',
            'issue_date' => now()->toDateString(),
            'currency' => 'XOF',
            'lines' => [
                ['description' => 'Tentative de modification', 'quantity' => 1, 'unit_price' => 1, 'tax_rate' => 0],
            ],
        ]);

    $response->assertRedirect(route('documents.show', $document));
    $response->assertSessionHas('error');

    $fresh = $document->fresh(['lines']);
    expect((float) $fresh->total)->toBe($originalTotal)
        ->and($fresh->lines)->toHaveCount(1)
        ->and($fresh->lines[0]->description)->toBe('Originale')
        ->and($fresh->integrity_hash)->not->toBeNull();
});
