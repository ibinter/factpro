<?php

use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Plan;
use App\Services\CouponService;

/** Crée un coupon avec des valeurs par défaut raisonnables. */
function createCoupon(array $attributes = []): Coupon
{
    return Coupon::create([
        'code' => 'IBIGSTART',
        'type' => 'percent',
        'value' => 50,
        'applies_to' => 'subscription',
        'per_user_limit' => 1,
        'is_active' => true,
        ...$attributes,
    ]);
}

beforeEach(function () {
    seedPlans();
});

/*
|--------------------------------------------------------------------------
| Console admin — CRUD & sécurité
|--------------------------------------------------------------------------
*/

it('interdit la console coupons aux non-superadmins', function () {
    $user = createUserWithCompany();

    $this->actingAs($user)->get(route('admin.coupons'))->assertForbidden();
});

it('affiche la console coupons au superadmin', function () {
    $admin = createUserWithCompany(['is_superadmin' => true]);

    $this->actingAs($admin)->get(route('admin.coupons'))->assertOk();
});

it('permet au superadmin de créer un coupon (code en majuscules)', function () {
    $admin = createUserWithCompany(['is_superadmin' => true]);

    $this->actingAs($admin)->post(route('admin.coupons.store'), [
        'code' => 'ibigstart',
        'type' => 'percent',
        'value' => 50,
        'per_user_limit' => 1,
        'is_active' => true,
    ])->assertRedirect();

    expect(Coupon::where('code', 'IBIGSTART')->exists())->toBeTrue();
});

it('refuse un pourcentage supérieur à 100', function () {
    $admin = createUserWithCompany(['is_superadmin' => true]);

    $this->actingAs($admin)->post(route('admin.coupons.store'), [
        'code' => 'TROP',
        'type' => 'percent',
        'value' => 150,
        'per_user_limit' => 1,
    ])->assertSessionHasErrors('value');
});

