<?php

use App\Http\Controllers\ReminderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Module Relances intelligentes (cahier des charges §13)
|--------------------------------------------------------------------------
| Tableau de bord des factures en retard, relance manuelle et paramétrage
| des seuils d'escalade J+3 / J+7 / J+15.
*/

Route::middleware(['auth', 'license'])->group(function () {
    Route::get('/reminders', [ReminderController::class, 'index'])->name('reminders.index');
    Route::patch('/reminders/settings', [ReminderController::class, 'updateSettings'])->name('reminders.settings');
    Route::post('/reminders/{document}/send', [ReminderController::class, 'send'])
        ->middleware('throttle:30,1')
        ->name('reminders.send');
});
