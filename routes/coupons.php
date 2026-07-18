<?php

// Coupons & réductions (cahier §22.2) — possédé par l'agent Coupons.
use App\Http\Controllers\Admin\CouponAdminController;
use App\Http\Controllers\BillingController;
use Illuminate\Support\Facades\Route;

// Console Superadmin — CRUD des coupons.
Route::middleware(['auth', 'superadmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/coupons', [CouponAdminController::class, 'index'])->name('coupons');
    Route::post('/coupons', [CouponAdminController::class, 'store'])->name('coupons.store');
    Route::put('/coupons/{coupon}', [CouponAdminController::class, 'update'])->name('coupons.update');
    Route::delete('/coupons/{coupon}', [CouponAdminController::class, 'destroy'])->name('coupons.destroy');
    Route::post('/coupons/{coupon}/toggle', [CouponAdminController::class, 'toggle'])->name('coupons.toggle');
});

// Application d'un code promo au moment du checkout abonnement.
Route::middleware(['auth'])->group(function () {
    Route::post('/billing/checkout/{order}/coupon', [BillingController::class, 'applyCoupon'])->name('billing.coupon.apply');
    Route::delete('/billing/checkout/{order}/coupon', [BillingController::class, 'removeCoupon'])->name('billing.coupon.remove');
});
