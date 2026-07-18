<?php

use App\Http\Controllers\LoyaltyController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'license'])->prefix('loyalty')->name('loyalty.')->group(function () {
    Route::get('/', [LoyaltyController::class, 'dashboard'])->name('dashboard');
    Route::post('/setup', [LoyaltyController::class, 'setupProgram'])->name('setup');
    Route::get('/customers/{customer}/points', [LoyaltyController::class, 'customerPoints'])->name('customer.points');
    Route::post('/redeem', [LoyaltyController::class, 'redeemReward'])->name('redeem');
    Route::post('/rewards', [LoyaltyController::class, 'storeReward'])->name('rewards.store');
    Route::get('/customers/{customer}/card', [LoyaltyController::class, 'cardPdf'])->name('card');
    Route::get('/top-customers', [LoyaltyController::class, 'topCustomers'])->name('top-customers');
});
