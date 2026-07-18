<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Validation & application des coupons de réduction (cahier IBIG §22.2).
 */
class CouponService
{
    /**
     * Valide un code promo pour un utilisateur, un forfait et un montant donnés.
     *
     * @return array{valid: bool, coupon: ?Coupon, discount: float, message: string}
     */
    public function validateFor(string $code, User $user, Plan $plan, float $amount): array
    {
        $coupon = Coupon::whereRaw('UPPER(code) = ?', [strtoupper(trim($code))])->first();

        if (! $coupon || ! $coupon->isCurrentlyValid()) {
            return $this->fail('Code promo invalide ou expiré');
        }

        if ($coupon->plan_code !== null && $coupon->plan_code !== $plan->code) {
            return $this->fail("Ce code ne s'applique pas à ce forfait");
        }

        if ($coupon->min_amount !== null && $amount < (float) $coupon->min_amount) {
            return $this->fail('Montant minimum non atteint');
        }

        $userRedemptions = CouponRedemption::where('coupon_id', $coupon->id)
            ->where('user_id', $user->id)
            ->count();

        if ($coupon->per_user_limit !== null && $userRedemptions >= $coupon->per_user_limit) {
            return $this->fail('Code déjà utilisé');
        }

        $discount = $coupon->discountFor($amount);

        return [
            'valid' => true,
            'coupon' => $coupon,
            'discount' => $discount,
            'message' => 'Code promo appliqué',
        ];
    }

    /**
     * Enregistre l'utilisation d'un coupon pour une commande.
     * Idempotent par commande : ne crée pas de doublon si l'order est déjà lié.
     */
    public function redeem(Coupon $coupon, User $user, Order $order): CouponRedemption
    {
        return DB::transaction(function () use ($coupon, $user, $order) {
            $existing = CouponRedemption::where('coupon_id', $coupon->id)
                ->where('order_id', $order->id)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return $existing;
            }

            $discount = $coupon->discountFor((float) $order->amount);

            $redemption = CouponRedemption::create([
                'coupon_id' => $coupon->id,
                'user_id' => $user->id,
                'company_id' => $order->company_id ?? $user->current_company_id,
                'order_id' => $order->id,
                'amount_discounted' => $discount,
                'redeemed_at' => now(),
            ]);

            $coupon->increment('redemptions_count');

            return $redemption;
        });
    }

    /** Annule l'utilisation d'un coupon liée à une commande (retrait du code). */
    public function cancelForOrder(Coupon $coupon, Order $order): void
    {
        DB::transaction(function () use ($coupon, $order) {
            $redemptions = CouponRedemption::where('coupon_id', $coupon->id)
                ->where('order_id', $order->id)
                ->lockForUpdate()
                ->get();

            foreach ($redemptions as $redemption) {
                $redemption->delete();
                if ($coupon->redemptions_count > 0) {
                    $coupon->decrement('redemptions_count');
                }
            }
        });
    }

    /** @return array{valid: bool, coupon: ?Coupon, discount: float, message: string} */
    private function fail(string $message): array
    {
        return ['valid' => false, 'coupon' => null, 'discount' => 0.0, 'message' => $message];
    }
}
