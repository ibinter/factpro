<?php

use App\Http\Controllers\OssVatController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'license'])->prefix('tax/oss')->name('tax.oss.')->group(function () {
    Route::get('/', [OssVatController::class, 'index'])->name('index');
    Route::post('/declaration', [OssVatController::class, 'declaration'])->name('declaration');
    Route::post('/validate-vat', [OssVatController::class, 'validateVatNumber'])->name('validate-vat');
});
