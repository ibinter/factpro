<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\CouponRedemption;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Console Superadmin — gestion des coupons & réductions (cahier IBIG §22.2).
 */
class CouponAdminController extends Controller
{
    public function index(Request $request): Response
    {
        $coupons = Coupon::withCount('redemptions')
            ->orderByDesc('created_at')
            ->paginate(15);

        $stats = [
            'active' => Coupon::where('is_active', true)->count(),
            'total' => Coupon::count(),
            'redemptions' => (int) CouponRedemption::count(),
            'discounted' => (float) CouponRedemption::sum('amount_discounted'),
        ];

        return Inertia::render('Admin/Coupons', [
            'coupons' => $coupons,
            'stats' => $stats,
            'planCodes' => ['starter', 'pro', 'business', 'enterprise'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        Coupon::create($data);

        return redirect()->route('admin.coupons.index')->with('success', "Coupon « {$data['code']} » créé.");
    }

    public function update(Request $request, Coupon $coupon): RedirectResponse
    {
        $data = $this->validated($request, $coupon);

        $coupon->update($data);

        return redirect()->route('admin.coupons.index')->with('success', "Coupon « {$coupon->code} » mis à jour.");
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        $coupon->delete();

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon supprimé.');
    }

    public function toggle(Coupon $coupon): RedirectResponse
    {
        $coupon->update(['is_active' => ! $coupon->is_active]);

        return redirect()->route('admin.coupons.index')->with('success', $coupon->is_active ? 'Coupon activé.' : 'Coupon désactivé.');
    }

    /** Valide et normalise les données d'un coupon (création ou édition). */
    private function validated(Request $request, ?Coupon $coupon = null): array
    {
        $isPercent = $request->input('type') === 'percent';

        $data = $request->validate([
            'code' => [
                'required', 'string', 'max:50',
                Rule::unique('coupons', 'code')->ignore($coupon?->id),
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'type' => ['required', Rule::in(['percent', 'fixed'])],
            'value' => ['required', 'numeric', 'min:0', ...($isPercent ? ['max:100'] : [])],
            'applies_to' => ['nullable', 'string', 'max:30'],
            'plan_code' => ['nullable', Rule::in(['starter', 'pro', 'business', 'enterprise'])],
            'max_redemptions' => ['nullable', 'integer', 'min:1'],
            'per_user_limit' => ['nullable', 'integer', 'min:1', 'max:255'],
            'min_amount' => ['nullable', 'numeric', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['boolean'],
        ]);

        $data['code'] = strtoupper(trim($data['code']));
        $data['applies_to'] = $data['applies_to'] ?? 'subscription';
        $data['per_user_limit'] = $data['per_user_limit'] ?? 1;

        return $data;
    }
}
