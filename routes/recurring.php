<?php

use App\Http\Controllers\RecurringController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Factures récurrentes — abonnements automatiques (cahier §3)
|--------------------------------------------------------------------------
| Inclus dès le forfait PRO (§22.1) — le gate plan est géré par le contrôleur
| (upsell sur l'index, 403 sur les mutations).
*/
Route::middleware(['auth', 'license'])->group(function () {
    Route::get('/recurring', [RecurringController::class, 'index'])->name('recurring.index');
    Route::post('/recurring', [RecurringController::class, 'store'])->name('recurring.store');
    Route::put('/recurring/{template}', [RecurringController::class, 'update'])->name('recurring.update');
    Route::delete('/recurring/{template}', [RecurringController::class, 'destroy'])->name('recurring.destroy');
    Route::post('/recurring/{template}/toggle', [RecurringController::class, 'toggle'])->name('recurring.toggle');
    Route::post('/recurring/{template}/run', [RecurringController::class, 'run'])->name('recurring.run');
});
