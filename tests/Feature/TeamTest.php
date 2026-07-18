<?php

use App\Mail\TeamInvitationMail;
use App\Models\Company;
use App\Models\License;
use App\Models\Plan;
use App\Models\TeamInvitation;
use App\Models\User;
use App\Services\LicenseService;
use Illuminate\Support\Facades\Mail;

/** Attache un utilisateur (nouveau) à une société avec un rôle donné. */
function attachTeamMember(Company $company, string $role = 'member', array $attributes = []): User
{
    $user = User::factory()->create($attributes);
    $company->users()->attach($user->id, ['role' => $role]);
    $user->forceFill(['current_company_id' => $company->id])->save();

    return $user->fresh();
}

/** Donne à un utilisateur une licence active d'un plan précis (starter/pro/business/enterprise). */
function giveTeamLicense(User $user, string $planCode): void
{
    seedPlans();
    $plan = Plan::where('code', $planCode)->firstOrFail();

    License::create([
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

it('permet au propriétaire d\'inviter et envoie l\'email', function () {
    Mail::fake();
    $owner = createUserWithCompanyAndTrial(); // plan pro : 3 sièges

    $response = $this->actingAs($owner)->post(route('team.invite'), [
        'email' => 'nouveau@example.com',
        'role' => 'member',
    ]);

    $response->assertSessionHas('success');
    expect(TeamInvitation::where('email', 'nouveau@example.com')->pending()->exists())->toBeTrue();
    Mail::assertSent(TeamInvitationMail::class);
});

it('refuse à un simple membre d\'inviter (403)', function () {
    $owner = createUserWithCompanyAndTrial();
    $company = $owner->currentCompany;
    $member = attachTeamMember($company, 'member');

    $this->actingAs($member)->post(route('team.invite'), [
        'email' => 'x@example.com',
        'role' => 'member',
    ])->assertForbidden();

    expect(TeamInvitation::count())->toBe(0);
});

it('applique la limite de sièges du forfait PRO (3 sièges)', function () {
    Mail::fake();
    $owner = createUserWithCompanyAndTrial(); // pro = 3
    $company = $owner->currentCompany;
    attachTeamMember($company, 'member'); // 2e siège
    attachTeamMember($company, 'cashier'); // 3e siège → plein

    $response = $this->actingAs($owner)->post(route('team.invite'), [
        'email' => 'quatrieme@example.com',
        'role' => 'member',
    ]);

    $response->assertSessionHas('error');
    expect(TeamInvitation::count())->toBe(0);
    Mail::assertNothingSent();
});

it('autorise un nombre illimité d\'invitations en forfait enterprise', function () {
    Mail::fake();
    $owner = createUserWithCompany();
    giveTeamLicense($owner, 'enterprise'); // users = unlimited
    $company = $owner->currentCompany;
    attachTeamMember($company, 'member');
    attachTeamMember($company, 'member');
    attachTeamMember($company, 'member');

    $this->actingAs($owner)->post(route('team.invite'), [
        'email' => 'illimite@example.com',
        'role' => 'admin',
    ])->assertSessionHas('success');

    expect(TeamInvitation::where('email', 'illimite@example.com')->exists())->toBeTrue();
});

it('rattache un utilisateur connecté qui accepte une invitation', function () {
    $owner = createUserWithCompanyAndTrial();
    $company = $owner->currentCompany;

    $invitation = TeamInvitation::create([
        'company_id' => $company->id,
        'email' => 'joiner@example.com',
        'role' => 'member',
        'token' => 'tok-'.uniqid(),
        'invited_by' => $owner->id,
        'expires_at' => now()->addDays(7),
    ]);

    // Utilisateur invité, connecté, avec sa propre société de départ.
    $joiner = createUserWithCompany(['email' => 'joiner@example.com']);

    $this->actingAs($joiner)
        ->post(route('team.join.accept', $invitation->token))
        ->assertRedirect(route('dashboard'));

    $joiner->refresh();
    $invitation->refresh();

    expect($company->users()->whereKey($joiner->id)->first()?->pivot->role)->toBe('member');
    expect($joiner->current_company_id)->toBe($company->id);
    expect($invitation->accepted_at)->not->toBeNull();
});

it('refuse de modifier le rôle du propriétaire', function () {
    $owner = createUserWithCompanyAndTrial();

    $this->actingAs($owner)
        ->put(route('team.members.role', $owner->id), ['role' => 'member'])
        ->assertForbidden();
});

it('refuse de retirer le propriétaire', function () {
    $owner = createUserWithCompanyAndTrial();

    $this->actingAs($owner)
        ->delete(route('team.members.remove', $owner->id))
        ->assertForbidden();
});

it('retire un membre et bascule sa société active', function () {
    $owner = createUserWithCompanyAndTrial();
    $company = $owner->currentCompany;
    $member = attachTeamMember($company, 'member'); // current_company_id = $company

    $this->actingAs($owner)
        ->delete(route('team.members.remove', $member->id))
        ->assertSessionHas('success');

    $member->refresh();
    expect($company->users()->whereKey($member->id)->exists())->toBeFalse();
    expect($member->current_company_id)->not->toBe($company->id);
});

it('affiche une page invalide pour une invitation expirée', function () {
    $owner = createUserWithCompanyAndTrial();
    $company = $owner->currentCompany;

    $invitation = TeamInvitation::create([
        'company_id' => $company->id,
        'email' => 'late@example.com',
        'role' => 'member',
        'token' => 'expired-token',
        'invited_by' => $owner->id,
        'expires_at' => now()->subDay(),
    ]);

    $this->actingAs($owner)
        ->get(route('team.join', $invitation->token))
        ->assertInertia(fn ($page) => $page->component('Team/Join')->where('valid', false));
});

it('interdit d\'inviter dans une société que l\'on ne gère pas (isolation)', function () {
    $owner = createUserWithCompanyAndTrial();
    $foreignCompany = $owner->currentCompany;

    // Un intrus, propriétaire de sa propre société, force la société courante sur celle d'autrui.
    $intruder = createUserWithCompany();
    $intruder->forceFill(['current_company_id' => $foreignCompany->id])->save();

    $this->actingAs($intruder)->post(route('team.invite'), [
        'email' => 'evil@example.com',
        'role' => 'admin',
    ])->assertForbidden();

    expect(TeamInvitation::count())->toBe(0);
});
