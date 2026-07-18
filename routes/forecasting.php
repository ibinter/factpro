<?php

use App\Http\Controllers\ForecastingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'license'])->prefix('forecasting')->name('forecasting.')->group(function () {
    Route::get('/', [ForecastingController::class, 'dashboard'])->name('dashboard');
    Route::post('/targets', [ForecastingController::class, 'storeTarget'])->name('targets.store');
    Route::get('/forecast', [ForecastingController::class, 'forecast'])->name('forecast');
    Route::get('/comparison', [ForecastingController::class, 'comparison'])->name('comparison');
    Route::get('/underperformance', [ForecastingController::class, 'underperformance'])->name('underperformance');
    Route::get('/history', [ForecastingController::class, 'history'])->name('history');
    Route::get('/export', [ForecastingController::class, 'exportReport'])->name('export');
});
