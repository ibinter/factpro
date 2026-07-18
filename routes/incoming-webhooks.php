<?php

use App\Http\Controllers\IncomingWebhookController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'license'])->group(function () {
    Route::get('/incoming-webhooks', [IncomingWebhookController::class, 'index'])->name('incoming-webhooks.index');
    Route::post('/incoming-webhooks', [IncomingWebhookController::class, 'store'])->name('incoming-webhooks.store');
    Route::delete('/incoming-webhooks/{webhook}', [IncomingWebhookController::class, 'destroy'])->name('incoming-webhooks.destroy');
    Route::post('/incoming-webhooks/{webhook}/regenerate', [IncomingWebhookController::class, 'regenerate'])->name('incoming-webhooks.regenerate');
});
