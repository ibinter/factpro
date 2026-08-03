<?php

use Illuminate\Support\Facades\Route;

// Routes publiques (sans auth)
Route::get('/catalog/{slug}', [App\Http\Controllers\PublicCatalogController::class, 'show'])->name('catalog.public');

// Routes privées (avec auth)
Route::middleware(['auth', 'license'])->group(function () {
    Route::get('/parametres/catalogue', [App\Http\Controllers\Settings\CatalogSettingsController::class, 'index'])->name('settings.catalog');
    Route::put('/parametres/catalogue', [App\Http\Controllers\Settings\CatalogSettingsController::class, 'update'])->name('settings.catalog.update');
    Route::post('/parametres/catalogue/produits/{product}/toggle', [App\Http\Controllers\Settings\CatalogSettingsController::class, 'toggleProduct'])->name('settings.catalog.toggle-product');
});
