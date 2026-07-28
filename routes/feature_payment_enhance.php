<?php

use Illuminate\Support\Facades\Route;

// Routes publiques — sans middleware auth
Route::post('/pay/{token}/preuve', [App\Http\Controllers\PaymentLinkController::class, 'uploadProof'])->name('pay.proof');
Route::get('/pay/{token}/statut', [App\Http\Controllers\PaymentLinkController::class, 'statusCheck'])->name('pay.status');
