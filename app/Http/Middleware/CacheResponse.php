<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CacheResponse
{
    private const TTL = 300; // 5 minutes

    public function handle(Request $request, Closure $next): Response
    {
        // Skip : non-GET, non authentifié, ou paramètre no-cache
        if (
            ! $request->isMethod('GET')
            || ! $request->user()
            || $request->has('no-cache')
            || $request->header('Cache-Control') === 'no-cache'
        ) {
            return $next($request);
        }

        $cacheKey = 'http_response_' . md5(
            $request->user()->id . '|' . $request->fullUrl()
        );

        if (Cache::has($cacheKey)) {
            /** @var array $cached */
            $cached = Cache::get($cacheKey);

            $response = response($cached['content'], $cached['status']);
            foreach ($cached['headers'] as $name => $values) {
                $response->headers->set($name, $values);
            }
            $response->headers->set('X-Cache', 'HIT');
            $response->headers->set('X-Cache-TTL', (string) self::TTL);

            return $response;
        }

        /** @var Response $response */
        $response = $next($request);

        // Ne cache que les réponses 200 OK
        if ($response->getStatusCode() === 200) {
            Cache::put($cacheKey, [
                'content' => $response->getContent(),
                'status'  => $response->getStatusCode(),
                'headers' => $response->headers->all(),
            ], self::TTL);

            $response->headers->set('X-Cache', 'MISS');
            $response->headers->set('X-Cache-TTL', (string) self::TTL);
        }

        return $response;
    }
}
