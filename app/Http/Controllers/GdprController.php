<?php

namespace App\Http\Controllers;

use App\Services\GdprService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;

class GdprController extends Controller
{
    public function __construct(private GdprService $gdpr)
    {
    }

    /**
     * Page "Mes données & RGPD".
     */
    public function index(Request $request): \Inertia\Response
    {
        $user = $request->user();

        return Inertia::render('Account/Data', [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at?->toDateString(),
            ],
        ]);
    }

    /**
     * Exporte les données personnelles en JSON (RGPD Art. 20 — portabilité).
     */
    public function export(Request $request): JsonResponse
    {
        $data = $this->gdpr->exportData($request->user());

        return response()->json($data)
            ->header('Content-Disposition', 'attachment; filename="mes-donnees-factpro.json"');
    }

    /**
     * Supprime le compte (RGPD Art. 17 — droit à l'oubli).
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);

        $user = $request->user();

        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $this->gdpr->deleteAccount($user);

        return redirect()->route('login')->with('success', 'Votre compte a été supprimé. Au revoir.');
    }

    /**
     * Journal d'audit de la société courante (RGPD / traçabilité).
     */
    public function auditLog(Request $request): \Inertia\Response
    {
        $logs = $this->gdpr->auditLogForCompany($request->user());

        return Inertia::render('Account/AuditLog', [
            'logs' => $logs,
        ]);
    }
}
