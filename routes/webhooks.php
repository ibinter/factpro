<?php

use App\Http\Controllers\MonerooPaymentController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Webhooks fournisseurs de paiement (script §22)
|--------------------------------------------------------------------------
| SANS session, SANS CSRF : appelé serveur → serveur par Moneroo.
| La sécurité repose sur la signature HMAC vérifiée dans le contrôleur.
*/

Route::post('/webhooks/moneroo', [WebhookController::class, 'moneroo'])
    ->middleware('throttle:60,1')
    ->name('webhooks.moneroo');

/*
|--------------------------------------------------------------------------
| Paiement Moneroo côté client (authentifié, session web classique)
|--------------------------------------------------------------------------
*/

Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/billing/checkout/{order}/moneroo', [MonerooPaymentController::class, 'initiate'])
        ->middleware('throttle:6,1')
        ->name('billing.moneroo.initiate');

    Route::get('/billing/moneroo/return/{order}', [MonerooPaymentController::class, 'handleReturn'])
        ->name('billing.moneroo.return');
});
