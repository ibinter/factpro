<?php

// Étiquettes & codes-barres (cahier §6.2) — impression en masse sur planches Avery.
use App\Http\Controllers\LabelController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'license'])->group(function () {
    Route::get('/labels', [LabelController::class, 'index'])->name('labels.index');
    Route::post('/labels/pdf', [LabelController::class, 'pdf'])
        ->middleware('throttle:20,1')
        ->name('labels.pdf');
});
