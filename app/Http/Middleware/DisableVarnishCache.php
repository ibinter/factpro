<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Empêche Varnish/proxies de cacher les réponses des utilisateurs authentifiés.
 * Sans ça, Inertia.js ne reçoit pas les bonnes réponses après POST et la page
 * ne se recharge pas (action exécutée mais UI figée).
 */
class DisableVarnishCache
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Vary', 'Cookie, X-Inertia');
        $response->headers->set('Surrogate-Control', 'no-store');

        return $response;
    }
}
