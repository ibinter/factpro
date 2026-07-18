<?php

use App\Models\Company;
use App\Models\License;
use App\Models\Plan;
use App\Models\User;
use App\Services\LicenseService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/** Crée une licence ACTIVE (payée) sur un plan donné pour un utilisateur. */
function createActiveLicenseFor(User $user, string $planCode): License
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

it('lists the user companies on the index page', function () {
    $user = createUserWithCompanyAndTrial();

    $this->actingAs($user)->get(route('companies.index'))->assertOk();
});

it('creates a company, attaches the owner pivot and switches to it', function () {
    $user = createUserWithCompany();
    createActiveLicenseFor($user, 'enterprise');

    $response = $this->actingAs($user)->post(route('companies.store'), [
        'name' => 'Deuxième Société',
        'country' => 'CI',
        'currency' => 'XOF',
    ]);

    $company = Company::where('name', 'Deuxième Société')->firstOrFail();

    $response->assertRedirect(route('companies.index'));
    expect($company->owner_id)->toBe($user->id)
        ->and($user->fresh()->current_company_id)->toBe($company->id)
        ->and($company->users()->whereKey($user->id)->first()->pivot->role)->toBe('owner');
});

it('blocks creating a second company on a pro plan (limit 1)', function () {
    $user = createUserWithCompanyAndTrial(); // essai = plan PRO → 1 société max

    $response = $this->actingAs($user)
        ->from(route('companies.index'))
        ->post(route('companies.store'), [
            'name' => 'Société Interdite',
            'country' => 'CI',
            'currency' => 'XOF',
        ]);

    $response->assertRedirect(route('companies.index'));
    $response->assertSessionHas('error');
    expect(Company::where('owner_id', $user->id)->count())->toBe(1)
        ->and(Company::where('name', 'Société Interdite')->exists())->toBeFalse();
});

it('lets an enterprise user create several companies', function () {
    $user = createUserWithCompany();
    createActiveLicenseFor($user, 'enterprise');

    foreach (['Filiale Alpha', 'Filiale Beta', 'Filiale Gamma'] as $name) {
        $this->actingAs($user)->post(route('companies.store'), [
            'name' => $name,
            'country' => 'CI',
            'currency' => 'XOF',
        ])->assertSessionHas('success');
    }

    expect(Company::where('owner_id', $user->id)->count())->toBe(4);
});

it('switches the current company back to another owned company', function () {
    $user = createUserWithCompany();
    createActiveLicenseFor($user, 'business');
    $firstCompanyId = $user->current_company_id;

    $this->actingAs($user)->post(route('companies.store'), [
        'name' => 'Filiale',
        'country' => 'CI',
        'currency' => 'XOF',
    ]);
    expect($user->fresh()->current_company_id)->not->toBe($firstCompanyId);

    $response = $this->actingAs($user)->post(route('companies.switch', $firstCompanyId));

    $response->assertRedirect(route('dashboard'));
    expect($user->fresh()->current_company_id)->toBe($firstCompanyId);
});

it('forbids switching to a company belonging to another user', function () {
    $user = createUserWithCompanyAndTrial();
    $other = createUserWithCompany();

    $this->actingAs($user)
        ->post(route('companies.switch', $other->current_company_id))
        ->assertForbidden();

    expect($user->fresh()->current_company_id)->not->toBe($other->current_company_id);
});

it('lets the owner update the company settings', function () {
    $user = createUserWithCompanyAndTrial();

    $response = $this->actingAs($user)->patch(route('companies.settings.update'), [
        'name' => 'Nouveau Nom SARL',
        'country' => 'CI',
        'currency' => 'XOF',
        'default_tax_rate' => 18,
        'default_template' => 'corporate-01',
        'invoice_footer' => 'Merci de votre confiance.',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $company = $user->currentCompany->fresh();
    expect($company->name)->toBe('Nouveau Nom SARL')
        ->and($company->default_template)->toBe('corporate-01')
        ->and($company->invoice_footer)->toBe('Merci de votre confiance.');
});

it('forbids a non-admin member from updating company settings', function () {
    $owner = createUserWithCompanyAndTrial();
    $company = $owner->currentCompany;

    $member = User::factory()->create(['current_company_id' => $company->id]);
    $company->users()->attach($member->id, ['role' => 'member']);
    app(LicenseService::class)->startTrial($member); // licence pour passer le middleware

    $this->actingAs($member)
        ->patch(route('companies.settings.update'), [
            'name' => 'Nom Piraté',
            'country' => 'CI',
            'currency' => 'XOF',
        ])
        ->assertForbidden();

    expect($company->fresh()->name)->not->toBe('Nom Piraté');
});

it('uploads a company logo and updates logo_path', function () {
    Storage::fake('public');
    $user = createUserWithCompanyAndTrial();

    $response = $this->actingAs($user)->post(route('companies.logo'), [
        'logo' => UploadedFile::fake()->image('logo.png', 200, 200),
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $company = $user->currentCompany->fresh();
    expect($company->logo_path)->not->toBeNull()
        ->and($company->logo_path)->toStartWith('companies/');
    Storage::disk('public')->assertExists($company->logo_path);
});

it('deletes the previous logo file when a new one is uploaded', function () {
    Storage::fake('public');
    $user = createUserWithCompanyAndTrial();

    $this->actingAs($user)->post(route('companies.logo'), [
        'logo' => UploadedFile::fake()->image('old.png'),
    ]);
    $oldPath = $user->currentCompany->fresh()->logo_path;

    $this->actingAs($user)->post(route('companies.logo'), [
        'logo' => UploadedFile::fake()->image('new.png'),
    ]);
    $newPath = $user->currentCompany->fresh()->logo_path;

    expect($newPath)->not->toBe($oldPath);
    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($newPath);
});
