<?php

namespace App\Http\Controllers;

use App\Models\TeamInvitation;
use App\Services\LicenseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Acceptation des invitations d'équipe (cahier IBIG §22.1 / §16).
 * La page est consultable connecté ou non : un visiteur est invité à se
 * connecter / s'inscrire, un utilisateur connecté rejoint directement.
 */
class TeamInvitationController extends Controller
{
    private const ROLE_LABELS = [
        'admin' => 'Administrateur',
        'member' => 'Membre',
        'cashier' => 'Caissier',
    ];

    /** Affiche l'invitation (ou une page « invalide/expirée »). */
    public function show(Request $request, string $token): Response
    {
        $invitation = TeamInvitation::where('token', $token)->first();

        if (! $invitation || ! $invitation->isPending()) {
            return Inertia::render('Team/Join', [
                'valid' => false,
                'authenticated' => Auth::check(),
            ]);
        }

        $invitation->loadMissing('company');

        return Inertia::render('Team/Join', [
            'valid' => true,
            'authenticated' => Auth::check(),
            'token' => $invitation->token,
            'company' => $invitation->company->name,
            'role' => $invitation->role,
            'roleLabel' => self::ROLE_LABELS[$invitation->role] ?? $invitation->role,
            'email' => $invitation->email,
        ]);
    }

    /** Accepte l'invitation : rattache l'utilisateur connecté à la société. */
    public function accept(Request $request, string $token, LicenseService $licenses): RedirectResponse
    {
        $invitation = TeamInvitation::where('token', $token)->first();

        if (! $invitation || ! $invitation->isPending()) {
            return redirect()->route('team.join', $token)
                ->with('error', 'Cette invitation est invalide ou a expiré.');
        }

        // Visiteur non connecté : on mémorise l'invitation et on renvoie vers la connexion.
        if (! Auth::check()) {
            $request->session()->put('pending_invitation', $token);

            return redirect()->route('login')
                ->with('status', 'Connectez-vous ou créez un compte pour rejoindre l\'équipe.');
        }

        $user = $request->user();
        $company = $invitation->company;
        $alreadyMember = $company->users()->whereKey($user->id)->exists();

        // Respect de la limite de sièges du forfait (licence du propriétaire).
        if (! $alreadyMember && $company->owner) {
            $seatLimit = $licenses->currentFor($company->owner)?->limit('users');
            if ($seatLimit !== null && $company->users()->count() >= $seatLimit) {
                return redirect()->route('team.join', $token)
                    ->with('error', "Limite d'utilisateurs atteinte pour le forfait de cette société.");
            }
        }

        if (! $alreadyMember) {
            $company->users()->attach($user->id, ['role' => $invitation->role]);
        }

        $invitation->update(['accepted_at' => now()]);

        $user->forceFill(['current_company_id' => $company->id])->save();
        $request->session()->forget('pending_invitation');

        return redirect()->route('dashboard')
            ->with('success', 'Vous avez rejoint '.$company->name.'.');
    }
}
