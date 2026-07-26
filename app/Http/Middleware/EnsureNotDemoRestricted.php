<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotDemoRestricted
{
    /**
     * Patterns d'URL bloqués pour les comptes démo (méthodes mutantes).
     */
    private const BLOCKED_PATTERNS = [
        '#^/([\w-]+/)?export#',
        '#^/documents/\d+/send#',
        '#^/reminders/#',
        '#^/settings/#',
        '#^/api/#',
        '#^/webhooks/#',
    ];

    /**
     * Routes de création soumises à des limites.
     * [pattern => [model, limit]]
     */
    private const CREATION_LIMITS = [
        '#^/documents$#'  => ['model' => \App\Models\Document::class, 'limit' => 3, 'label' => 'documents'],
        '#^/customers$#'  => ['model' => \App\Models\Customer::class, 'limit' => 5, 'label' => 'clients'],
    ];

    public function handle(Request $request, \Closure $next): Response
    {
        $user = Auth::user();

        if (! $user || ! $user->isDemoAccount()) {
            return $next($request);
        }

        $path = '/' . ltrim($request->path(), '/');

        // Bloquer les routes sensibles sur les méthodes mutantes
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            foreach (self::BLOCKED_PATTERNS as $pattern) {
                if (preg_match($pattern, $path)) {
                    return $this->demoBlockedResponse($request, 'Action indisponible en mode démo.');
                }
            }

            // Vérifier les limites de création
            if ($request->isMethod('POST')) {
                foreach (self::CREATION_LIMITS as $pattern => $config) {
                    if (preg_match($pattern, $path)) {
                        $company = $user->currentCompany;
                        if ($company) {
                            $count = $config['model']::where('company_id', $company->id)->count();
                            if ($count >= $config['limit']) {
                                return $this->demoBlockedResponse(
                                    $request,
                                    "Limite démo atteinte : maximum {$config['limit']} {$config['label']} autorisés."
                                );
                            }
                        }
                        break;
                    }
                }
            }
        }

        // Bloquer la modification de l'email sur /profile
        if ($request->isMethod('PUT') || $request->isMethod('PATCH')) {
            if (preg_match('#^/profile$#', $path) && $request->has('email') && $request->input('email') !== $user->email) {
                return $this->demoBlockedResponse($request, 'Modification de l\'email indisponible en mode démo.');
            }
        }

        return $next($request);
    }

    private function demoBlockedResponse(Request $request, string $message): Response
    {
        if ($request->header('X-Inertia')) {
            return back()->with('error', $message);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 403);
        }

        return back()->with('error', $message);
    }
}
