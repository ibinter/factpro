<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'license'])->group(function () {
    Route::resource('sav/reparations', App\Http\Controllers\RepairController::class)->names('sav.repairs');
});
