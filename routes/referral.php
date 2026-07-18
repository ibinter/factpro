<?php

use App\Http\Controllers\ReferralController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes — Programme ambassadeur & parrainage (cahier IBIG §22 Phase 8)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/referral', [ReferralController::class, 'index'])->name('referral.index');
});
