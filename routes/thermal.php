<?php

// Module Impression thermique — ticket de caisse 58/80/110 mm (cahier IBIG §6).
use App\Http\Controllers\ThermalController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'license'])->group(function () {
    Route::get('/documents/{document}/thermal', ThermalController::class)->name('documents.thermal');
});
