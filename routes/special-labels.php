<?php

// Étiquettes spéciales (Phase 15 — cahier IBIG §6.3) : sticker livraison, étiquette garantie.
use App\Http\Controllers\SpecialLabelController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'license'])->group(function () {
    Route::get('/documents/{document}/delivery-sticker', [SpecialLabelController::class, 'deliverySticker'])
        ->name('documents.delivery-sticker');

    Route::get('/documents/{document}/warranty/{item}', [SpecialLabelController::class, 'warrantyLabel'])
        ->name('documents.warranty-label');

    Route::get('/documents/{document}/special-labels', [SpecialLabelController::class, 'index'])
        ->name('documents.special-labels');
});
