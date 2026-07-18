<?php

use App\Models\ApprovalStep;
use App\Models\ApprovalWorkflow;
use App\Models\Company;
use App\Models\Document;
use App\Models\License;
use App\Models\Plan;
use App\Models\User;
use App\Services\ApprovalService;
use App\Services\LicenseService;
use Illuminate\Support\Str;

// ─── Helpers ────────────────────────────────────────────────────────────────

function createApprovalBusinessLicense(User $user): License
{
    seedPlans();
    $plan = Plan::where('code', 'business')->firstOrFail();

    return License::create([
        'user_id'           => $user->id,
        'plan_id'           => $plan->id,
        'license_key'       => app(LicenseService::class)->generateKey(),
        'type'              => 'paid',
        'status'            => 'active',
        'starts_at'         => now(),
        'ends_at'           => now()->addYear(),
        'limits'            => $plan->limits,
        'activation_source' => 'manual',
    ]);
}

function createApprovalDocument(Company $company, array $attributes = []): Document
{
    return Document::create([
        'company_id'      => $company->id,
        'type'            => 'invoice',
        'number'          => 'FAC-'.strtoupper(Str::random(8)),
        'status'          => 'draft',
        'issue_date'      => now()->toDateString(),
        'currency'        => 'XOF',
        'subtotal'        => 100000,
        'discount_amount' => 0,
        'tax_amount'      => 18000,
        'total'           => 118000,
        'amount_paid'     => 0,
        ...$attributes,
    ]);
}

function createWorkflow(Company $company, array $approverIds, array $attributes = []): ApprovalWorkflow
{
    return ApprovalWorkflow::create([
        'company_id'     => $company->id,
        'name'           => 'Workflow Test',
        'document_types' => ['invoice'],
        'approvers'      => $approverIds,
        'steps_count'    => count($approverIds),
        'is_active'      => true,
        ...$attributes,
    ]);
}

// ─── Tests ──────────────────────────────────────────────────────────────────

it('creates an approval workflow', function () {
    $user = createUserWithCompany();
    $company = $user->currentCompany;
    createApprovalBusinessLicense($user);

    $approver1 = User::factory()->create();
    $approver2 = User::factory()->create();

    $this->actingAs($user)
        ->post(route('approval.workflows.store'), [
            'name'           => 'Circuit Factures',
            'document_types' => ['invoice', 'quote'],
            'approvers'      => [$approver1->id, $approver2->id],
        ])
        ->assertRedirect();

    expect(ApprovalWorkflow::where('company_id', $company->id)->count())->toBe(1);
    $wf = ApprovalWorkflow::where('company_id', $company->id)->first();
    expect($wf->name)->toBe('Circuit Factures');
    expect($wf->steps_count)->toBe(2);
});

it('submits document for approval and creates steps', function () {
    $user = createUserWithCompany();
    $company = $user->currentCompany;
    createApprovalBusinessLicense($user);

    $approver = User::factory()->create();
    $workflow = createWorkflow($company, [$approver->id]);
    $document = createApprovalDocument($company);

    app(ApprovalService::class)->submitForApproval($document, $workflow, $user);

    $document->refresh();
    expect($document->approval_status)->toBe('pending_approval');
    expect($document->approval_workflow_id)->toBe($workflow->id);
    expect(ApprovalStep::where('document_id', $document->id)->count())->toBe(1);
});

it('approves a step and moves to next step', function () {
    $user = createUserWithCompany();
    $company = $user->currentCompany;

    $approver1 = User::factory()->create();
    $approver2 = User::factory()->create();
    $workflow = createWorkflow($company, [$approver1->id, $approver2->id]);
    $document = createApprovalDocument($company);

    $service = app(ApprovalService::class);
    $service->submitForApproval($document, $workflow, $user);

    $step1 = ApprovalStep::where('document_id', $document->id)
        ->where('step_number', 1)->first();

    $service->approve($step1, $approver1, 'Bon pour accord');

    $step1->refresh();
    $document->refresh();

    expect($step1->status)->toBe('approved');
    expect($document->approval_status)->toBe('pending_approval'); // still waiting step 2
});

