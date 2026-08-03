<?php

use App\Http\Controllers\DocumentConversionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'license'])->group(function () {
    Route::post('/documents/{document}/convert', [DocumentConversionController::class, 'convert'])
        ->name('documents.convert');

    Route::get('/documents/{document}/chain', [DocumentConversionController::class, 'chain'])
        ->name('documents.chain');
});
