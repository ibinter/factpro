<?php

// Portail client self-service (cahier §11) — accès par lien privé (token), sans compte.
use App\Http\Controllers\PortalController;
use App\Http\Controllers\SignatureController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes publiques (token client — pas de middleware auth)
|--------------------------------------------------------------------------
*/
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/portal/{token}', [PortalController::class, 'show'])->name('portal.show');
    Route::get('/portal/{token}/documents/{document:uuid}/pdf', [PortalController::class, 'pdf'])->name('portal.pdf');
    Route::post('/portal/{token}/documents/{document:uuid}/decision', [PortalController::class, 'decision'])
        ->middleware('throttle:10,1')->name('portal.decision');
});

/*
|--------------------------------------------------------------------------
| Gestion du portail côté société (authentifié)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'license'])->group(function () {
    Route::post('/customers/{customer}/portal-token', [PortalController::class, 'generateToken'])->name('portal.generate');
    Route::get('/documents/{document}/signature', SignatureController::class)->name('documents.signature');
});
