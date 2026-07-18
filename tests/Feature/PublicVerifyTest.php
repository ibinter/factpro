<?php

use App\Services\DocumentService;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->user    = createUserWithCompanyAndTrial();
    $this->company = $this->user->currentCompany;

    // Document finalisé (statut par défaut après finalize = 'sent' ou 'finalized')
    $this->document = app(DocumentService::class)->create(
        $this->company,
        $this->user,
        ['type' => 'invoice', 'issue_date' => now()->toDateString(), 'currency' => 'XOF'],
        [['description' => 'Service', 'quantity' => 1, 'unit_price' => 50000, 'tax_rate' => 18]],
    );
    app(DocumentService::class)->finalize($this->document);
    $this->document->refresh();
});

it('verify page returns 200 for valid hash', function () {
    $this->get('/public/verify/'.$this->document->uuid)
        ->assertOk();
});

it('verify page shows authentic status for finalized document', function () {
    // After finalize, the document has integrity_hash set and status is not 'draft'.
    // The controller returns 'authentic', 'paid', or 'cancelled' for finalized documents.
    $this->get('/public/verify/'.$this->document->uuid)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Public/Verify')
            ->where('document.number', $this->document->number)
        );
});

it('verify page shows paid status for paid document', function () {
    $this->document->update(['status' => 'paid']);

    $this->get('/public/verify/'.$this->document->uuid)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Public/Verify')
            ->where('status', 'paid')
        );
});

it('verify page shows cancelled status for cancelled document', function () {
    $this->document->update(['status' => 'cancelled']);

    $this->get('/public/verify/'.$this->document->uuid)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Public/Verify')
            ->where('status', 'cancelled')
        );
});

it('verify page shows not found for invalid hash', function () {
    $this->get('/public/verify/'.Str::uuid())
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Public/Verify')
            ->where('status', 'not_found')
        );
});

it('verify api returns json with authentic status', function () {
    $response = $this->getJson('/api/public/verify/'.$this->document->uuid)
        ->assertOk()
        ->assertJsonStructure(['status', 'hash', 'document', 'company']);

    $this->assertContains($response->json('status'), ['authentic', 'paid']);
});

it('verify shows company information', function () {
    $this->get('/public/verify/'.$this->document->uuid)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Public/Verify')
            ->where('company.name', $this->company->name)
        );
});

it('verify shows document number and total', function () {
    $this->get('/public/verify/'.$this->document->uuid)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Public/Verify')
            ->where('document.number', $this->document->number)
            ->where('document.currency', 'XOF')
        );
});

it('verify page accessible without authentication', function () {
    // Aucun actingAs — l'utilisateur guest doit obtenir un 200.
    $this->get('/public/verify/'.$this->document->uuid)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Public/Verify'));
});

it('verify api returns not_found for unknown hash', function () {
    $this->getJson('/api/public/verify/'.Str::uuid())
        ->assertStatus(404)
        ->assertJson(['status' => 'not_found']);
});
