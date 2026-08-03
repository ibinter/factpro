<?php

use Illuminate\Support\Facades\Route;

// Webhook SANS auth (Meta appelle ce endpoint)
Route::post('/webhooks/whatsapp', [App\Http\Controllers\WhatsAppController::class, 'webhook'])->name('webhooks.whatsapp');
Route::get('/webhooks/whatsapp', [App\Http\Controllers\WhatsAppController::class, 'webhook'])->name('webhooks.whatsapp.verify');

// Routes privées
Route::middleware(['auth', 'license'])->group(function () {
    Route::post('/whatsapp/envoyer', [App\Http\Controllers\WhatsAppController::class, 'sendMessage'])->name('whatsapp.send');
    Route::post('/whatsapp/tester', [App\Http\Controllers\WhatsAppController::class, 'test'])->name('whatsapp.test');
    Route::get('/whatsapp/historique', [App\Http\Controllers\WhatsAppController::class, 'history'])->name('whatsapp.history');
    Route::get('/parametres/whatsapp', [App\Http\Controllers\Settings\WhatsAppSettingsController::class, 'index'])->name('settings.whatsapp');
    Route::put('/parametres/whatsapp', [App\Http\Controllers\Settings\WhatsAppSettingsController::class, 'update'])->name('settings.whatsapp.update');
});
