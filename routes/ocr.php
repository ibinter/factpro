<?php

use App\Http\Controllers\OcrController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'license'])->group(function () {
    Route::get('/purchases/ocr', [OcrController::class, 'index'])->name('purchases.ocr.index');
    Route::post('/purchases/ocr/upload', [OcrController::class, 'upload'])->name('purchases.ocr.upload');
    Route::post('/purchases/ocr/{scan}/process', [OcrController::class, 'process'])->name('purchases.ocr.process');
    Route::post('/purchases/ocr/{scan}/convert', [OcrController::class, 'convert'])->name('purchases.ocr.convert');
});
