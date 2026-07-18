<?php

// Comptabilité simplifiée (cahier IBIG §10) — journal des ventes, balance âgée,
// récap TVA, compte de résultat, exports FEC & CSV. Réservé BUSINESS/ENTERPRISE.
use App\Http\Controllers\AccountingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'license'])->group(function () {
    Route::get('/accounting', [AccountingController::class, 'index'])
        ->name('accounting.index');

    Route::get('/accounting/export/fec', [AccountingController::class, 'fecExport'])
        ->middleware('throttle:12,1')
        ->name('accounting.fec');

    Route::get('/accounting/export/journal-csv', [AccountingController::class, 'journalCsv'])
        ->middleware('throttle:12,1')
        ->name('accounting.journal.csv');
});
