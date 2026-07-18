<?php

use App\Models\License;
use App\Models\Plan;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\TimeEntry;
use App\Services\LicenseService;
use App\Services\ProjectBillingService;

// ─────────────────────────────────────────────────────────────────────────────
// Helpers locaux
// ─────────────────────────────────────────────────────────────────────────────

/** Crée une licence active BUSINESS pour un utilisateur (milestones). */
function createMilestonesBusinessLicense(\App\Models\User $user): License
{
    seedPlans();
    $plan = Plan::where('code', 'business')->firstOrFail();

    return License::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'license_key' => app(LicenseService::class)->generateKey(),
        'type' => 'paid',
        'status' => 'active',
        'starts_at' => now(),
        'ends_at' => now()->addYear(),
        'limits' => $plan->limits,
        'activation_source' => 'manual',
    ]);
}

/** Crée un user avec société + licence business (milestones). */
function userWithBusiness(): \App\Models\User
{
    $user = createUserWithCompany();
    createMilestonesBusinessLicense($user);
    return $user->fresh();
}

/** Crée un projet sur la société courante d'un user. */
function milestoneProject(\App\Models\User $user, array $attrs = []): Project
{
    return Project::create([
        'company_id' => $user->currentCompany->id,
        'name' => 'Projet Jalons',
        'status' => 'active',
        'currency' => 'XOF',
        'hourly_rate' => 10000,
        ...$attrs,
    ]);
}

/** Crée un jalon sur un projet. */
function createMilestone(Project $project, array $attrs = []): ProjectMilestone
{
    return ProjectMilestone::create([
        'project_id' => $project->id,
        'company_id' => $project->company_id,
        'name' => 'Jalon Test',
        'completion_pct' => 0,
        'status' => 'pending',
        ...$attrs,
    ]);
}

// ─────────────────────────────────────────────────────────────────────────────
// Tests
// ─────────────────────────────────────────────────────────────────────────────

