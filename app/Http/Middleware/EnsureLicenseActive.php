<?php

namespace App\Http\Middleware;

use App\Services\LicenseService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bloque l'accès aux fonctionnalités métier si aucune licence utilisable.
 * Les données sont conservées ; l'utilisateur est redirigé vers les forfaits (script §13).
 */
class EnsureLicenseActive
{
    public function __construct(private LicenseService $licenses)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Les pages métier nécessitent une société courante.
        // Un superadmin sans société est redirigé vers sa console.
        if ($user && ! $user->current_company_id) {
            if ($user->is_superadmin) {
                return redirect()->route('admin.payments');
            }

            return redirect()->route('profile.edit')
                ->with('error', 'Aucune société associée à votre compte. Contactez le support.');
        }

        if ($user && ! $user->is_superadmin && ! $this->licenses->isActive($user)) {
            return redirect()->route('billing.plans')
                ->with('error', 'Votre essai a expiré — vos données sont conservées. Choisissez un forfait pour continuer.');
        }

        return $next($request);
    }
}