it('met à jour, bascule et supprime un coupon', function () {
    $admin = createUserWithCompany(['is_superadmin' => true]);
    $coupon = createCoupon();

    $this->actingAs($admin)->put(route('admin.coupons.update', $coupon->id), [
        'code' => 'IBIGSTART',
        'type' => 'fixed',
        'value' => 2000,
        'per_user_limit' => 1,
        'is_active' => true,
    ])->assertRedirect();
    expect($coupon->fresh()->type)->toBe('fixed');

    $this->actingAs($admin)->post(route('admin.coupons.toggle', $coupon->id))->assertRedirect();
    expect($coupon->fresh()->is_active)->toBeFalse();

    $this->actingAs($admin)->delete(route('admin.coupons.destroy', $coupon->id))->assertRedirect();
    expect($coupon->fresh()->trashed())->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| CouponService::validateFor
|--------------------------------------------------------------------------
*/

it('calcule une remise de 50% sur 10000 = 5000', function () {
    $user = createUserWithCompany();
    $plan = Plan::where('code', 'pro')->first();
    $coupon = createCoupon(['type' => 'percent', 'value' => 50]);

    $result = app(CouponService::class)->validateFor('IBIGSTART', $user, $plan, 10000);

    expect($result['valid'])->toBeTrue()
        ->and($result['discount'])->toBe(5000.0);
});

it('plafonne une remise fixe au montant', function () {
    $user = createUserWithCompany();
    $plan = Plan::where('code', 'pro')->first();
    $coupon = createCoupon(['type' => 'fixed', 'value' => 999999]);

    $result = app(CouponService::class)->validateFor('IBIGSTART', $user, $plan, 8000);

    expect($result['discount'])->toBe(8000.0);
});

it('rejette un code inconnu ou expiré', function () {
    $user = createUserWithCompany();
    $plan = Plan::where('code', 'pro')->first();
    createCoupon(['expires_at' => now()->subDay()]);

    $unknown = app(CouponService::class)->validateFor('NOPE', $user, $plan, 10000);
    expect($unknown['valid'])->toBeFalse()
        ->and($unknown['message'])->toBe('Code promo invalide ou expiré');

    $expired = app(CouponService::class)->validateFor('IBIGSTART', $user, $plan, 10000);
    expect($expired['valid'])->toBeFalse();
});

it('rejette un coupon inactif', function () {
    $user = createUserWithCompany();
    $plan = Plan::where('code', 'pro')->first();
    createCoupon(['is_active' => false]);

    $result = app(CouponService::class)->validateFor('IBIGSTART', $user, $plan, 10000);

    expect($result['valid'])->toBeFalse();
});

it('refuse un coupon restreint à un autre forfait', function () {
    $user = createUserWithCompany();
    $plan = Plan::where('code', 'pro')->first();
    createCoupon(['plan_code' => 'business']);

    $result = app(CouponService::class)->validateFor('IBIGSTART', $user, $plan, 10000);

    expect($result['valid'])->toBeFalse()
        ->and($result['message'])->toBe("Ce code ne s'applique pas à ce forfait");
});

it('refuse quand le montant minimum n\'est pas atteint', function () {
    $user = createUserWithCompany();
    $plan = Plan::where('code', 'pro')->first();
    createCoupon(['min_amount' => 20000]);

    $result = app(CouponService::class)->validateFor('IBIGSTART', $user, $plan, 10000);

    expect($result['valid'])->toBeFalse()
        ->and($result['message'])->toBe('Montant minimum non atteint');
});

it('refuse quand la limite par utilisateur est dépassée', function () {
    $user = createUserWithCompany();
    $plan = Plan::where('code', 'pro')->first();
    $coupon = createCoupon(['per_user_limit' => 1]);

    CouponRedemption::create([
        'coupon_id' => $coupon->id,
        'user_id' => $user->id,
        'amount_discounted' => 5000,
        'redeemed_at' => now(),
    ]);

    $result = app(CouponService::class)->validateFor('IBIGSTART', $user, $plan, 10000);

    expect($result['valid'])->toBeFalse()
        ->and($result['message'])->toBe('Code déjà utilisé');
});

it('refuse quand le nombre max de redemptions est atteint', function () {
    $user = createUserWithCompany();
    $plan = Plan::where('code', 'pro')->first();
    createCoupon(['max_redemptions' => 5, 'redemptions_count' => 5]);

    $result = app(CouponService::class)->validateFor('IBIGSTART', $user, $plan, 10000);

    expect($result['valid'])->toBeFalse();
});

/*
|--------------------------------------------------------------------------
| CouponService::redeem — idempotence
|--------------------------------------------------------------------------
*/

it('redeem est idempotent par commande', function () {
    $user = createUserWithCompany();
    $plan = Plan::where('code', 'pro')->first();
    $order = createPendingOrder($user, $plan);
    $coupon = createCoupon();

    $service = app(CouponService::class);
    $service->redeem($coupon, $user, $order);
    $service->redeem($coupon, $user, $order);

    expect(CouponRedemption::where('order_id', $order->id)->count())->toBe(1)
        ->and($coupon->fresh()->redemptions_count)->toBe(1);
});

/*
|--------------------------------------------------------------------------
| BillingController — application / retrait
|--------------------------------------------------------------------------
*/

it('applique un coupon au checkout et recalcule le total', function () {
    $user = createUserWithCompany();
    $plan = Plan::where('code', 'pro')->first();
    $order = createPendingOrder($user, $plan);
    $coupon = createCoupon(['type' => 'percent', 'value' => 50]);

    $expectedDiscount = round((float) $order->amount * 0.5, 2);

    $this->actingAs($user)
        ->post(route('billing.coupon.apply', $order->id), ['code' => 'IBIGSTART'])
        ->assertRedirect();

    $order->refresh();
    expect((float) $order->discount_amount)->toBe($expectedDiscount)
        ->and((float) $order->total_amount)->toBe(round((float) $order->amount - $expectedDiscount, 2))
        ->and($order->metadata['coupon_code'])->toBe('IBIGSTART')
        ->and(CouponRedemption::where('order_id', $order->id)->count())->toBe(1)
        ->and($coupon->fresh()->redemptions_count)->toBe(1);
});

it('retire un coupon et restaure le montant', function () {
    $user = createUserWithCompany();
    $plan = Plan::where('code', 'pro')->first();
    $order = createPendingOrder($user, $plan);
    $coupon = createCoupon();

    $this->actingAs($user)->post(route('billing.coupon.apply', $order->id), ['code' => 'IBIGSTART']);
    $this->actingAs($user)->delete(route('billing.coupon.remove', $order->id))->assertRedirect();

    $order->refresh();
    expect((float) $order->discount_amount)->toBe(0.0)
        ->and((float) $order->total_amount)->toBe((float) $order->amount)
        ->and($order->metadata['coupon_code'] ?? null)->toBeNull()
        ->and(CouponRedemption::where('order_id', $order->id)->count())->toBe(0)
        ->and($coupon->fresh()->redemptions_count)->toBe(0);
});

it('refuse l\'application d\'un coupon sur la commande d\'un autre utilisateur', function () {
    $owner = createUserWithCompany();
    $intruder = createUserWithCompany();
    $plan = Plan::where('code', 'pro')->first();
    $order = createPendingOrder($owner, $plan);
    createCoupon();

    $this->actingAs($intruder)
        ->post(route('billing.coupon.apply', $order->id), ['code' => 'IBIGSTART'])
        ->assertForbidden();
});
