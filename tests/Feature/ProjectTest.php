<?php

use App\Models\Company;
use App\Models\Document;
use App\Models\License;
use App\Models\Plan;
use App\Models\Project;
use App\Models\TimeEntry;
use App\Models\User;
use App\Services\LicenseService;

/** Crée une licence active sur un plan donné (helper local au module projets). */
function createProjectLicenseFor(User $user, string $planCode): License
{
    seedPlans();
    $plan = Plan::where('code', $planCode)->firstOrFail();

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

/** Crée un projet rattaché à une société. */
function createProjectFor(Company $company, array $attributes = []): Project
{
    return Project::create([
        'company_id' => $company->id,
        'name' => 'Projet Test',
        'status' => 'active',
        'currency' => 'XOF',
        'hourly_rate' => 10000,
        ...$attributes,
    ]);
}

/** Crée une entrée de temps sur un projet. */
function createProjectEntryFor(Project $project, User $user, array $attributes = []): TimeEntry
{
    return TimeEntry::create([
        'company_id' => $project->company_id,
        'project_id' => $project->id,
        'user_id' => $user->id,
        'description' => 'Développement module',
        'entry_date' => now()->toDateString(),
        'duration_minutes' => 60,
        'is_billable' => true,
        'is_billed' => false,
        ...$attributes,
    ]);
}

/*
|--------------------------------------------------------------------------
| Gate forfait BUSINESS/ENTERPRISE (cahier §22.1)
|--------------------------------------------------------------------------
*/

it('shows the projects page without access for a PRO (trial) user', function () {
    $user = createUserWithCompanyAndTrial(); // essai = plan PRO

    $this->actingAs($user)
        ->get(route('projects.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Projects/Index')
            ->where('hasAccess', false));
});

it('forbids project creation for a PRO (trial) user', function () {
    $user = createUserWithCompanyAndTrial();

    $this->actingAs($user)
        ->post(route('projects.store'), ['name' => 'Interdit'])
        ->assertForbidden();

    expect(Project::count())->toBe(0);
});

/*
|--------------------------------------------------------------------------
| CRUD projet (forfait BUSINESS)
|--------------------------------------------------------------------------
*/

it('lists projects with access for a BUSINESS user', function () {
    $user = createUserWithCompany();
    createProjectLicenseFor($user, 'business');
    createProjectFor($user->currentCompany);

    $this->actingAs($user)
        ->get(route('projects.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Projects/Index')
            ->where('hasAccess', true)
            ->has('projects', 1)
            ->has('stats'));
});

it('creates, updates and soft deletes a project for a BUSINESS user', function () {
    $user = createUserWithCompany();
    createProjectLicenseFor($user, 'business');
    $customer = createCustomerFor($user->currentCompany);

    // Création
    $this->actingAs($user)
        ->post(route('projects.store'), [
            'name' => 'Refonte site web',
            'customer_id' => $customer->id,
            'hourly_rate' => 15000,
            'budget_hours' => 40,
            'budget_amount' => 600000,
            'status' => 'active',
        ])
        ->assertRedirect();

    $project = Project::firstWhere('name', 'Refonte site web');
    expect($project)->not->toBeNull()
        ->and($project->company_id)->toBe($user->current_company_id)
        ->and((float) $project->hourly_rate)->toBe(15000.0)
        ->and($project->currency)->toBe('XOF');

    // Mise à jour
    $this->actingAs($user)
        ->put(route('projects.update', $project), [
            'name' => 'Refonte site web v2',
            'status' => 'paused',
            'hourly_rate' => 20000,
        ])
        ->assertRedirect();

    $project->refresh();
    expect($project->name)->toBe('Refonte site web v2')
        ->and($project->status)->toBe('paused');

    // Suppression douce
    $this->actingAs($user)
        ->delete(route('projects.destroy', $project))
        ->assertRedirect(route('projects.index'));

    expect(Project::find($project->id))->toBeNull()
        ->and(Project::withTrashed()->find($project->id))->not->toBeNull();
});

it('rejects a customer belonging to another company', function () {
    $user = createUserWithCompany();
    createProjectLicenseFor($user, 'business');

    $other = createUserWithCompany();
    $foreignCustomer = createCustomerFor($other->currentCompany);

    $this->actingAs($user)
        ->post(route('projects.store'), [
            'name' => 'Projet pirate',
            'customer_id' => $foreignCustomer->id,
        ])
        ->assertSessionHasErrors('customer_id');
});

/*
|--------------------------------------------------------------------------
| Saisie des heures
|--------------------------------------------------------------------------
*/

it('creates a time entry with an explicit duration', function () {
    $user = createUserWithCompany();
    createProjectLicenseFor($user, 'business');
    $project = createProjectFor($user->currentCompany);

    $this->actingAs($user)
        ->post(route('projects.entries.store', $project), [
            'description' => 'Réunion de cadrage',
            'entry_date' => '2026-07-15',
            'duration_minutes' => 90,
            'is_billable' => true,
        ])
        ->assertRedirect();

    $entry = TimeEntry::firstWhere('description', 'Réunion de cadrage');
    expect($entry)->not->toBeNull()
        ->and($entry->duration_minutes)->toBe(90)
        ->and($entry->user_id)->toBe($user->id)
        ->and($entry->company_id)->toBe($user->current_company_id)
        // Taux copié depuis le projet au moment de la saisie
        ->and((float) $entry->hourly_rate)->toBe(10000.0)
        ->and($entry->amount)->toBe(15000.0); // 1,5 h × 10 000
});

it('computes the duration from started_at and ended_at', function () {
    $user = createUserWithCompany();
    createProjectLicenseFor($user, 'business');
    $project = createProjectFor($user->currentCompany);

    $this->actingAs($user)
        ->post(route('projects.entries.store', $project), [
            'description' => 'Session chronométrée',
            'entry_date' => '2026-07-15',
            'started_at' => '2026-07-15 09:00:00',
            'ended_at' => '2026-07-15 10:30:00',
        ])
        ->assertRedirect();

    $entry = TimeEntry::firstWhere('description', 'Session chronométrée');
    expect($entry)->not->toBeNull()
        ->and($entry->duration_minutes)->toBe(90);
});

it('shows the project detail page with totals', function () {
    $user = createUserWithCompany();
    createProjectLicenseFor($user, 'business');
    $project = createProjectFor($user->currentCompany, ['budget_hours' => 1]);
    createProjectEntryFor($project, $user, ['duration_minutes' => 90]); // 150 % du budget

    $this->actingAs($user)
        ->get(route('projects.show', $project))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Projects/Show')
            ->where('project.id', $project->id)
            ->has('entries.data', 1)
            ->where('totals.total_minutes', 90)
            ->where('totals.hours_pct', fn ($v) => (float) $v === 150.0)
            ->where('totals.hours_over_budget', true)
            ->where('totals.unbilled_amount', fn ($v) => (float) $v === 15000.0));
});

