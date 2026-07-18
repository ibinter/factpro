<?php

use App\Http\Controllers\OutgoingWebhookController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'license'])->group(function () {
    Route::get('/outgoing-webhooks', [OutgoingWebhookController::class, 'index'])->name('outgoing-webhooks.index');
    Route::post('/outgoing-webhooks', [OutgoingWebhookController::class, 'store'])->name('outgoing-webhooks.store');
    Route::put('/outgoing-webhooks/{endpoint}', [OutgoingWebhookController::class, 'update'])->name('outgoing-webhooks.update');
    Route::delete('/outgoing-webhooks/{endpoint}', [OutgoingWebhookController::class, 'destroy'])->name('outgoing-webhooks.destroy');
    Route::post('/outgoing-webhooks/{endpoint}/test', [OutgoingWebhookController::class, 'test'])->name('outgoing-webhooks.test');
});
