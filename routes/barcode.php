<?php

// Phase 16 — Scan caméra POS Mobile
use App\Http\Controllers\BarcodeLookupController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'license'])->group(function () {
    Route::get('/barcode/lookup', [BarcodeLookupController::class, 'lookup'])
        ->name('barcode.lookup');

    Route::post('/barcode/assign', [BarcodeLookupController::class, 'assign'])
        ->name('barcode.assign');

    Route::get('/pos/mobile', fn () => Inertia::render('Pos/MobilePos'))
        ->name('pos.mobile');
});
