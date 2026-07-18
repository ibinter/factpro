<?php

use App\Models\Document;
use App\Models\DocumentAuditLog;
use App\Services\DocumentService;
use App\Services\GdprService;

beforeEach(function () {
    $this->user = createUserWithCompanyAndTrial();
    $this->company = $this->user->currentCompany;
});

// ─── DocumentAuditLog::record() ──────────────────────────────────────────────

it('records a document audit log entry via static record()', function () {
    $document = app(DocumentService::class)->create(
        $this->company,
        $this->user,
        ['type' => 'invoice', 'issue_date' => now()->toDateString(), 'currency' => 'XOF'],
        [['description' => 'Test', 'quantity' => 1, 'unit_price' => 1000]],
    );

    DocumentAuditLog::record($document, 'test_event', $this->user, ['foo' => 'bar']);

    $log = DocumentAuditLog::where('document_id', $document->id)->latest('id')->first();

    expect($log)->not->toBeNull()
        ->and($log->event)->toBe('test_event')
        ->and($log->user_id)->toBe($this->user->id)
        ->and($log->company_id)->toBe($this->company->id)
        ->and($log->meta)->toBe(['foo' => 'bar']);
});

it('records an audit log with null user and null meta when not specified', function () {
    $document = app(DocumentService::class)->create(
        $this->company,
        $this->user,
        ['type' => 'invoice', 'issue_date' => now()->toDateString(), 'currency' => 'XOF'],
        [['description' => 'Test', 'quantity' => 1, 'unit_price' => 1000]],
    );

    DocumentAuditLog::record($document, 'system_event');

    $log = DocumentAuditLog::where('document_id', $document->id)->latest('id')->first();

    expect($log->user_id)->toBeNull()
        ->and($log->meta)->toBeNull();
});

// ─── HTTP — RGPD routes ───────────────────────────────────────────────────────

it('returns 200 on GET /account/data for authenticated user', function () {
    $this->actingAs($this->user)
        ->get(route('gdpr.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Account/Data')
            ->has('user.name')
            ->has('user.email')
        );
});

it('redirects guest from GET /account/data', function () {
    $this->get(route('gdpr.index'))
        ->assertRedirect(route('login'));
});

it('returns a JSON export for authenticated user via GET /account/data/export', function () {
    $response = $this->actingAs($this->user)
        ->get(route('gdpr.export'));

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/json')
        ->assertJsonStructure(['exported_at', 'user', 'companies', 'audit_logs']);

    $data = $response->json();
    expect($data['user']['email'])->toBe($this->user->email);
});

it('requires a valid current_password to delete account', function () {
    $this->actingAs($this->user)
        ->delete(route('gdpr.destroy'), ['password' => 'wrong-password'])
        ->assertSessionHasErrors('password');
});

it('deletes the account when the correct password is supplied', function () {
    $userId = $this->user->id;

    $this->actingAs($this->user)
        ->delete(route('gdpr.destroy'), ['password' => 'password'])
        ->assertRedirect(route('login'));

    expect(\App\Models\User::find($userId))->toBeNull();
});

it('returns 200 on GET /account/audit-log for authenticated user', function () {
    $this->actingAs($this->user)
        ->get(route('gdpr.audit-log'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Account/AuditLog'));
});

// ─── GdprService::exportData() ───────────────────────────────────────────────

it('includes audit log entries in the export', function () {
    $document = app(DocumentService::class)->create(
        $this->company,
        $this->user,
        ['type' => 'invoice', 'issue_date' => now()->toDateString(), 'currency' => 'XOF'],
        [['description' => 'Export test', 'quantity' => 1, 'unit_price' => 500]],
    );

    DocumentAuditLog::record($document, 'created', $this->user);

    $export = app(GdprService::class)->exportData($this->user);

    expect($export['audit_logs'])->toHaveCount(1)
        ->and($export['audit_logs'][0]['event'])->toBe('created');
});

// ─── GdprService::auditLogForCompany() ───────────────────────────────────────

it('returns paginated audit logs for the current company only', function () {
    $other = createUserWithCompanyAndTrial();

    $myDoc = app(DocumentService::class)->create(
        $this->company,
        $this->user,
        ['type' => 'invoice', 'issue_date' => now()->toDateString(), 'currency' => 'XOF'],
        [['description' => 'Mine', 'quantity' => 1, 'unit_price' => 1000]],
    );
    $theirDoc = app(DocumentService::class)->create(
        $other->currentCompany,
        $other,
        ['type' => 'invoice', 'issue_date' => now()->toDateString(), 'currency' => 'XOF'],
        [['description' => 'Theirs', 'quantity' => 1, 'unit_price' => 1000]],
    );

    DocumentAuditLog::record($myDoc, 'created', $this->user);
    DocumentAuditLog::record($theirDoc, 'created', $other);

    $logs = app(GdprService::class)->auditLogForCompany($this->user);

    expect($logs->total())->toBe(1)
        ->and($logs->items()[0]->company_id)->toBe($this->company->id);
});

// ─── DocumentController enregistre les audit logs ────────────────────────────

it('creates an audit log when a document is created via POST /documents', function () {
    $customer = createCustomerFor($this->company);

    $this->actingAs($this->user)->post('/documents', [
        'type' => 'invoice',
        'customer_id' => $customer->id,
        'issue_date' => now()->toDateString(),
        'currency' => 'XOF',
        'lines' => [
            ['description' => 'Audit test', 'quantity' => 1, 'unit_price' => 10000, 'tax_rate' => 0],
        ],
    ]);

    $document = Document::where('company_id', $this->company->id)->firstOrFail();

    expect(DocumentAuditLog::where('document_id', $document->id)->where('event', 'created')->count())
        ->toBe(1);
});
