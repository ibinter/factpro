<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TrackUtm
{
    public function handle(Request $request, Closure $next)
    {
        foreach (['utm_source', 'utm_medium', 'utm_campaign', 'utm_content'] as $param) {
            if ($request->has($param)) {
                session([$param => $request->get($param)]);
            }
        }
        if ($request->header('referer')) {
            session(['referrer_url' => $request->header('referer')]);
        }

        return $next($request);
    }
}
