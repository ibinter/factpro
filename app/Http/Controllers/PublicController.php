<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Pages publiques marketing (cahier §1 vision, §3 fonctionnalités, §22 tarification).
 * Aucune authentification requise.
 */
class PublicController extends Controller
{
    /** Page tarifs détaillée (Inertia) — comparatif complet des forfaits. */
    public function pricing(Request $request): Response
    {
        return Inertia::render('Public/Pricing', [
            'plans' => $this->formattedPlans(),
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
        ]);
    }

    /** JSON des plans actifs (consommé par la landing Welcome.vue). */
    public function plansJson(Request $request): JsonResponse
    {
        return response()->json([
            'plans' => $this->formattedPlans(),
        ]);
    }

    /**
     * Formate les forfaits actifs avec conversions EUR/USD et remise annuelle (cahier §22.2).
     *
     * @return array<int, array<string, mixed>>
     */
    private function formattedPlans(): array
    {
        $rates = config('factpro.exchange_rates_xof', ['EUR' => 655.957, 'USD' => 590.0]);
        $eurRate = $rates['EUR'] ?? 655.957;
        $usdRate = $rates['USD'] ?? 590.0;

        return Plan::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function (Plan $plan) use ($eurRate, $usdRate) {
                $monthly = (float) $plan->price_monthly;

                return [
                    'code' => $plan->code,
                    'name' => $plan->name,
                    'short_description' => $plan->short_description,
                    'price_monthly' => $monthly,
                    // Remise annuelle 20 % (cahier §22.2) : 12 mois × 0,8.
                    'price_yearly' => round($monthly * 12 * 0.8, 2),
                    'eur' => round($monthly / $eurRate, 2),
                    'usd' => round($monthly / $usdRate, 2),
                    'currency' => $plan->currency,
                    'limits' => $plan->limits ?? [],
                    'features' => $plan->features ?? [],
                    'highlight' => $plan->code === 'pro',
                ];
            })
            ->values()
            ->all();
    }
}
