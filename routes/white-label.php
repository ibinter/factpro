<?php

use App\Http\Controllers\Admin\WhiteLabelController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'superadmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/white-label', [WhiteLabelController::class, 'index'])->name('white-label.index');
    Route::post('/white-label', [WhiteLabelController::class, 'store'])->name('white-label.store');
    Route::put('/white-label/{config}', [WhiteLabelController::class, 'update'])->name('white-label.update');
    Route::delete('/white-label/{config}', [WhiteLabelController::class, 'destroy'])->name('white-label.destroy');
    Route::post('/white-label/{config}/logo', [WhiteLabelController::class, 'uploadLogo'])->name('white-label.logo');
});
