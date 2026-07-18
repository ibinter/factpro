<?php

use App\Models\Document;
use App\Models\EmailTracking;
use App\Services\EmailTrackingService;
use Illuminate\Support\Str;

// ─── helpers ────────────────────────────────────────────────────────────────

function makeDoc($user): Document
{
    return Document::create([
        'company_id' => $user->current_company_id,
        'type'       => 'invoice',
        'number'     => 'FAC-' . Str::random(6),
        'status'     => 'sent',
        'issue_date' => now()->toDateString(),
        'currency'   => 'XOF',
        'subtotal'   => 100000,
        'tax_amount' => 0,
        'total'      => 100000,
    ]);
}

function makeEmailTracking(Document $doc, string $email = 'client@test.com'): EmailTracking
{
    return EmailTracking::create([
        'document_id'    => $doc->id,
        'company_id'     => $doc->company_id,
        'recipient_email'=> $email,
        'tracking_token' => Str::random(48),
        'sent_at'        => now(),
    ]);
}

// ─── tests ──────────────────────────────────────────────────────────────────

it('creates tracking record on email send', function () {
    $user = createUserWithCompany();
    $doc  = makeDoc($user);

    $service  = app(EmailTrackingService::class);
    $tracking = $service->createTracking($doc, 'client@example.com');

    expect($tracking)->toBeInstanceOf(EmailTracking::class)
        ->and($tracking->document_id)->toBe($doc->id)
        ->and($tracking->company_id)->toBe($user->current_company_id)
        ->and($tracking->recipient_email)->toBe('client@example.com')
        ->and($tracking->tracking_token)->toHaveLength(48)
        ->and($tracking->sent_at)->not->toBeNull();
});

it('open pixel returns 1x1 gif image', function () {
    $user     = createUserWithCompany();
    $doc      = makeDoc($user);
    $tracking = makeEmailTracking($doc);

    $this->get(route('tracking.open', $tracking->tracking_token))
        ->assertStatus(200)
        ->assertHeader('Content-Type', 'image/gif');
});

it('records open event correctly', function () {
    $user     = createUserWithCompany();
    $doc      = makeDoc($user);
    $tracking = makeEmailTracking($doc);

    $this->get(route('tracking.open', $tracking->tracking_token));

    $tracking->refresh();
    expect($tracking->opens_count)->toBe(1)
        ->and($tracking->opened_at)->not->toBeNull()
        ->and($tracking->last_opened_at)->not->toBeNull();
});

it('increments opens count on multiple opens', function () {
    $user     = createUserWithCompany();
    $doc      = makeDoc($user);
    $tracking = makeEmailTracking($doc);

    $this->get(route('tracking.open', $tracking->tracking_token));
    $this->get(route('tracking.open', $tracking->tracking_token));
    $this->get(route('tracking.open', $tracking->tracking_token));

    $tracking->refresh();
    expect($tracking->opens_count)->toBe(3)
        ->and($tracking->opened_at)->not->toBeNull();
});

it('click redirect returns 302 to original url', function () {
    $user     = createUserWithCompany();
    $doc      = makeDoc($user);
    $tracking = makeEmailTracking($doc);

    $originalUrl = 'https://example.com/document.pdf';

    $this->get(route('tracking.click', [
        'token' => $tracking->tracking_token,
        'url'   => urlencode($originalUrl),
    ]))->assertStatus(302)
       ->assertRedirect($originalUrl);
});

it('records click event correctly', function () {
    $user     = createUserWithCompany();
    $doc      = makeDoc($user);
    $tracking = makeEmailTracking($doc);

    $this->get(route('tracking.click', [
        'token' => $tracking->tracking_token,
        'url'   => urlencode('https://example.com/doc.pdf'),
    ]));

    $tracking->refresh();
    expect($tracking->clicks_count)->toBe(1)
        ->and($tracking->clicked_at)->not->toBeNull()
        ->and($tracking->last_clicked_at)->not->toBeNull();
});

it('dashboard returns tracking stats', function () {
    $user = createUserWithCompanyAndTrial();
    $doc  = makeDoc($user);
    makeEmailTracking($doc);

    $this->actingAs($user)
        ->get(route('email-tracking.dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('EmailTracking/Dashboard')
            ->has('stats')
            ->has('trackings')
        );
});

it('document tracking shows sent and opened status', function () {
    $user     = createUserWithCompanyAndTrial();
    $doc      = makeDoc($user);
    $tracking = makeEmailTracking($doc);
    $tracking->update(['opened_at' => now(), 'opens_count' => 1]);

    $response = $this->actingAs($user)
        ->getJson(route('email-tracking.document', $doc->id));

    $response->assertOk();
    $data = $response->json('trackings');
    expect($data)->toHaveCount(1)
        ->and($data[0]['opened_at'])->not->toBeNull();
});

it('isolates tracking between companies', function () {
    $userA = createUserWithCompanyAndTrial();
    $userB = createUserWithCompanyAndTrial();

    $docA = makeDoc($userA);
    $docB = makeDoc($userB);
    makeEmailTracking($docA, 'a@test.com');
    makeEmailTracking($docB, 'b@test.com');

    $this->actingAs($userA)
        ->getJson(route('email-tracking.stats'))
        ->assertOk()
        ->assertJsonPath('total_sent', 1);

    $this->actingAs($userB)
        ->getJson(route('email-tracking.stats'))
        ->assertOk()
        ->assertJsonPath('total_sent', 1);
});

it('does not track if token not found (returns gif anyway)', function () {
    $this->get(route('tracking.open', 'nonexistent-token-that-does-not-exist-xyz'))
        ->assertStatus(200)
        ->assertHeader('Content-Type', 'image/gif');

    expect(EmailTracking::count())->toBe(0);
});
