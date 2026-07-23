<?php

use App\Http\Controllers\QualifiedSignatureController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Phase 16E — Signatures qualifiées eIDAS
|--------------------------------------------------------------------------
*/

// Routes internes (auth + licence)
Route::middleware(['auth', 'license'])->prefix('signatures')->name('signatures.')->group(function () {
    Route::get('/', [QualifiedSignatureController::class, 'dashboard'])->name('dashboard');
    Route::post('/invite', [QualifiedSignatureController::class, 'invite'])->name('invite');
    Route::get('/{signature}/status', [QualifiedSignatureController::class, 'status'])->name('status');
    Route::get('/{signature}/download', [QualifiedSignatureController::class, 'download'])->name('download');
});

// Routes publiques (portail signataire externe, pas d'auth)
Route::prefix('sign')->name('sign.')->group(function () {
    Route::get('/{token}', [QualifiedSignatureController::class, 'showPortal'])->name('portal');
    Route::post('/{token}/otp', [QualifiedSignatureController::class, 'sendOtp'])->name('otp');
    Route::post('/{token}/sign', [QualifiedSignatureController::class, 'verifyOtpAndSign'])->name('sign');
    Route::post('/{token}/refuse', [QualifiedSignatureController::class, 'refuse'])->name('refuse');
});
