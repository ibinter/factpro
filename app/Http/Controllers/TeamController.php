<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\TeamInvitation;
use App\Models\User;
use App\Mail\TeamInvitationMail;
use App\Services\LicenseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Équipe & rôles par société (cahier IBIG §22.1 multi-utilisateurs, §16 rôles).
 * Seuls le propriétaire et les administrateurs de la société courante gèrent
 * l'équipe ; le nombre de sièges dépend du forfait (LicenseService).
 */
class TeamController extends Controller
{
    private const ASSIGNABLE_ROLES = ['admin', 'member', 'cashier'];

    /** Page « Équipe » : membres, invitations en attente et compteur de sièges. */
    public function index(Request $request, LicenseService $licenses): Response
    {
        $user = $request->user();
        $company = $user->currentCompany;
        abort_unless($company, 404);

        $isManager = $this->canManageTeam($user, $company);

        $members = $company->users()
            ->orderBy('name')
            ->get()
            ->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->pivot->role,
                'is_owner' => $u->id === $company->owner_id,
            ])
            ->sortByDesc('is_owner')
            ->values();

        $invitations = TeamInvitation::where('company_id', $company->id)
            ->pending()
            ->latest()
            ->get()
            ->map(fn (TeamInvitation $i) => [
                'id' => $i->id,
                'email' => $i->email,
                'role' => $i->role,
                'expires_at' => $i->expires_at->toIso8601String(),
            ])
            ->values();

        $seatLimit = $licenses->currentFor($user)?->limit('users');
        $seatsUsed = $members->count() + $invitations->count();

        return Inertia::render('Team/Index', [
            'members' => $members,
            'invitations' => $invitations,
            'seatLimit' => $seatLimit,
            'seatsUsed' => $seatsUsed,
            'canInvite' => $isManager && ($seatLimit === null || $seatsUsed < $seatLimit),
            'isManager' => $isManager,
            'roles' => self::ASSIGNABLE_ROLES,
        ]);
    }

    /** Invite un collaborateur par email (gate manager + limite de sièges). */
    public function invite(Request $request, LicenseService $licenses): RedirectResponse
    {
        $user = $request->user();
        $company = $user->currentCompany;
        abort_unless($company, 404);
        abort_unless($this->canManageTeam($user, $company), 403);

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', Rule::in(self::ASSIGNABLE_ROLES)],
        ]);

        $email = strtolower($data['email']);

        if ($company->users()->whereRaw('LOWER(email) = ?', [$email])->exists()) {
            return back()->withErrors(['email' => 'Cette personne fait déjà partie de l\'équipe.']);
        }

        if (TeamInvitation::where('company_id', $company->id)
            ->whereRaw('LOWER(email) = ?', [$email])
            ->pending()
            ->exists()) {
            return back()->withErrors(['email' => 'Une invitation est déjà en attente pour cet email.']);
        }

        $seatLimit = $licenses->currentFor($user)?->limit('users');
        if ($seatLimit !== null) {
            $seatsUsed = $company->users()->count()
                + TeamInvitation::where('company_id', $company->id)->pending()->count();

            if ($seatsUsed >= $seatLimit) {
                return back()->with('error', "Limite d'utilisateurs atteinte pour votre forfait ({$seatLimit}). Passez au forfait supérieur.");
            }
        }

        $invitation = TeamInvitation::create([
            'company_id' => $company->id,
            'email' => $email,
            'role' => $data['role'],
            'token' => Str::random(48),
            'invited_by' => $user->id,
            'expires_at' => now()->addDays(7),
        ]);

        Mail::to($email)->send(new TeamInvitationMail($invitation));

        return redirect()->route('team.index')->with('success', 'Invitation envoyée à '.$email.'.');
    }

    /** Annule une invitation en attente de la société courante. */
    public function cancelInvite(Request $request, TeamInvitation $invitation): RedirectResponse
    {
        $user = $request->user();
        $company = $user->currentCompany;
        abort_unless($company, 404);
        abort_unless($this->canManageTeam($user, $company), 403);
        abort_unless($invitation->company_id === $company->id, 403);

        $invitation->delete();

        return redirect()->route('team.index')->with('success', 'Invitation annulée.');
    }

    /** Change le rôle d'un membre (jamais le propriétaire). */
    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $manager = $request->user();
        $company = $manager->currentCompany;
        abort_unless($company, 404);
        abort_unless($this->canManageTeam($manager, $company), 403);
        abort_unless($company->users()->whereKey($user->id)->exists(), 404);

        // Le rôle du propriétaire est immuable.
        abort_if($user->id === $company->owner_id, 403, 'Le rôle du propriétaire ne peut pas être modifié.');

        $data = $request->validate([
            'role' => ['required', Rule::in(self::ASSIGNABLE_ROLES)],
        ]);

        $company->users()->updateExistingPivot($user->id, ['role' => $data['role']]);

        return redirect()->route('team.index')->with('success', 'Rôle mis à jour.');
    }

    /** Retire un membre de la société courante (jamais le propriétaire). */
    public function removeMember(Request $request, User $user): RedirectResponse
    {
        $manager = $request->user();
        $company = $manager->currentCompany;
        abort_unless($company, 404);
        abort_unless($this->canManageTeam($manager, $company), 403);
        abort_unless($company->users()->whereKey($user->id)->exists(), 404);

        abort_if($user->id === $company->owner_id, 403, 'Le propriétaire ne peut pas être retiré.');

        $company->users()->detach($user->id);

        // Si la société retirée était la société active du membre, bascule ailleurs.
        if ($user->current_company_id === $company->id) {
            $fallback = $user->companies()->where('companies.id', '!=', $company->id)->first()
                ?? $user->ownedCompanies()->where('id', '!=', $company->id)->first();

            $user->forceFill(['current_company_id' => $fallback?->id])->save();
        }

        return redirect()->route('team.index')->with('success', 'Membre retiré de l\'équipe.');
    }

    /** Le propriétaire (owner_id) ou un rôle pivot owner/admin peut gérer l'équipe. */
    private function canManageTeam(User $user, Company $company): bool
    {
        if ($company->owner_id === $user->id) {
            return true;
        }

        $role = $company->users()->whereKey($user->id)->first()?->pivot?->role;

        return in_array($role, ['owner', 'admin'], true);
    }
}
