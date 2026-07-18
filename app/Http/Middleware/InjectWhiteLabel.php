<?php

namespace App\Http\Middleware;

use App\Models\WhiteLabelConfig;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InjectWhiteLabel
{
    public function handle(Request $request, Closure $next): Response
    {
        $config = WhiteLabelConfig::forRequest($request);
        if ($config) {
            \Inertia\Inertia::share('whiteLabel', [
                'app_name'        => $config->app_name,
                'primary_color'   => $config->primary_color,
                'secondary_color' => $config->secondary_color,
                'accent_color'    => $config->accent_color,
                'logo_url'        => $config->logo_url,
                'footer_text'     => $config->footer_text,
            ]);
            config(['app.name' => $config->app_name]);
        }

        return $next($request);
    }
}
