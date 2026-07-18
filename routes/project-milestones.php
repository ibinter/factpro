<?php

use App\Http\Controllers\ProjectMilestoneController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'license'])->group(function () {
    Route::get('/projects/{project}/milestones', [ProjectMilestoneController::class, 'index'])->name('projects.milestones.index');
    Route::post('/projects/{project}/milestones', [ProjectMilestoneController::class, 'store'])->name('projects.milestones.store');
    Route::put('/milestones/{milestone}', [ProjectMilestoneController::class, 'update'])->name('milestones.update');
    Route::delete('/milestones/{milestone}', [ProjectMilestoneController::class, 'destroy'])->name('milestones.destroy');
    Route::post('/milestones/{milestone}/bill', [ProjectMilestoneController::class, 'bill'])->name('milestones.bill');
    Route::get('/projects/{project}/budget', [ProjectMilestoneController::class, 'budgetStatus'])->name('projects.budget');
});
