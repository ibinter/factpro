<?php

// Codes prépayés revendeurs (cahier §Voucher) — possédé par l'agent Voucher.
use App\Http\Controllers\Admin\VoucherAdminController;
use App\Http\Controllers\BillingController;
use Illuminate\Support\Facades\Route;

// ── Client : vérification + activation ──────────────────────────────────────
Route::middleware(['auth'])->group(function () {
    Route::post('/billing/voucher/verify', [BillingController::class, 'verifyVoucher'])
        ->name('billing.voucher.verify');
    Route::post('/billing/voucher/redeem', [BillingController::class, 'redeemVoucher'])
        ->name('billing.voucher.redeem');
});

// ── Superadmin : gestion des lots ───────────────────────────────────────────
Route::middleware(['auth', 'superadmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/vouchers', [VoucherAdminController::class, 'index'])->name('vouchers.index');
    Route::post('/vouchers/generate', [VoucherAdminController::class, 'store'])->name('vouchers.generate');
    Route::get('/vouchers/batch/{batchRef}', [VoucherAdminController::class, 'show'])->name('vouchers.batch');
    Route::get('/vouchers/batch/{batchRef}/export', [VoucherAdminController::class, 'exportCsv'])->name('vouchers.export');
    Route::delete('/vouchers/{voucher}', [VoucherAdminController::class, 'destroy'])->name('vouchers.cancel');
});
