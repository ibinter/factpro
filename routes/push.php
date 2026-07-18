<?php

use App\Http\Controllers\PushSubscriptionController;
use Illuminate\Support\Facades\Route;

// Clé publique VAPID accessible sans authentification
Route::get('/push/vapid-public-key', [PushSubscriptionController::class, 'publicKey'])
    ->name('push.public-key');

Route::middleware(['auth'])->group(function () {
    Route::post('/push/subscribe', [PushSubscriptionController::class, 'subscribe'])
        ->name('push.subscribe');

    Route::delete('/push/unsubscribe', [PushSubscriptionController::class, 'unsubscribe'])
        ->name('push.unsubscribe');
});
