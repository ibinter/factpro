<?php

// Commissions vendeurs (cahier IBIG §3 CMD « Calcul automatique des commissions
// par commercial ou agent »). Réservé BUSINESS/ENTERPRISE (§22.1).
use App\Http\Controllers\CommissionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'license'])->group(function () {
    Route::get('/commissions', [CommissionController::class, 'index'])->name('commissions.index');

    // Répertoire des vendeurs / commerciaux
    Route::post('/commissions/agents', [CommissionController::class, 'storeAgent'])->name('commissions.agents.store');
    Route::put('/commissions/agents/{agent}', [CommissionController::class, 'updateAgent'])->name('commissions.agents.update');
    Route::delete('/commissions/agents/{agent}', [CommissionController::class, 'destroyAgent'])->name('commissions.agents.destroy');

    // Affectation de clients à un vendeur (customer_ids[])
    Route::post('/commissions/agents/{agent}/assign', [CommissionController::class, 'assign'])->name('commissions.assign');

    // Décomptes de commission
    Route::post('/commissions/payouts', [CommissionController::class, 'generatePayout'])->name('commissions.payouts.generate');
    Route::post('/commissions/payouts/{payout}/pay', [CommissionController::class, 'payPayout'])->name('commissions.payouts.pay');
});
