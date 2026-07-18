<?php

use App\Http\Controllers\FacturXController;
use Illuminate\Support\Facades\Route;

// Factur-X / e-facture France 2026 (cahier §5 + §10.2) — Phase 9.
Route::middleware(['auth', 'license'])->group(function () {
    Route::get('/documents/{document}/facturx', [FacturXController::class, 'export'])
        ->name('documents.facturx');
    Route::get('/documents/{document}/facturx/preview', [FacturXController::class, 'preview'])
        ->name('documents.facturx.preview');
});
