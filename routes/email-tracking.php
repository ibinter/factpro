<?php

use App\Http\Controllers\EmailTrackingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Email Tracking — Phase 13
|--------------------------------------------------------------------------
*/

// Routes publiques (pixel et clic — PAS d'auth)
Route::get('/track/open/{token}', [EmailTrackingController::class, 'trackOpen'])->name('tracking.open');
Route::get('/track/click/{token}', [EmailTrackingController::class, 'trackClick'])->name('tracking.click');

// Routes authentifiées (dashboard)
Route::middleware(['auth', 'license'])->group(function () {
    Route::get('/email-tracking', [EmailTrackingController::class, 'dashboard'])->name('email-tracking.dashboard');
    Route::get('/email-tracking/stats', [EmailTrackingController::class, 'stats'])->name('email-tracking.stats');
    Route::get('/email-tracking/document/{document}', [EmailTrackingController::class, 'documentTracking'])->name('email-tracking.document');
});
