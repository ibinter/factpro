<?php

use App\Http\Controllers\LeaseController;
use App\Http\Controllers\PropertyController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'license'])->prefix('immobilier')->name('real-estate.')->group(function () {
    Route::resource('proprietes', PropertyController::class)->names('properties');
    Route::resource('baux', LeaseController::class)->names('leases');
    Route::post('baux/{baux}/resilier', [LeaseController::class, 'terminate'])->name('leases.terminate');
    Route::post('baux/{baux}/quittance', [LeaseController::class, 'generateRent'])->name('leases.generate-rent');
});
