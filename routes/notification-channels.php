<?php

use App\Http\Controllers\NotificationChannelController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'license'])->group(function () {
    Route::get('/notification-channels', [NotificationChannelController::class, 'index'])
        ->name('notification-channels.index');
    Route::post('/notification-channels', [NotificationChannelController::class, 'store'])
        ->name('notification-channels.store');
    Route::put('/notification-channels/{channel}', [NotificationChannelController::class, 'update'])
        ->name('notification-channels.update');
    Route::delete('/notification-channels/{channel}', [NotificationChannelController::class, 'destroy'])
        ->name('notification-channels.destroy');
    Route::post('/notification-channels/{channel}/test', [NotificationChannelController::class, 'test'])
        ->name('notification-channels.test');
});