it('creates a project milestone', function () {
    $user = userWithBusiness();
    $project = milestoneProject($user);

    $this->actingAs($user)
        ->post(route('projects.milestones.store', $project), [
            'name' => 'Livraison V1',
            'description' => 'Première livraison',
            'due_date' => '2026-09-01',
            'billing_amount' => 500000,
            'status' => 'pending',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('project_milestones', [
        'project_id' => $project->id,
        'name' => 'Livraison V1',
        'billing_amount' => 500000,
    ]);
});

it('calculates budget status correctly', function () {
    $user = userWithBusiness();
    $project = milestoneProject($user, [
        'budget_hours' => 10,
        'budget_amount' => 1000000,
    ]);

    // 5h de temps enregistrées
    TimeEntry::create([
        'company_id' => $project->company_id,
        'project_id' => $project->id,
        'user_id' => $user->id,
        'description' => 'Dev',
        'entry_date' => now()->toDateString(),
        'duration_minutes' => 300, // 5h
        'is_billable' => true,
        'is_billed' => false,
    ]);

    // Jalon facturé : 200 000 XOF
    createMilestone($project, [
        'status' => 'invoiced',
        'billing_amount' => 200000,
    ]);

    $service = app(ProjectBillingService::class);
    $status = $service->getBudgetStatus($project);

    expect($status['hours_logged'])->toBe(5.0)
        ->and((int) $status['hours_pct'])->toBe(50)
        ->and($status['hours_remaining'])->toBe(5.0)
        ->and((float) $status['amount_billed'])->toBe(200000.0)
        ->and((int) $status['amount_pct'])->toBe(20)
        ->and($status['over_budget'])->toBeFalse();
});

it('detects over budget status', function () {
    $user = userWithBusiness();
    $project = milestoneProject($user, [
        'budget_hours' => 2,
    ]);

    TimeEntry::create([
        'company_id' => $project->company_id,
        'project_id' => $project->id,
        'user_id' => $user->id,
        'description' => 'Travail',
        'entry_date' => now()->toDateString(),
        'duration_minutes' => 180, // 3h > 2h budget
        'is_billable' => true,
        'is_billed' => false,
    ]);

    $status = app(ProjectBillingService::class)->getBudgetStatus($project);

    expect($status['over_budget'])->toBeTrue();
});

it('updates milestone completion percentage', function () {
    $user = userWithBusiness();
    $project = milestoneProject($user);
    $milestone = createMilestone($project, ['completion_pct' => 0]);

    $this->actingAs($user)
        ->put(route('milestones.update', $milestone), [
            'completion_pct' => 75,
            'status' => 'in_progress',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('project_milestones', [
        'id' => $milestone->id,
        'completion_pct' => 75,
        'status' => 'in_progress',
    ]);
});

it('bills a milestone and creates invoice', function () {
    $user = userWithBusiness();
    $company = $user->currentCompany;
    $customer = createCustomerFor($company);
    $project = milestoneProject($user, ['customer_id' => $customer->id]);
    $milestone = createMilestone($project, [
        'status' => 'completed',
        'billing_amount' => 300000,
        'name' => 'Livraison finale',
    ]);

    $this->actingAs($user)
        ->post(route('milestones.bill', $milestone))
        ->assertRedirect();

    $milestone->refresh();

    expect($milestone->status)->toBe('invoiced')
        ->and($milestone->document_id)->not->toBeNull()
        ->and($milestone->invoiced_at)->not->toBeNull();
});

it('billed milestone is linked to document', function () {
    $user = userWithBusiness();
    $company = $user->currentCompany;
    $customer = createCustomerFor($company);
    $project = milestoneProject($user, ['customer_id' => $customer->id]);
    $milestone = createMilestone($project, [
        'status' => 'completed',
        'billing_amount' => 150000,
    ]);

    $service = app(ProjectBillingService::class);
    $document = $service->billMilestone($milestone, $user);

    expect($document->id)->not->toBeNull()
        ->and($document->type)->toBe('deposit_invoice');

    $milestone->refresh();
    expect($milestone->document_id)->toBe($document->id);
});

it('completion pct is average of milestones', function () {
    $user = userWithBusiness();
    $project = milestoneProject($user);

    createMilestone($project, ['completion_pct' => 50]);
    createMilestone($project, ['completion_pct' => 100, 'status' => 'completed']);
    createMilestone($project, ['completion_pct' => 0]);

    $pct = app(ProjectBillingService::class)->getCompletionPct($project);

    // (50 + 100 + 0) / 3 = 50
    expect($pct)->toBe(50);
});

it('budget alert sends when threshold reached', function () {
    $user = userWithBusiness();
    $project = milestoneProject($user, [
        'budget_hours' => 10,
        'alert_threshold_pct' => 80,
    ]);

    // 9h consommées = 90% > seuil 80%
    TimeEntry::create([
        'company_id' => $project->company_id,
        'project_id' => $project->id,
        'user_id' => $user->id,
        'description' => 'Dev',
        'entry_date' => now()->toDateString(),
        'duration_minutes' => 540,
        'is_billable' => true,
        'is_billed' => false,
    ]);

    $service = app(ProjectBillingService::class);
    $triggered = $service->checkBudgetAlert($project);

    expect($triggered)->toBeTrue();

    $project->refresh();
    expect($project->budget_alert_sent_at)->not->toBeNull();

    // 2ème appel ne doit pas redéclencher (déjà marqué)
    $triggeredAgain = $service->checkBudgetAlert($project);
    expect($triggeredAgain)->toBeFalse();
});

it('destroys milestone', function () {
    $user = userWithBusiness();
    $project = milestoneProject($user);
    $milestone = createMilestone($project);

    $this->actingAs($user)
        ->delete(route('milestones.destroy', $milestone))
        ->assertRedirect();

    $this->assertDatabaseMissing('project_milestones', ['id' => $milestone->id]);
});

it('isolates milestones between companies', function () {
    $user1 = userWithBusiness();
    $user2 = userWithBusiness();

    $project1 = milestoneProject($user1);
    $milestone = createMilestone($project1);

    // user2 ne peut pas modifier le jalon de user1
    $this->actingAs($user2)
        ->put(route('milestones.update', $milestone), ['completion_pct' => 50])
        ->assertForbidden();
});
