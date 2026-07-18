<?php

use App\Http\Controllers\GdprController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/account/data', [GdprController::class, 'index'])->name('gdpr.index');
    Route::get('/account/data/export', [GdprController::class, 'export'])->name('gdpr.export');
    Route::delete('/account/data', [GdprController::class, 'destroy'])->name('gdpr.destroy');
    Route::get('/account/audit-log', [GdprController::class, 'auditLog'])->name('gdpr.audit-log');
});
