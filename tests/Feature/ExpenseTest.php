<?php

use App\Models\Company;
use App\Models\Expense;
use App\Models\License;
use App\Models\Plan;
use App\Models\User;
use App\Services\LicenseService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Helpers locaux au module notes de frais (préfixés pour éviter les
| collisions globales Pest).
|--------------------------------------------------------------------------
*/

/** Crée une licence active sur un plan donné. */
function createExpenseLicenseFor(User $user, string $planCode = 'business'): License
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

/** Ajoute un collaborateur (avec licence BUSINESS) à une société existante. */
function createExpenseMemberFor(Company $company, string $role = 'member'): User
{
    $member = User::factory()->create();
    $company->users()->attach($member->id, ['role' => $role]);
    $member->forceFill(['current_company_id' => $company->id])->save();
    createExpenseLicenseFor($member, 'business');

    return $member->fresh();
}

/** Crée une note de frais pour un utilisateur (dans sa société courante). */
function createExpenseFor(User $user, array $attributes = []): Expense
{
    return Expense::create([
        'company_id' => $user->current_company_id,
        'user_id' => $user->id,
        'category' => 'transport',
        'description' => 'Taxi aéroport',
        'amount' => 5000,
        'currency' => 'XOF',
        'expense_date' => now()->toDateString(),
        'status' => 'submitted',
        ...$attributes,
    ]);
}

/*
|--------------------------------------------------------------------------
| Gate BUSINESS/ENTERPRISE
|--------------------------------------------------------------------------
*/

it('shows the expenses page without access for a PRO (trial) user', function () {
    $user = createUserWithCompanyAndTrial(); // essai = plan PRO

    $this->actingAs($user)
        ->get(route('expenses.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Expenses/Index')
            ->where('hasAccess', false));
});

it('forbids expense mutations for a PRO (trial) user', function () {
    $user = createUserWithCompanyAndTrial();

    $this->actingAs($user)
        ->post(route('expenses.store'), [
            'category' => 'transport',
            'description' => 'Taxi',
            'amount' => 2000,
            'expense_date' => now()->toDateString(),
        ])
        ->assertForbidden();
});

/*
|--------------------------------------------------------------------------
| Déclaration & justificatif privé
|--------------------------------------------------------------------------
*/