/*
|--------------------------------------------------------------------------
| Conversion des heures en facture
|--------------------------------------------------------------------------
*/

it('converts selected time entries into an invoice', function () {
    $user = createUserWithCompany();
    createProjectLicenseFor($user, 'business');
    $customer = createCustomerFor($user->currentCompany);
    $user->currentCompany->update(['default_tax_rate' => 18]);

    $project = createProjectFor($user->currentCompany, ['customer_id' => $customer->id]);
    $entryA = createProjectEntryFor($project, $user, [
        'description' => 'Développement API',
        'entry_date' => '2026-07-10',
        'duration_minutes' => 90, // 1,5 h
    ]);
    $entryB = createProjectEntryFor($project, $user, [
        'description' => 'Tests & recette',
        'entry_date' => '2026-07-11',
        'duration_minutes' => 30, // 0,5 h
        'hourly_rate' => 20000,   // taux spécifique à l'entrée
    ]);

    $response = $this->actingAs($user)->post(route('projects.invoice', $project), [
        'entry_ids' => [$entryA->id, $entryB->id],
    ]);

    $document = Document::firstWhere('type', 'invoice');
    expect($document)->not->toBeNull()
        ->and($document->customer_id)->toBe($customer->id)
        ->and($document->currency)->toBe('XOF')
        ->and($document->lines)->toHaveCount(2);

    $response->assertRedirect(route('documents.show', $document));

    // Une ligne par entrée : quantité en heures, unité « heure », taux effectif
    $lineA = $document->lines->firstWhere('description', '10/07/2026 — Développement API');
    $lineB = $document->lines->firstWhere('description', '11/07/2026 — Tests & recette');
    expect($lineA)->not->toBeNull()
        ->and((float) $lineA->quantity)->toBe(1.5)
        ->and($lineA->unit)->toBe('heure')
        ->and((float) $lineA->unit_price)->toBe(10000.0)
        ->and((float) $lineA->tax_rate)->toBe(18.0)
        ->and($lineB)->not->toBeNull()
        ->and((float) $lineB->quantity)->toBe(0.5)
        ->and((float) $lineB->unit_price)->toBe(20000.0);

    // Totaux : 1,5×10 000 + 0,5×20 000 = 25 000 HT ; TVA 18 % = 4 500 ; TTC 29 500
    expect((float) $document->subtotal)->toBe(25000.0)
        ->and((float) $document->tax_amount)->toBe(4500.0)
        ->and((float) $document->total)->toBe(29500.0);

    // Entrées marquées facturées et rattachées au document
    $entryA->refresh();
    $entryB->refresh();
    expect($entryA->is_billed)->toBeTrue()
        ->and($entryA->document_id)->toBe($document->id)
        ->and($entryB->is_billed)->toBeTrue()
        ->and($entryB->document_id)->toBe($document->id);
});

