<?php

use App\Http\Controllers\ExcelExportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'license'])->prefix('export')->name('export.')->group(function () {
    Route::get('/excel/customers', [ExcelExportController::class, 'customers'])->name('excel.customers');
    Route::get('/excel/products',  [ExcelExportController::class, 'products'])->name('excel.products');
    Route::get('/excel/documents', [ExcelExportController::class, 'documents'])->name('excel.documents');
    Route::get('/excel/revenue',   [ExcelExportController::class, 'monthlyRevenue'])->name('excel.revenue');
    Route::get('/excel/fec',       [ExcelExportController::class, 'fec'])->name('excel.fec');
});
