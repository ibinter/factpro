<?php

// Console Superadmin étendue (script §16) — fichier possédé par l'agent Admin.
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\LicenseAdminController;
use App\Http\Controllers\Admin\PaymentMethodAdminController;
use App\Http\Controllers\Admin\PaymentValidationController;
use App\Http\Controllers\Admin\PlanAdminController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'superadmin'])->prefix('admin')->name('admin.')->group(function () {
    // §16.1 — Tableau de bord financier
    Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
    Route::get('/financial-dashboard', [AdminDashboardController::class, 'financialDashboard'])->name('financial-dashboard');

    // §16.2 — Gestion des licences
    Route::get('/licenses', [LicenseAdminController::class, 'index'])->name('licenses');
    Route::get('/license-manager', [LicenseAdminController::class, 'manager'])->name('license-manager');
    Route::post('/licenses/{license}/extend', [LicenseAdminController::class, 'extend'])->name('licenses.extend');
    Route::post('/licenses/{license}/suspend', [LicenseAdminController::class, 'suspend'])->name('licenses.suspend');
    Route::post('/licenses/{license}/reactivate', [LicenseAdminController::class, 'reactivate'])->name('licenses.reactivate');
    Route::post('/licenses/{license}/revoke', [LicenseAdminController::class, 'revoke'])->name('licenses.revoke');
    Route::post('/licenses/{license}/confirm-provisional', [LicenseAdminController::class, 'confirmProvisional'])->name('licenses.confirm-provisional');

    // §16.3 — Moyens de paiement manuels (CRUD + toggle)
    Route::get('/payment-methods', [PaymentMethodAdminController::class, 'index'])->name('methods');
    Route::get('/payment-method-settings', [PaymentMethodAdminController::class, 'settings'])->name('payment-method-settings');
    Route::post('/payment-methods', [PaymentMethodAdminController::class, 'store'])->name('methods.store');
    Route::put('/payment-methods/{method}', [PaymentMethodAdminController::class, 'update'])->name('methods.update');
    Route::delete('/payment-methods/{method}', [PaymentMethodAdminController::class, 'destroy'])->name('methods.destroy');
    Route::post('/payment-methods/{method}/toggle', [PaymentMethodAdminController::class, 'toggle'])->name('methods.toggle');

    // §16.4 — Forfaits
    Route::get('/plans', [PlanAdminController::class, 'index'])->name('plans');
    Route::put('/plans/{plan}', [PlanAdminController::class, 'update'])->name('plans.update');

    // §16.5 — File de validation enrichie
    Route::get('/payment-queue', [PaymentValidationController::class, 'queue'])->name('payment-queue');
    Route::post('/payments/{transaction}/complement', [PaymentValidationController::class, 'requestComplement'])->name('payments.complement');
    Route::post('/orders/{order}/provisional', [PaymentValidationController::class, 'activateProvisionally'])->name('orders.provisional');
    Route::post('/payments/{transaction}/suspect', [PaymentValidationController::class, 'markSuspect'])->name('payments.suspect');
});