it('stores an expense with a private receipt and submitted status', function () {
    Storage::fake(config('factpro.proofs.disk'));

    $user = createUserWithCompany();
    createExpenseLicenseFor($user);

    $this->actingAs($user)
        ->post(route('expenses.store'), [
            'category' => 'repas',
            'description' => 'Déjeuner client',
            'amount' => 12500,
            'expense_date' => now()->toDateString(),
            'receipt' => UploadedFile::fake()->create('recu.jpg', 120, 'image/jpeg'),
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $expense = Expense::firstOrFail();

    expect($expense->status)->toBe('submitted')
        ->and($expense->user_id)->toBe($user->id)
        ->and($expense->company_id)->toBe($user->current_company_id)
        ->and($expense->receipt_path)->toStartWith('private/receipts/')
        ->and($expense->receipt_original_name)->toBe('recu.jpg');

    Storage::disk(config('factpro.proofs.disk'))->assertExists($expense->receipt_path);
});

it('rejects an invalid receipt file type', function () {
    Storage::fake(config('factpro.proofs.disk'));

    $user = createUserWithCompany();
    createExpenseLicenseFor($user);

    $this->actingAs($user)
        ->post(route('expenses.store'), [
            'category' => 'autre',
            'description' => 'Fichier interdit',
            'amount' => 1000,
            'expense_date' => now()->toDateString(),
            'receipt' => UploadedFile::fake()->create('script.exe', 10, 'application/x-msdownload'),
        ])
        ->assertSessionHasErrors('receipt');
});

/*
|--------------------------------------------------------------------------
| Périmètre de visibilité
|--------------------------------------------------------------------------
*/

it('shows only the member own expenses while the owner sees everything', function () {
    $owner = createUserWithCompany();
    createExpenseLicenseFor($owner);
    $company = $owner->currentCompany;
    $member = createExpenseMemberFor($company);

    createExpenseFor($owner, ['description' => 'Dépense owner']);
    createExpenseFor($member, ['description' => 'Dépense member']);

    $this->actingAs($member)
        ->get(route('expenses.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Expenses/Index')
            ->where('hasAccess', true)
            ->where('canReview', false)
            ->has('expenses.data', 1)
            ->where('expenses.data.0.description', 'Dépense member'));

    $this->actingAs($owner)
        ->get(route('expenses.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('canReview', true)
            ->has('expenses.data', 2));
});

/*
|--------------------------------------------------------------------------
| Workflow approbation / rejet
|--------------------------------------------------------------------------
*/

it('forbids a simple member from reviewing an expense', function () {
    $owner = createUserWithCompany();
    createExpenseLicenseFor($owner);
    $member = createExpenseMemberFor($owner->currentCompany);
    $expense = createExpenseFor($owner);

    $this->actingAs($member)
        ->post(route('expenses.review', $expense), ['decision' => 'approve'])
        ->assertForbidden();
});

it('lets the owner approve a submitted expense', function () {
    $owner = createUserWithCompany();
    createExpenseLicenseFor($owner);
    $member = createExpenseMemberFor($owner->currentCompany);
    $expense = createExpenseFor($member);

    $this->actingAs($owner)
        ->post(route('expenses.review', $expense), ['decision' => 'approve'])
        ->assertRedirect()
        ->assertSessionHas('success');

    $fresh = $expense->fresh();
    expect($fresh->status)->toBe('approved')
        ->and($fresh->reviewed_by)->toBe($owner->id)
        ->and($fresh->reviewed_at)->not->toBeNull();
});

it('requires a note when rejecting an expense', function () {
    $owner = createUserWithCompany();
    createExpenseLicenseFor($owner);
    $member = createExpenseMemberFor($owner->currentCompany);
    $expense = createExpenseFor($member);

    $this->actingAs($owner)
        ->post(route('expenses.review', $expense), ['decision' => 'reject'])
        ->assertSessionHasErrors('note');

    expect($expense->fresh()->status)->toBe('submitted');

    $this->actingAs($owner)
        ->post(route('expenses.review', $expense), [
            'decision' => 'reject',
            'note' => 'Justificatif illisible',
        ])
        ->assertRedirect();

    expect($expense->fresh()->status)->toBe('rejected')
        ->and($expense->fresh()->review_note)->toBe('Justificatif illisible');
});

it('forbids an admin from reviewing their own expense (role separation)', function () {
    $owner = createUserWithCompany();
    createExpenseLicenseFor($owner);
    $admin = createExpenseMemberFor($owner->currentCompany, 'admin');
    $expense = createExpenseFor($admin);

    $this->actingAs($admin)
        ->post(route('expenses.review', $expense), ['decision' => 'approve'])
        ->assertForbidden();

    // Le propriétaire de la société, lui, peut valider sa propre dépense.
    $ownExpense = createExpenseFor($owner);
    $this->actingAs($owner)
        ->post(route('expenses.review', $ownExpense), ['decision' => 'approve'])
        ->assertRedirect();

    expect($ownExpense->fresh()->status)->toBe('approved');
});

/*
|--------------------------------------------------------------------------
| Modification / suppression
|--------------------------------------------------------------------------
*/

it('refuses updating an approved expense', function () {
    $owner = createUserWithCompany();
    createExpenseLicenseFor($owner);
    $member = createExpenseMemberFor($owner->currentCompany);
    $expense = createExpenseFor($member, [
        'status' => 'approved',
        'reviewed_by' => $owner->id,
        'reviewed_at' => now(),
    ]);

    $this->actingAs($member)
        ->put(route('expenses.update', $expense), [
            'category' => 'repas',
            'description' => 'Tentative de modification',
            'amount' => 99999,
            'expense_date' => now()->toDateString(),
        ])
        ->assertForbidden();

    expect((float) $expense->fresh()->amount)->toBe(5000.0);
});

it('resubmits a rejected expense when updated by its owner', function () {
    $owner = createUserWithCompany();
    createExpenseLicenseFor($owner);
    $member = createExpenseMemberFor($owner->currentCompany);
    $expense = createExpenseFor($member, [
        'status' => 'rejected',
        'reviewed_by' => $owner->id,
        'reviewed_at' => now(),
        'review_note' => 'Montant erroné',
    ]);

    $this->actingAs($member)
        ->put(route('expenses.update', $expense), [
            'category' => 'transport',
            'description' => 'Taxi aéroport (corrigé)',
            'amount' => 4500,
            'expense_date' => now()->toDateString(),
        ])
        ->assertRedirect();

    $fresh = $expense->fresh();
    expect($fresh->status)->toBe('submitted')
        ->and((float) $fresh->amount)->toBe(4500.0)
        ->and($fresh->reviewed_by)->toBeNull()
        ->and($fresh->review_note)->toBeNull();
});

it('forbids updating another user expense', function () {
    $owner = createUserWithCompany();
    createExpenseLicenseFor($owner);
    $member = createExpenseMemberFor($owner->currentCompany);
    $expense = createExpenseFor($member);

    $this->actingAs($owner)
        ->put(route('expenses.update', $expense), [
            'category' => 'autre',
            'description' => 'Pas ma dépense',
            'amount' => 100,
            'expense_date' => now()->toDateString(),
        ])
        ->assertForbidden();
});

/*
|--------------------------------------------------------------------------
| Justificatif privé — contrôle d'accès
|--------------------------------------------------------------------------
*/

it('streams the receipt to its owner and blocks another company', function () {
    Storage::fake(config('factpro.proofs.disk'));

    $user = createUserWithCompany();
    createExpenseLicenseFor($user);

    $path = 'private/receipts/'.Str::random(40).'.jpg';
    Storage::disk(config('factpro.proofs.disk'))->put($path, 'fake-image-content');

    $expense = createExpenseFor($user, [
        'receipt_path' => $path,
        'receipt_original_name' => 'recu.jpg',
        'receipt_mime' => 'image/jpeg',
    ]);

    $this->actingAs($user)
        ->get(route('expenses.receipt', $expense))
        ->assertOk();

    // Un utilisateur BUSINESS d'une AUTRE société n'y accède pas.
    $stranger = createUserWithCompany();
    createExpenseLicenseFor($stranger);

    $this->actingAs($stranger)
        ->get(route('expenses.receipt', $expense))
        ->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Remboursement
|--------------------------------------------------------------------------
*/

it('marks an approved expense as reimbursed', function () {
    $owner = createUserWithCompany();
    createExpenseLicenseFor($owner);
    $member = createExpenseMemberFor($owner->currentCompany);
    $expense = createExpenseFor($member, [
        'status' => 'approved',
        'reviewed_by' => $owner->id,
        'reviewed_at' => now(),
    ]);

    $this->actingAs($owner)
        ->post(route('expenses.reimburse', $expense))
        ->assertRedirect()
        ->assertSessionHas('success');

    $fresh = $expense->fresh();
    expect($fresh->status)->toBe('reimbursed')
        ->and($fresh->reimbursed_at?->toDateString())->toBe(today()->toDateString());
});

it('refuses reimbursing a non-approved expense', function () {
    $owner = createUserWithCompany();
    createExpenseLicenseFor($owner);
    $expense = createExpenseFor($owner); // submitted

    $this->actingAs($owner)
        ->post(route('expenses.reimburse', $expense))
        ->assertForbidden();

    expect($expense->fresh()->status)->toBe('submitted');
});
