<?php

use App\Models\Document;
use App\Models\QuoteLink;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use App\Notifications\QuoteLinkActionNotification;
use Illuminate\Support\Str;

/** Crée un devis finalisé rattaché à une société. */
function makeQuote(User $user, array $attrs = []): Document
{
    return Document::create([
        'company_id'   => $user->current_company_id,
        'type'         => 'quote',
        'number'       => 'DEV-'.strtoupper(Str::random(6)),
        'status'       => 'finalized',
        'issue_date'   => now()->toDateString(),
        'currency'     => 'XOF',
        'subtotal'     => 100000,
        'discount_amount' => 0,
        'tax_amount'   => 18000,
        'total'        => 118000,
        'amount_paid'  => 0,
        'finalized_at' => now(),
        ...$attrs,
    ]);
}

/** Crée un QuoteLink actif pour un document. */
function makeLink(Document $document, array $attrs = []): QuoteLink
{
    return QuoteLink::create([
        'document_id' => $document->id,
        'token'       => Str::random(48),
        ...$attrs,
    ]);
}

/* =========================================================================
 * Côté vendeur — génération
 * ====================================================================== */

it('generates a quote link for a finalized quote', function () {
    $user = createUserWithCompanyAndTrial();
    $doc  = makeQuote($user);

    $response = $this->actingAs($user)
        ->postJson("/documents/{$doc->id}/quote-link", [
            'expires_in_days'   => 7,
            'allow_comments'    => true,
            'allow_decline'     => true,
            'require_signature' => true,
        ]);

    $response->assertCreated()->assertJsonStructure(['url', 'token', 'link']);
    expect(QuoteLink::where('document_id', $doc->id)->count())->toBe(1);
});

it('rejects link generation for non-quote documents', function () {
    $user = createUserWithCompanyAndTrial();
    $doc  = makeQuote($user, ['type' => 'invoice', 'number' => 'FAC-'.Str::random(6)]);

    $this->actingAs($user)
        ->postJson("/documents/{$doc->id}/quote-link")
        ->assertStatus(422);
});

it('rejects link generation for draft quotes', function () {
    $user = createUserWithCompanyAndTrial();
    $doc  = makeQuote($user, ['finalized_at' => null, 'status' => 'draft']);

    $this->actingAs($user)
        ->postJson("/documents/{$doc->id}/quote-link")
        ->assertStatus(422);
});

/* =========================================================================
 * Vue publique
 * ====================================================================== */

it('public page shows document without auth', function () {
    $user = createUserWithCompanyAndTrial();
    $doc  = makeQuote($user);
    $link = makeLink($doc);

    $this->get("/q/{$link->token}")
        ->assertOk()
        ->assertInertia(fn ($p) => $p->component('QuoteLinks/Show')
            ->where('requiresPassword', false)
        );
});

it('increments view count on each visit', function () {
    $user = createUserWithCompanyAndTrial();
    $doc  = makeQuote($user);
    $link = makeLink($doc);

    $this->get("/q/{$link->token}");
    $this->get("/q/{$link->token}");

    expect($link->fresh()->views_count)->toBe(2);
});

it('signs a quote link with signature data', function () {
    $user = createUserWithCompanyAndTrial();
    $doc  = makeQuote($user);
    $link = makeLink($doc, ['require_signature' => false]);

    $this->postJson("/q/{$link->token}/sign", [
        'client_name'  => 'Jean Dupont',
        'client_email' => 'jean@test.com',
    ])->assertOk()->assertJson(['success' => true]);

    $fresh = $link->fresh();
    expect($fresh->signed_at)->not->toBeNull();
    expect($fresh->client_name)->toBe('Jean Dupont');
});

it('declines a quote link with reason', function () {
    $user = createUserWithCompanyAndTrial();
    $doc  = makeQuote($user);
    $link = makeLink($doc, ['allow_decline' => true]);

    $this->postJson("/q/{$link->token}/decline", [
        'client_name'    => 'Jean Dupont',
        'decline_reason' => 'Budget insuffisant.',
    ])->assertOk()->assertJson(['success' => true]);

    $fresh = $link->fresh();
    expect($fresh->declined_at)->not->toBeNull();
    expect($fresh->decline_reason)->toBe('Budget insuffisant.');
});

it('expired link returns 410 gone', function () {
    $user = createUserWithCompanyAndTrial();
    $doc  = makeQuote($user);
    $link = makeLink($doc, ['expires_at' => now()->subDay()]);

    $this->get("/q/{$link->token}")->assertStatus(410);
});

it('password protected link requires password', function () {
    $user = createUserWithCompanyAndTrial();
    $doc  = makeQuote($user);
    $link = makeLink($doc, ['password' => Hash::make('secret123')]);

    $this->get("/q/{$link->token}")
        ->assertOk()
        ->assertInertia(fn ($p) => $p->where('requiresPassword', true));
});

it('correct password grants access', function () {
    $user = createUserWithCompanyAndTrial();
    $doc  = makeQuote($user);
    $link = makeLink($doc, ['password' => Hash::make('secret123')]);

    $this->postJson("/q/{$link->token}/password", ['password' => 'secret123'])
        ->assertOk()
        ->assertJson(['success' => true]);
});

it('notifies vendor when quote is signed', function () {
    Notification::fake();

    $user = createUserWithCompanyAndTrial();
    $doc  = makeQuote($user);
    $link = makeLink($doc, ['require_signature' => false]);

    $this->postJson("/q/{$link->token}/sign", [
        'client_name'  => 'Jean Dupont',
        'client_email' => 'jean@test.com',
    ])->assertOk();

    Notification::assertSentTo($user, QuoteLinkActionNotification::class, function ($n) {
        return $n->event === 'signed';
    });
});

it('revokes a quote link', function () {
    $user = createUserWithCompanyAndTrial();
    $doc  = makeQuote($user);
    $link = makeLink($doc);

    $this->actingAs($user)
        ->deleteJson("/quote-links/{$link->id}")
        ->assertOk()
        ->assertJson(['success' => true]);

    expect(QuoteLink::find($link->id))->toBeNull();
});
