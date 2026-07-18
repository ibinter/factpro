<?php

use App\Http\Controllers\MobileMoneyController;
use Illuminate\Support\Facades\Route;

// Webhooks publics (sans auth CSRF)
Route::post('/webhooks/mobile-money/{driver}', [MobileMoneyController::class, 'webhook'])
    ->middleware('throttle:60,1')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
    ->name('mobile-money.webhook');

// Paiement (authentifié)
Route::middleware(['auth'])->group(function () {
    Route::get('/mobile-money', [MobileMoneyController::class, 'index'])->name('mobile-money.index');
    Route::post('/mobile-money/initiate', [MobileMoneyController::class, 'initiate'])->name('mobile-money.initiate');
    Route::get('/mobile-money/status/{reference}', [MobileMoneyController::class, 'status'])->name('mobile-money.status');
    Route::post('/mobile-money/detect-driver', [MobileMoneyController::class, 'detectDriver'])->name('mobile-money.detect');
});
