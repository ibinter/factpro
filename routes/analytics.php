<?php

use App\Http\Controllers\AnalyticsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'license'])->prefix('analytics')->name('analytics.')->group(function () {
    // Page principale
    Route::get('/', [AnalyticsController::class, 'dashboard'])->name('dashboard');

    // Widgets CRUD
    Route::get('/widgets', [AnalyticsController::class, 'widgets'])->name('widgets');
    Route::post('/widgets', [AnalyticsController::class, 'addWidget'])->name('widgets.add');
    Route::post('/widgets/save', [AnalyticsController::class, 'saveWidgets'])->name('widgets.save');
    Route::delete('/widgets/{widget}', [AnalyticsController::class, 'removeWidget'])->name('widgets.remove');

    // Data
    Route::get('/data', [AnalyticsController::class, 'data'])->name('data');

    // AI Insights
    Route::post('/ai-insights', [AnalyticsController::class, 'aiInsights'])
        ->middleware('throttle:10,1')
        ->name('ai-insights');

    // Export PDF
    Route::get('/export/report', [AnalyticsController::class, 'exportReport'])->name('export.report');
});