it('final approval sets document status to approved', function () {
    $user = createUserWithCompany();
    $company = $user->currentCompany;

    $approver = User::factory()->create();
    $workflow = createWorkflow($company, [$approver->id]);
    $document = createApprovalDocument($company);

    $service = app(ApprovalService::class);
    $service->submitForApproval($document, $workflow, $user);

    $step = ApprovalStep::where('document_id', $document->id)->first();
    $service->approve($step, $approver, 'OK');

    $document->refresh();
    expect($document->approval_status)->toBe('approved');
});

it('rejection sets document status to rejected', function () {
    $user = createUserWithCompany();
    $company = $user->currentCompany;

    $approver = User::factory()->create();
    $workflow = createWorkflow($company, [$approver->id]);
    $document = createApprovalDocument($company);

    $service = app(ApprovalService::class);
    $service->submitForApproval($document, $workflow, $user);

    $step = ApprovalStep::where('document_id', $document->id)->first();
    $service->reject($step, $approver, 'Montant incorrect');

    $step->refresh();
    $document->refresh();
    expect($step->status)->toBe('rejected');
    expect($step->comment)->toBe('Montant incorrect');
    expect($document->approval_status)->toBe('rejected');
});

it('delegates step to another user', function () {
    $user = createUserWithCompany();
    $company = $user->currentCompany;

    $approver1 = User::factory()->create();
    $approver2 = User::factory()->create();
    $workflow = createWorkflow($company, [$approver1->id]);
    $document = createApprovalDocument($company);

    $service = app(ApprovalService::class);
    $service->submitForApproval($document, $workflow, $user);

    $step = ApprovalStep::where('document_id', $document->id)->first();
    $service->delegate($step, $approver1, $approver2);

    $step->refresh();
    expect($step->status)->toBe('delegated');
    expect($step->delegated_to_id)->toBe($approver2->id);

    // New step created for approver2
    expect(ApprovalStep::where('document_id', $document->id)
        ->where('approver_id', $approver2->id)
        ->where('status', 'pending')
        ->exists())->toBeTrue();
});

it('returns pending steps for current user', function () {
    $user = createUserWithCompany();
    $company = $user->currentCompany;

    $approver = User::factory()->create();
    $workflow = createWorkflow($company, [$approver->id]);
    $document = createApprovalDocument($company);

    $service = app(ApprovalService::class);
    $service->submitForApproval($document, $workflow, $user);

    $pending = $service->pendingForUser($approver);
    expect($pending)->toHaveCount(1);
    expect($pending->first()->approver_id)->toBe($approver->id);
});

it('returns approval history for a document', function () {
    $user = createUserWithCompany();
    createApprovalBusinessLicense($user);
    $company = $user->currentCompany;

    $approver = User::factory()->create();
    $workflow = createWorkflow($company, [$approver->id]);
    $document = createApprovalDocument($company);

    app(ApprovalService::class)->submitForApproval($document, $workflow, $user);

    $this->actingAs($user)
        ->getJson(route('approval.history', $document->id))
        ->assertOk()
        ->assertJsonFragment(['document_id' => $document->id]);
});

it('requires business plan', function () {
    $user = createUserWithCompanyAndTrial(); // PRO trial

    $this->actingAs($user)
        ->post(route('approval.workflows.store'), [
            'name'           => 'Test',
            'document_types' => ['invoice'],
            'approvers'      => [1],
        ])
        ->assertForbidden();
});

it('isolates workflows between companies', function () {
    $user1 = createUserWithCompany();
    $company1 = $user1->currentCompany;
    createApprovalBusinessLicense($user1);

    $user2 = createUserWithCompany();
    $company2 = $user2->currentCompany;
    createApprovalBusinessLicense($user2);

    $approver = User::factory()->create();

    // Workflow pour company1
    createWorkflow($company1, [$approver->id]);

    // user2 soumet sur le workflow de company1 → doit être refusé
    $workflow = ApprovalWorkflow::where('company_id', $company1->id)->first();
    $document = createApprovalDocument($company2);

    $this->actingAs($user2)
        ->post(route('approval.submit', $document->id), [
            'workflow_id' => $workflow->id,
        ])
        ->assertForbidden();
});
