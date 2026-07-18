<?php

// Équipe & rôles par société (cahier IBIG §22.1 multi-utilisateurs, §16 rôles) — agent Équipe.
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TeamInvitationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    // Gestion de l'équipe de la société courante (owner/admin).
    Route::get('/team', [TeamController::class, 'index'])->name('team.index');
    Route::post('/team/invitations', [TeamController::class, 'invite'])->name('team.invite');
    Route::delete('/team/invitations/{invitation}', [TeamController::class, 'cancelInvite'])->name('team.invite.cancel');
    Route::put('/team/members/{user}', [TeamController::class, 'updateRole'])->name('team.members.role');
    Route::delete('/team/members/{user}', [TeamController::class, 'removeMember'])->name('team.members.remove');
});

// Acceptation d'invitation : la page est consultable par un visiteur non connecté
// (elle propose alors de se connecter / s'inscrire) comme par un utilisateur connecté.
Route::get('/team/join/{token}', [TeamInvitationController::class, 'show'])->name('team.join');
Route::post('/team/join/{token}', [TeamInvitationController::class, 'accept'])->name('team.join.accept');