it('refuses to invoice a project without a customer', function () {
    $user = createUserWithCompany();
    createProjectLicenseFor($user, 'business');
    $project = createProjectFor($user->currentCompany); // sans client
    $entry = createProjectEntryFor($project, $user);

    $this->actingAs($user)
        ->post(route('projects.invoice', $project), ['entry_ids' => [$entry->id]])
        ->assertSessionHasErrors('entry_ids');

    expect(Document::count())->toBe(0)
        ->and($entry->refresh()->is_billed)->toBeFalse();
});

it('refuses to invoice an already billed entry', function () {
    $user = createUserWithCompany();
    createProjectLicenseFor($user, 'business');
    $customer = createCustomerFor($user->currentCompany);
    $project = createProjectFor($user->currentCompany, ['customer_id' => $customer->id]);
    $entry = createProjectEntryFor($project, $user, ['is_billed' => true]);

    $this->actingAs($user)
        ->post(route('projects.invoice', $project), ['entry_ids' => [$entry->id]])
        ->assertSessionHasErrors('entry_ids');
});

/*
|--------------------------------------------------------------------------
| Verrouillage des entrées facturées
|--------------------------------------------------------------------------
*/

it('refuses to update a billed time entry', function () {
    $user = createUserWithCompany();
    createProjectLicenseFor($user, 'business');
    $project = createProjectFor($user->currentCompany);
    $entry = createProjectEntryFor($project, $user, ['is_billed' => true]);

    $this->actingAs($user)
        ->put(route('projects.entries.update', $entry), [
            'description' => 'Tentative de modification',
            'entry_date' => now()->toDateString(),
            'duration_minutes' => 999,
        ])
        ->assertSessionHasErrors('entry');

    expect($entry->refresh()->duration_minutes)->toBe(60)
        ->and($entry->description)->toBe('Développement module');
});

it('refuses to delete a billed time entry but deletes an unbilled one', function () {
    $user = createUserWithCompany();
    createProjectLicenseFor($user, 'business');
    $project = createProjectFor($user->currentCompany);
    $billed = createProjectEntryFor($project, $user, ['is_billed' => true]);
    $unbilled = createProjectEntryFor($project, $user, ['description' => 'Non facturée']);

    $this->actingAs($user)
        ->delete(route('projects.entries.destroy', $billed))
        ->assertSessionHasErrors('entry');
    expect(TimeEntry::find($billed->id))->not->toBeNull();

    $this->actingAs($user)
        ->delete(route('projects.entries.destroy', $unbilled))
        ->assertRedirect();
    expect(TimeEntry::find($unbilled->id))->toBeNull();
});

/*
|--------------------------------------------------------------------------
| Isolation multi-sociétés
|--------------------------------------------------------------------------
*/

it('forbids access to a project of another company', function () {
    $user = createUserWithCompany();
    createProjectLicenseFor($user, 'business');

    $other = createUserWithCompany();
    $foreignProject = createProjectFor($other->currentCompany);

    $this->actingAs($user)
        ->get(route('projects.show', $foreignProject))
        ->assertForbidden();

    $this->actingAs($user)
        ->put(route('projects.update', $foreignProject), ['name' => 'Piraté'])
        ->assertForbidden();

    $this->actingAs($user)
        ->post(route('projects.entries.store', $foreignProject), [
            'description' => 'Intrusion',
            'entry_date' => now()->toDateString(),
            'duration_minutes' => 60,
        ])
        ->assertForbidden();

    expect($foreignProject->refresh()->name)->toBe('Projet Test');
});
