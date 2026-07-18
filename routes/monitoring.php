<?php

use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes Monitoring — Phase 17
|--------------------------------------------------------------------------
*/

// Note: GET /health (public) est défini dans web.php
// Auth requis
Route::middleware(['auth'])->group(function () {
    Route::get('/health/detailed', [HealthController::class, 'detailed'])->name('health.detailed');
});

// Superadmin
Route::middleware(['auth', 'superadmin'])->group(function () {
    Route::get('/health/uptimerobot', [HealthController::class, 'uptimeRobotConfig'])->name('health.uptimerobot');
});
