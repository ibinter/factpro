<?php

namespace App\Http\Middleware;

use App\Services\ExchangeRateService;
use App\Services\LicenseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        $manifest = public_path('build/manifest.json');
        return file_exists($manifest) ? md5_file($manifest) : null;
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $license = null;
        $company = null;
        $companies = null;
        $rates = null;

        if ($user) {
            $current = app(LicenseService::class)->currentFor($user);
            if ($current) {
                $license = [
                    'plan' => $current->plan?->name,
                    'plan_code' => $current->plan?->code,
                    'type' => $current->type,
                    'status' => $current->status,
                    'days_remaining' => $current->daysRemaining(),
                    'is_trial' => $current->isTrial(),
                    'is_usable' => $current->isUsable(),
                    'ends_at' => $current->effectiveEndsAt()->toDateString(),
                ];
            }

            $company = $user->currentCompany?->only([
                'id', 'name', 'currency', 'country', 'logo_path', 'default_tax_rate',
            ]);

            // Multi-sociétés : sociétés du user (pivot + possédées, sans doublon)
            $pivoted = $user->companies()->get(['companies.id', 'companies.name', 'companies.logo_path']);
            $owned = $user->ownedCompanies()->get(['id', 'name', 'logo_path']);

            $companies = $pivoted
                ->concat($owned->reject(fn ($c) => $pivoted->contains('id', $c->id)))
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'logo_path' => $c->logo_path,
                    'is_current' => $c->id === $user->current_company_id,
                ])
                ->values();

            // Multi-devises : équivalents EUR/USD de la devise société (cahier §14)
            if ($company) {
                $exchange = app(ExchangeRateService::class);
                $base = $company['currency'] ?? 'XOF';
                $freshness = $exchange->freshness();

                $rates = [
                    'base' => $base,
                    'eur' => $exchange->rate($base, 'EUR'),
                    'usd' => $exchange->rate($base, 'USD'),
                    'updated_at' => $freshness?->format('d/m/Y H:i'),
                ];
            }
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user?->only(['id', 'name', 'email', 'is_superadmin']),
            ],
            'license' => $license,
            'company' => $company,
            'companies' => $companies,
            'rates' => $rates,
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
            'analytics' => [
                'ga4_id'   => config('factpro.analytics.ga4_id', ''),
                'pixel_id' => config('factpro.analytics.pixel_id', ''),
            ],
            'announcements' => \App\Models\Announcement::visible()->orderByDesc('created_at')->get()->map(fn($a) => [
                'id' => $a->id, 'title' => $a->title, 'message' => $a->message,
                'type' => $a->type, 'link_text' => $a->link_text, 'link_url' => $a->link_url,
            ]),
            'whiteLabel' => fn () => null, // Le middleware InjectWhiteLabel override si besoin
            'locale' => fn () => App::getLocale(),
            'translations' => fn () => [
                'ui' => trans('ui'),
                'documents' => trans('documents'),
            ],
        ];
    }
}
