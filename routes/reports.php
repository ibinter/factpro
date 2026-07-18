<?php

use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rapports & analytiques + exports (cahier des charges §3 RPT, §22.1)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'license'])->group(function () {
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export/{dataset}', [ReportController::class, 'export'])
        ->whereIn('dataset', ['documents', 'customers', 'products', 'payments'])
        ->name('reports.export');
});
