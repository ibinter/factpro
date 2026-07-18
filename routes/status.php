<?php

use App\Http\Controllers\StatusPageController;
use Illuminate\Support\Facades\Route;

// Public - sans authentification
Route::get('/status', [StatusPageController::class, 'public'])->name('status.public');
Route::get('/status.json', [StatusPageController::class, 'api'])->name('status.api');

// Superadmin
Route::middleware(['auth', 'superadmin'])->prefix('admin')->group(function () {
    Route::get('/ops-board', [StatusPageController::class, 'opsBoard'])->name('admin.ops-board');
    Route::post('/incidents', [StatusPageController::class, 'storeIncident'])->name('admin.incidents.store');
    Route::put('/incidents/{incident}', [StatusPageController::class, 'updateIncident'])->name('admin.incidents.update');
    Route::post('/incidents/{incident}/resolve', [StatusPageController::class, 'resolveIncident'])->name('admin.incidents.resolve');
});
