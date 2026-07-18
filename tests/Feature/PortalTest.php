<?php

use App\Models\Document;
use App\Models\PaymentAuditLog;
use App\Services\DocumentService;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->user = createUserWithCompanyAndTrial();
    $this->company = $this->user->currentCompany;
    $this->customer = createCustomerFor($this->company, [
        'name' => 'Client Portail',
        'portal_token' => str_repeat('a', 48),
        'portal_enabled' => true,
    ]);
});

/** Crée un document pour un client (finalisé + statut au choix). */
function portalDoc($company, $user, $customer, string $type = 'quote', bool $finalized = true, string $status = 'sent'): Document
{
    $service = app(DocumentService::class);

    $document = $service->create($company, $user, [
        'type' => $type,
        'customer_id' => $customer->id,
        'issue_date' => now()->toDateString(),
        'currency' => 'XOF',
    ], [
        ['description' => 'Prestation', 'quantity' => 1, 'unit_price' => 100000, 'tax_rate' => 18],
    ]);

    if ($finalized) {
        $service->finalize($document);
    }

    $document->update(['status' => $status]);

    return $document->fresh();
}

it('returns 404 for an invalid or disabled portal token', function () {
    $this->get('/portal/'.str_repeat('z', 48))->assertNotFound();

    $this->customer->forceFill(['portal_enabled' => false])->save();
    $this->get('/portal/'.$this->customer->portal_token)->assertNotFound();
});

it('shows only the finalized documents of the token owner', function () {
    $visible = portalDoc($this->company, $this->user, $this->customer, 'invoice', true, 'sent');
    portalDoc($this->company, $this->user, $this->customer, 'quote', false, 'draft'); // brouillon non finalisé → caché

    $otherCustomer = createCustomerFor($this->company, ['name' => 'Autre Client']);
    portalDoc($this->company, $this->user, $otherCustomer, 'invoice', true, 'sent'); // autre client → caché

    $this->get('/portal/'.$this->customer->portal_token)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Portal/Index')
            ->where('customer.name', 'Client Portail')
            ->has('documents', 1)
            ->where('documents.0.uuid', $visible->uuid)
            ->where('documents.0.canDecide', false)
            ->where('stats.invoiced', fn ($v) => (float) $v === 118000.0)
        );
});

it('forbids downloading the pdf of another customer document', function () {
    $otherCustomer = createCustomerFor($this->company, ['name' => 'Autre Client']);
    $foreign = portalDoc($this->company, $this->user, $otherCustomer, 'invoice', true, 'sent');

    $this->get('/portal/'.$this->customer->portal_token.'/documents/'.$foreign->uuid.'/pdf')
        ->assertForbidden();
});

it('lets the customer accept a sent quote and records an audit log', function () {
    $quote = portalDoc($this->company, $this->user, $this->customer, 'quote', true, 'sent');

    $response = $this->post('/portal/'.$this->customer->portal_token.'/documents/'.$quote->uuid.'/decision', [
        'decision' => 'accept',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect($quote->fresh()->status)->toBe('accepted');

    $log = PaymentAuditLog::where('action', 'quote_accept')
        ->where('entity_type', 'document')
        ->where('entity_id', (string) $quote->id)
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->old_values)->toBe(['status' => 'sent'])
        ->and($log->new_values)->toBe(['status' => 'accepted']);
});

it('rejects a decision on an invoice (quotes only)', function () {
    $invoice = portalDoc($this->company, $this->user, $this->customer, 'invoice', true, 'sent');

    $this->post('/portal/'.$this->customer->portal_token.'/documents/'.$invoice->uuid.'/decision', [
        'decision' => 'accept',
    ])->assertForbidden();

    expect($invoice->fresh()->status)->toBe('sent');
});

it('forbids generating a portal token for a customer of another company', function () {
    $stranger = createUserWithCompanyAndTrial();

    $this->actingAs($stranger)
        ->post('/customers/'.$this->customer->id.'/portal-token')
        ->assertForbidden();

    expect($this->customer->fresh()->portal_token)->toBe(str_repeat('a', 48));
});

it('generates a portal token for an own customer', function () {
    $customer = createCustomerFor($this->company, ['name' => 'Sans Token']);

    $this->actingAs($this->user)
        ->post('/customers/'.$customer->id.'/portal-token')
        ->assertRedirect()
        ->assertSessionHas('success');

    $fresh = $customer->fresh();
    expect($fresh->portal_token)->toHaveLength(48)
        ->and((bool) $fresh->portal_enabled)->toBeTrue();
});
