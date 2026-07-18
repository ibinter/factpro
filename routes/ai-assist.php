<?php

use App\Http\Controllers\AiAssistController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'license'])->prefix('ai')->name('ai.')->group(function () {
    Route::get('/status', [AiAssistController::class, 'status'])->name('status');
    Route::post('/suggest-description', [AiAssistController::class, 'suggestDescription'])->name('suggest-description');
    Route::post('/detect-duplicates', [AiAssistController::class, 'detectDuplicates'])->name('detect-duplicates');
    Route::post('/summarize-document', [AiAssistController::class, 'summarizeDocument'])->name('summarize-document');
    Route::post('/suggest-price', [AiAssistController::class, 'suggestPrice'])->name('suggest-price');
});
