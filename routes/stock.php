<?php

use App\Http\Controllers\AbcAnalysisController;
use App\Http\Controllers\StockController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Gestion des stocks (cahier des charges §8)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'license'])->group(function () {
    Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
    Route::post('/stock/adjust', [StockController::class, 'adjust'])->name('stock.adjust');
    Route::get('/stock/inventory', [StockController::class, 'inventory'])->name('stock.inventory');
    Route::post('/stock/inventory', [StockController::class, 'applyInventory'])->name('stock.inventory.apply');
    Route::get('/stock/valuation', [StockController::class, 'valuation'])->name('stock.valuation');
    Route::get('/stock/abc', [AbcAnalysisController::class, 'index'])->name('stock.abc');
    Route::get('/stock/abc/data', [AbcAnalysisController::class, 'data'])->name('stock.abc.data');
    Route::get('/stock/abc/export', [AbcAnalysisController::class, 'export'])->name('stock.abc.export');
});
