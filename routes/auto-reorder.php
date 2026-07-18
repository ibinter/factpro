<?php

use App\Http\Controllers\AutoReorderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'license'])->prefix('stock/auto-reorder')->name('stock.auto-reorder.')->group(function () {
    Route::get('/simulate', [AutoReorderController::class, 'simulate'])->name('simulate');
    Route::get('/history', [AutoReorderController::class, 'history'])->name('history');

    Route::get('/', [AutoReorderController::class, 'index'])->name('index');
    Route::post('/', [AutoReorderController::class, 'store'])->name('store');
    Route::put('/{rule}', [AutoReorderController::class, 'update'])->name('update');
    Route::delete('/{rule}', [AutoReorderController::class, 'destroy'])->name('destroy');
    Route::post('/{rule}/trigger', [AutoReorderController::class, 'trigger'])->name('trigger');
});
