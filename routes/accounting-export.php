<?php

use App\Http\Controllers\AccountingExportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'license'])->prefix('accounting/export')->name('accounting.export.')->group(function () {
    Route::get('/', [AccountingExportController::class, 'index'])->name('index');
    Route::post('/sage', [AccountingExportController::class, 'exportSage'])->name('sage');
    Route::post('/quickbooks', [AccountingExportController::class, 'exportQuickBooks'])->name('quickbooks');
    Route::post('/pennylane', [AccountingExportController::class, 'exportPennylane'])->name('pennylane');
    Route::post('/preview', [AccountingExportController::class, 'preview'])->name('preview');
});
