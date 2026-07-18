<?php

// Time tracking & projets (cahier §9) — réservé BUSINESS/ENTERPRISE (§22.1).
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TimeEntryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'license'])->group(function () {
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');

    // Conversion des heures sélectionnées en facture
    Route::post('/projects/{project}/invoice', [ProjectController::class, 'invoice'])->name('projects.invoice');

    // Entrées de temps
    Route::post('/projects/{project}/entries', [TimeEntryController::class, 'store'])->name('projects.entries.store');
    Route::put('/time-entries/{entry}', [TimeEntryController::class, 'update'])->name('projects.entries.update');
    Route::delete('/time-entries/{entry}', [TimeEntryController::class, 'destroy'])->name('projects.entries.destroy');
});
