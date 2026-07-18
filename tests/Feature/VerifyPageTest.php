<?php

use App\Services\DocumentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->user = createUserWithCompanyAndTrial();

    $this->document = app(DocumentService::class)->create(
        $this->user->currentCompany,
        $this->user,
        ['type' => 'invoice', 'issue_date' => now()->toDateString(), 'currency' => 'XOF'],
        [['description' => 'Prestation', 'quantity' => 1, 'unit_price' => 10000, 'tax_rate' => 18]],
    );
    app(DocumentService::class)->finalize($this->document);
});

it('publicly verifies a sealed document as authentic', function () {
    // Route publique — aucun utilisateur connecté
    $this->get('/verify/'.$this->document->uuid)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Verify')
            ->where('result.found', true)
            ->where('result.authentic', true)
            ->where('result.number', $this->document->number)
            ->where('result.issuer', $this->user->currentCompany->name)
        );
});

it('flags a document as not authentic after its total is tampered with in database', function () {
    DB::table('documents')->where('id', $this->document->id)->update(['total' => 999999]);

    $this->get('/verify/'.$this->document->uuid)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Verify')
            ->where('result.found', true)
            ->where('result.authentic', false)
        );
});

it('reports found=false for an unknown uuid', function () {
    $this->get('/verify/'.Str::uuid())
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Verify')
            ->where('result.found', false)
        );
});

it('does not mark an unsealed (draft) document as authentic', function () {
    $draft = app(DocumentService::class)->create(
        $this->user->currentCompany,
        $this->user,
        ['type' => 'invoice', 'issue_date' => now()->toDateString(), 'currency' => 'XOF'],
        [['description' => 'Brouillon', 'quantity' => 1, 'unit_price' => 100]],
    );

    $this->get('/verify/'.$draft->uuid)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('result.found', true)
            ->where('result.authentic', false)
        );
});
