<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = 'fr';

        if ($request->user() && in_array($request->user()->language, ['fr', 'en', 'ar', 'pt', 'es'])) {
            $locale = $request->user()->language;
        }

        if ($request->session()->has('locale') && in_array($request->session()->get('locale'), ['fr', 'en', 'ar', 'pt', 'es'])) {
            $locale = $request->session()->get('locale');
        }

        App::setLocale($locale);

        return $next($request);
    }
}
