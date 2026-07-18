<?php

use App\Http\Controllers\DocumentController;
use App\Http\Controllers\PaymentPlanController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Acomptes & plans de paiement échelonnés (cahier IBIG §12)
|--------------------------------------------------------------------------
| Réservé aux forfaits PRO et plus (§22.1) — le gate plan est appliqué dans
| les contrôleurs (403 sur toutes les actions).
*/
Route::middleware(['auth', 'license'])->group(function () {
    Route::get('/payment-plans', [PaymentPlanController::class, 'index'])->name('payment-plans.index');
    Route::get('/payment-plans/{plan}', [PaymentPlanController::class, 'show'])->name('payment-plans.show');

    // Création d'un plan depuis un devis / une facture
    Route::post('/documents/{document}/payment-plan', [DocumentController::class, 'createPaymentPlan'])
        ->name('payment-plans.create');

    // Génération de la facture d'acompte / de solde d'une échéance
    Route::post('/payment-plans/installments/{installment}/invoice', [PaymentPlanController::class, 'invoiceInstallment'])
        ->name('payment-plans.installment.invoice');

    Route::post('/payment-plans/{plan}/cancel', [PaymentPlanController::class, 'cancel'])
        ->name('payment-plans.cancel');
});
