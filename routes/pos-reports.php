<?php

// Phase 15 — Rapports Z/X & Fonds de Caisse
use App\Http\Controllers\PosZReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'license'])->prefix('pos')->name('pos.')->group(function () {
    Route::post('/sessions/open', [PosZReportController::class, 'openSession'])->name('sessions.open');
    Route::get('/sessions/{session}/x-report', [PosZReportController::class, 'xReport'])->name('x-report');
    Route::post('/sessions/{session}/z-report', [PosZReportController::class, 'generateZ'])->name('z-report.generate');
    Route::get('/sessions/{session}/z-report/pdf', [PosZReportController::class, 'pdfZ'])->name('z-report.pdf');
    Route::get('/z-reports', [PosZReportController::class, 'history'])->name('z-reports.history');
});
