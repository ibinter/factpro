<?php

use App\Http\Controllers\CustomerSubscriptionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('abonnements', CustomerSubscriptionController::class)
        ->names('subscriptions');

    Route::post('abonnements/{customerSubscription}/pause',
        [CustomerSubscriptionController::class, 'pause'])
        ->name('subscriptions.pause');

    Route::post('abonnements/{customerSubscription}/reprendre',
        [CustomerSubscriptionController::class, 'resume'])
        ->name('subscriptions.resume');

    Route::post('abonnements/{customerSubscription}/annuler',
        [CustomerSubscriptionController::class, 'cancel'])
        ->name('subscriptions.cancel');

    Route::post('abonnements/{customerSubscription}/generer-maintenant',
        [CustomerSubscriptionController::class, 'generateNow'])
        ->name('subscriptions.generate-now');
});
