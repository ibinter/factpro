<?php

// Gestion des clés API (cahier §20) — page /api-tokens (forfaits BUSINESS+).
use App\Http\Controllers\ApiTokenController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'license'])->group(function () {
    Route::get('/api-tokens', [ApiTokenController::class, 'index'])->name('api-tokens.index');
    Route::post('/api-tokens', [ApiTokenController::class, 'store'])->name('api-tokens.store');
    Route::delete('/api-tokens/{tokenId}', [ApiTokenController::class, 'destroy'])
        ->whereNumber('tokenId')->name('api-tokens.destroy');
});
