<?php

use App\Services\DocumentService;

beforeEach(function () {
    $this->user = createUserWithCompanyAndTrial();
    $this->company = $this->user->currentCompany;

    $this->document = app(DocumentService::class)->create(
        $this->company,
        $this->user,
        ['type' => 'pos_ticket', 'issue_date' => now()->toDateString(), 'currency' => 'XOF'],
        [['description' => 'Café expresso', 'quantity' => 2, 'unit_price' => 1500, 'tax_rate' => 18]],
    );
});

it('renders the thermal ticket for the owner (200, number + QR visible)', function () {
    $response = $this->actingAs($this->user)
        ->get(route('documents.thermal', $this->document).'?width=80&copies=2');

    $response->assertOk()
        ->assertSee($this->document->number)
        ->assertSee('data:image/png;base64', false)
        ->assertSee('80mm', false);
});

it('forbids printing a thermal ticket of another company', function () {
    $stranger = createUserWithCompanyAndTrial();

    $this->actingAs($stranger)
        ->get(route('documents.thermal', $this->document))
        ->assertForbidden();
});

it('finalizes (seals) the document when the thermal ticket is rendered', function () {
    expect($this->document->isFinalized())->toBeFalse();

    $this->actingAs($this->user)
        ->get(route('documents.thermal', $this->document))
        ->assertOk();

    $fresh = $this->document->fresh();
    expect($fresh->isFinalized())->toBeTrue()
        ->and($fresh->integrity_hash)->not->toBeNull();
});

it('clamps invalid width and copies to safe defaults', function () {
    $response = $this->actingAs($this->user)
        ->get(route('documents.thermal', $this->document).'?width=999&copies=42');

    $response->assertOk()->assertSee('80mm', false);
    // copies plafonné à 3 : le séparateur ✂ apparaît au plus 2 fois
    expect(substr_count($response->getContent(), '✂'))->toBeLessThanOrEqual(2);
});
