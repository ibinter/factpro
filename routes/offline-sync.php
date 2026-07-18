<?php

use App\Http\Controllers\OfflineSyncController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes PWA hors-ligne (Phase 12)
|--------------------------------------------------------------------------
| Middleware : auth + license
| Préfixe   : /offline-sync
*/

Route::middleware(['auth', 'license'])
    ->prefix('offline-sync')
    ->name('offline.')
    ->group(function () {
        // Synchroniser les documents créés hors-ligne
        Route::post('/document', [OfflineSyncController::class, 'flush'])
            ->name('flush');

        // Données à mettre en cache dans IndexedDB (clients + produits)
        Route::get('/cache-data', [OfflineSyncController::class, 'cacheData'])
            ->name('cache-data');
    });
