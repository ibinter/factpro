<?php

use App\Http\Controllers\TaxConfigController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'license'])->group(function () {
    Route::get('/tax-config', [TaxConfigController::class, 'index'])->name('tax-config.index');
    Route::post('/tax-config', [TaxConfigController::class, 'store'])->name('tax-config.store');
    Route::put('/tax-config/{taxConfig}', [TaxConfigController::class, 'update'])->name('tax-config.update');
    Route::get('/tax-config/export', [TaxConfigController::class, 'export'])->name('tax-config.export');

    // Vues déclarations pays spécifiques
    Route::get('/tax-config/senegal', [TaxConfigController::class, 'senegalDeclaration'])->name('tax-config.senegal');
    Route::get('/tax-config/algerie', [TaxConfigController::class, 'algerieDeclaration'])->name('tax-config.algerie');

    // API JSON déclarations
    Route::get('/tax-config/api/senegal', [TaxConfigController::class, 'apiSenegalDeclaration'])->name('tax-config.api.senegal');
    Route::get('/tax-config/api/cote-ivoire', [TaxConfigController::class, 'apiCoteIvoireDeclaration'])->name('tax-config.api.cote-ivoire');
    Route::get('/tax-config/api/algerie', [TaxConfigController::class, 'apiAlgerieDeclaration'])->name('tax-config.api.algerie');
});
