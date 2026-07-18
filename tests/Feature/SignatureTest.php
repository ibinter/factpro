<?php

use App\Models\Document;
use App\Models\PaymentAuditLog;
use App\Services\DocumentIntegrityService;
use App\Services\DocumentService;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Storage::fake(config('factpro.proofs.disk'));

    $this->user = createUserWithCompanyAndTrial();
    $this->company = $this->user->currentCompany;
    $this->customer = createCustomerFor($this->company, [
        'name' => 'Client Signataire',
        'portal_token' => str_repeat('s', 48),
        'portal_enabled' => true,
    ]);
});

/** Crée un devis finalisé + envoyé pour le client de test. */
function createSignatureQuote($company, $user, $customer, string $status = 'sent'): Document
{
    $service = app(DocumentService::class);

    $document = $service->create($company, $user, [
        'type' => 'quote',
        'customer_id' => $customer->id,
        'issue_date' => now()->toDateString(),
        'currency' => 'XOF',
    ], [
        ['description' => 'Prestation', 'quantity' => 1, 'unit_price' => 100000, 'tax_rate' => 18],
    ]);

    $service->finalize($document);
    $document->update(['status' => $status]);

    return $document->fresh();
}

/** Un PNG 1x1 valide encodé en dataURL. */
function createSignatureDataUrl(): string
{
    return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAC0lEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';
}

it('accepts a quote with a signature, stores it privately and audits it', function () {
    $quote = createSignatureQuote($this->company, $this->user, $this->customer);

    $this->post('/portal/'.$this->customer->portal_token.'/documents/'.$quote->uuid.'/decision', [
        'decision' => 'accept',
        'signer_name' => 'Jean Client',
        'signature' => createSignatureDataUrl(),
    ])->assertRedirect()->assertSessionHas('success');

    $fresh = $quote->fresh();

    expect($fresh->status)->toBe('accepted')
        ->and($fresh->signature_path)->not->toBeNull()
        ->and($fresh->signed_by_name)->toBe('Jean Client')
        ->and($fresh->signed_at)->not->toBeNull()
        ->and($fresh->signature_ip)->not->toBeNull();

    Storage::disk(config('factpro.proofs.disk'))->assertExists($fresh->signature_path);

    expect(PaymentAuditLog::where('action', 'quote_signed')
        ->where('entity_type', 'document')
        ->where('entity_id', (string) $quote->id)
        ->exists())->toBeTrue();
});

it('rejects a non-PNG signature payload with 422', function () {
    $quote = createSignatureQuote($this->company, $this->user, $this->customer);

    $this->post('/portal/'.$this->customer->portal_token.'/documents/'.$quote->uuid.'/decision', [
        'decision' => 'accept',
        'signer_name' => 'Jean Client',
        'signature' => 'data:image/jpeg;base64,'.base64_encode('not a real png'),
    ])->assertStatus(422);
});

it('still accepts a quote without any signature (backward compatible)', function () {
    $quote = createSignatureQuote($this->company, $this->user, $this->customer);

    $this->post('/portal/'.$this->customer->portal_token.'/documents/'.$quote->uuid.'/decision', [
        'decision' => 'accept',
    ])->assertRedirect()->assertSessionHas('success');

    $fresh = $quote->fresh();
    expect($fresh->status)->toBe('accepted')
        ->and($fresh->signature_path)->toBeNull();
});

it('streams the signature to the owning company', function () {
    $quote = createSignatureQuote($this->company, $this->user, $this->customer);
    $this->post('/portal/'.$this->customer->portal_token.'/documents/'.$quote->uuid.'/decision', [
        'decision' => 'accept',
        'signer_name' => 'Jean Client',
        'signature' => createSignatureDataUrl(),
    ]);

    $this->actingAs($this->user)
        ->get(route('documents.signature', $quote->id))
        ->assertOk()
        ->assertHeader('Content-Type', 'image/png');
});

it('forbids streaming the signature to another company (404)', function () {
    $quote = createSignatureQuote($this->company, $this->user, $this->customer);
    $this->post('/portal/'.$this->customer->portal_token.'/documents/'.$quote->uuid.'/decision', [
        'decision' => 'accept',
        'signer_name' => 'Jean Client',
        'signature' => createSignatureDataUrl(),
    ]);

    $stranger = createUserWithCompanyAndTrial();

    $this->actingAs($stranger)
        ->get(route('documents.signature', $quote->id))
        ->assertNotFound();
});

it('returns 404 for the signature route when the document is unsigned', function () {
    $quote = createSignatureQuote($this->company, $this->user, $this->customer);

    $this->actingAs($this->user)
        ->get(route('documents.signature', $quote->id))
        ->assertNotFound();
});

it('exposes the signed state on the public verification page', function () {
    $quote = createSignatureQuote($this->company, $this->user, $this->customer);
    $this->post('/portal/'.$this->customer->portal_token.'/documents/'.$quote->uuid.'/decision', [
        'decision' => 'accept',
        'signer_name' => 'Jean Client',
        'signature' => createSignatureDataUrl(),
    ]);

    $this->get('/verify/'.$quote->uuid)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Verify')
            ->where('result.signed', true)
            ->where('result.signed_by', 'Jean Client')
        );
});

it('renders the pdf of a signed document without exception and keeps integrity valid', function () {
    $quote = createSignatureQuote($this->company, $this->user, $this->customer);
    $this->post('/portal/'.$this->customer->portal_token.'/documents/'.$quote->uuid.'/decision', [
        'decision' => 'accept',
        'signer_name' => 'Jean Client',
        'signature' => createSignatureDataUrl(),
    ]);

    // L'intégrité reste vraie : la signature est post-scellement (hors hash).
    expect(app(DocumentIntegrityService::class)->verify($quote->fresh()))->toBeTrue();

    $this->get('/portal/'.$this->customer->portal_token.'/documents/'.$quote->uuid.'/pdf')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');
});
