<?php

use App\Http\Controllers\PublicQuoteController;
use App\Http\Controllers\QuoteLinkController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Liens de partage de devis (Phase 12 — devis interactif public)
|--------------------------------------------------------------------------
*/

// Routes authentifiées (côté vendeur)
Route::middleware(['auth', 'license'])->group(function () {
    Route::post('/documents/{document}/quote-link', [QuoteLinkController::class, 'store'])
        ->name('quote-links.store');
    Route::get('/documents/{document}/quote-links', [QuoteLinkController::class, 'index'])
        ->name('quote-links.index');
    Route::delete('/quote-links/{link}', [QuoteLinkController::class, 'destroy'])
        ->name('quote-links.destroy');
    Route::get('/quote-links/{link}/status', [QuoteLinkController::class, 'status'])
        ->name('quote-links.status');
});

// Routes publiques (client, sans auth)
Route::prefix('q')->name('q.')->middleware('throttle:10,1')->group(function () {
    Route::get('/{token}', [PublicQuoteController::class, 'show'])->name('show');
    Route::post('/{token}/password', [PublicQuoteController::class, 'checkPassword'])->name('password');
    Route::post('/{token}/sign', [PublicQuoteController::class, 'sign'])->name('sign');
    Route::post('/{token}/decline', [PublicQuoteController::class, 'decline'])->name('decline');
});
