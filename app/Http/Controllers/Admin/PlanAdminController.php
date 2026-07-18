<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\Plan;
use App\Models\PaymentAuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Console Superadmin — gestion des forfaits (script §16.4).
 * LIMITS & FEATURES sont en LECTURE SEULE (jamais modifiés ici).
 */
class PlanAdminController extends Controller
{
    public function index(Request $request): Response
    {
        $activeCounts = License::whereIn('status', ['active', 'grace_period'])
            ->selectRaw('plan_id, COUNT(*) as total')
            ->groupBy('plan_id')
            ->pluck('total', 'plan_id');

        $plans = Plan::orderBy('sort_order')->get()->map(fn (Plan $p) => [
            'id' => $p->id,
            'code' => $p->code,
            'name' => $p->name,
            'short_description' => $p->short_description,
            'price_monthly' => (float) $p->price_monthly,
            'promo_price' => $p->promo_price !== null ? (float) $p->promo_price : null,
            'currency' => $p->currency,
            'trial_days' => (int) $p->trial_days,
            'is_active' => (bool) $p->is_active,
            'sort_order' => (int) $p->sort_order,
            'features' => $p->features ?? [],
            'limits' => $p->limits ?? [],
            'active_licenses' => (int) ($activeCounts[$p->id] ?? 0),
        ]);

        return Inertia::render('Admin/Plans', [
            'plans' => $plans,
        ]);
    }

    /** Met à jour les champs tarifaires/commerciaux d'un forfait (limits & features intouchés). */
    public function update(Request $request, Plan $plan): RedirectResponse
    {
        $data = $request->validate([
            'price_monthly' => ['required', 'numeric', 'min:0'],
            'promo_price' => ['nullable', 'numeric', 'min:0'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'trial_days' => ['required', 'integer', 'min:0', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        $fields = ['price_monthly', 'promo_price', 'short_description', 'trial_days', 'is_active'];
        $old = $plan->only($fields);

        $plan->update($data);

        PaymentAuditLog::record(
            'plan_updated',
            'plan',
            (string) $plan->id,
            $old,
            $plan->fresh()->only($fields),
            null,
            $request->user()->id,
        );

        return back()->with('success', "Forfait « {$plan->name} » mis à jour.");
    }
}
