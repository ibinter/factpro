<?php

namespace App\Http\Middleware;

use App\Services\LicenseService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * API REST publique v1 (cahier §20) — réservée aux forfaits BUSINESS et
 * ENTERPRISE (cahier §22.1). BUSINESS : 1000 requêtes/heure ; ENTERPRISE :
 * illimité. À placer APRÈS auth:sanctum.
 */
class EnsureApiPlanAccess
{
    private const BUSINESS_MAX_ATTEMPTS = 1000;

    private const DECAY_SECONDS = 3600;

    public function __construct(private LicenseService $licenses)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        $license = $this->licenses->currentFor($user);

        if (! $license || ! $license->isUsable()) {
            return response()->json(['message' => 'Licence inactive.'], 403);
        }

        $planCode = $license->plan?->code;

        if (! in_array($planCode, ['business', 'enterprise'], true)) {
            return response()->json([
                'message' => "L'API REST est disponible à partir du forfait BUSINESS.",
            ], 403);
        }

        // ENTERPRISE : aucune limite (cahier §22.1)
        if ($planCode === 'enterprise') {
            return $next($request);
        }

        // BUSINESS : 1000 requêtes / heure
        $key = 'api:'.$user->id;

        if (RateLimiter::tooManyAttempts($key, self::BUSINESS_MAX_ATTEMPTS)) {
            $retryAfter = RateLimiter::availableIn($key);

            return response()->json([
                'message' => 'Quota API dépassé : 1000 requêtes/heure (forfait BUSINESS). Réessayez plus tard.',
            ], 429)->withHeaders([
                'Retry-After' => $retryAfter,
                'X-RateLimit-Limit' => self::BUSINESS_MAX_ATTEMPTS,
                'X-RateLimit-Remaining' => 0,
            ]);
        }

        RateLimiter::hit($key, self::DECAY_SECONDS);

        $response = $next($request);

        $response->headers->set('X-RateLimit-Limit', (string) self::BUSINESS_MAX_ATTEMPTS);
        $response->headers->set(
            'X-RateLimit-Remaining',
            (string) max(0, self::BUSINESS_MAX_ATTEMPTS - RateLimiter::attempts($key))
        );

        return $response;
    }
}
