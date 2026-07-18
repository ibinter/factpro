<?php

use App\Http\Controllers\ExpenseController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Notes de frais (cahier §3 NDF) — réservé BUSINESS/ENTERPRISE (§22.1)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'license'])->group(function () {
    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

    // Justificatif privé streamé (pattern preuves de paiement)
    Route::get('/expenses/{expense}/receipt', [ExpenseController::class, 'receipt'])->name('expenses.receipt');

    // Workflow approbation / remboursement (approbateurs seulement)
    Route::post('/expenses/{expense}/review', [ExpenseController::class, 'review'])->name('expenses.review');
    Route::post('/expenses/{expense}/reimburse', [ExpenseController::class, 'reimburse'])->name('expenses.reimburse');
});
