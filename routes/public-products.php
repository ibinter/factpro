<?php

use App\Http\Controllers\PublicProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes Boutique Publique Produits (Phase 15)
|--------------------------------------------------------------------------
*/

// Routes publiques (sans auth)
Route::get('/p/{company}/{product}', [PublicProductController::class, 'show'])
    ->name('public.product.show');

Route::get('/p/{company}/{product}/json', [PublicProductController::class, 'api'])
    ->name('public.product.api');

// Routes auth (gestion page publique)
Route::middleware(['auth', 'license'])->group(function () {
    Route::post('/products/{product}/enable-public', [PublicProductController::class, 'enablePublic'])
        ->name('products.enable-public');

    Route::post('/products/{product}/disable-public', [PublicProductController::class, 'disablePublic'])
        ->name('products.disable-public');
});
