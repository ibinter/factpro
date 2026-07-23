<?php

namespace App\Http\Middleware;

use App\Models\AccessLog;
use App\Models\SecurityPolicy;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSecurityPolicy
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user || !$user->current_company_id) {
            return $next($request);
        }

        $policy = SecurityPolicy::where('company_id', $user->current_company_id)->first();

        if (!$policy) {
            return $next($request);
        }

        // Vérifier whitelist IP
        if (!empty($policy->allowed_ips) && !$policy->isIpAllowed($request->ip())) {
            Auth::logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Accès refusé depuis cette adresse IP.',
            ]);
        }

        // Vérifier expiration mot de passe
        if ($policy->password_expiry_days > 0 && !$request->routeIs('password.expired', 'password.expired.update', 'logout')) {
            $passwordChangedAt = $user->password_changed_at ?? $user->created_at;
            if ($passwordChangedAt && now()->diffInDays($passwordChangedAt) >= $policy->password_expiry_days) {
                return redirect()->route('password.expired');
            }
        }

        // Enregistrer l'accès si activé
        if ($policy->log_all_access && $request->isMethod('GET') && !$request->expectsJson()) {
            AccessLog::record('page_access', true, $request->path());
        }

        return $next($request);
    }
}
