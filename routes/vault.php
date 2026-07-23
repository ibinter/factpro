<?php

use App\Http\Controllers\VaultController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Phase 16B — Coffre-fort numérique immuable
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'license'])->prefix('vault')->name('vault.')->group(function () {
    Route::get('/', [VaultController::class, 'index'])->name('index');
    Route::get('/report/integrity', [VaultController::class, 'integrityReport'])->name('integrity-report');
    Route::get('/{vault}', [VaultController::class, 'show'])->name('show');
    Route::get('/{vault}/download', [VaultController::class, 'download'])->name('download');
    Route::post('/{vault}/verify', [VaultController::class, 'verify'])->name('verify');
    Route::delete('/{vault}', [VaultController::class, 'purge'])->name('purge');
});
