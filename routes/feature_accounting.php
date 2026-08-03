<?php

use App\Http\Controllers\DoubleEntryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'license'])->group(function () {
    Route::prefix('comptabilite')->name('accounting.')->group(function () {
        Route::get('comptes',            [DoubleEntryController::class, 'accounts'])->name('accounts');
        Route::post('comptes',           [DoubleEntryController::class, 'createAccount'])->name('accounts.store');
        Route::get('journal',            [DoubleEntryController::class, 'journal'])->name('journal');
        Route::post('ecritures',         [DoubleEntryController::class, 'createEntry'])->name('entries.store');
        Route::get('balance',            [DoubleEntryController::class, 'trialBalance'])->name('trial-balance');
        Route::post('importer-plan',     [DoubleEntryController::class, 'importDefaultPlan'])->name('import-plan');
    });
});
