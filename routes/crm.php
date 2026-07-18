<?php

use App\Http\Controllers\CrmController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'license'])->prefix('crm')->name('crm.')->group(function () {
    Route::get('/', [CrmController::class, 'pipeline'])->name('pipeline');
    Route::get('/stats', [CrmController::class, 'stats'])->name('stats');
    Route::post('/', [CrmController::class, 'store'])->name('store');
    Route::get('/{deal}', [CrmController::class, 'show'])->name('show');
    Route::put('/{deal}', [CrmController::class, 'update'])->name('update');
    Route::post('/{deal}/stage', [CrmController::class, 'moveStage'])->name('stage');
    Route::post('/{deal}/won', [CrmController::class, 'markWon'])->name('won');
    Route::post('/{deal}/lost', [CrmController::class, 'markLost'])->name('lost');
    Route::post('/{deal}/activities', [CrmController::class, 'addActivity'])->name('activities.store');
});
