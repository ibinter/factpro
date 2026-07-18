<?php

use App\Http\Controllers\CinetPayController;
use App\Http\Controllers\FedaPayController;
use App\Http\Controllers\FlutterwaveController;
use App\Http\Controllers\GatewayConfigController;
use Illuminate\Support\Facades\Route;

// Superadmin
Route::middleware(['auth', 'superadmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/gateways', [GatewayConfigController::class, 'index'])->name('gateways');
    Route::put('/gateways/{gateway}', [GatewayConfigController::class, 'update'])->name('gateways.update');
});

// CinetPay (checkout côté client)
Route::middleware(['auth'])->group(function () {
    Route::post('/billing/checkout/{order}/cinetpay', [CinetPayController::class, 'initiate'])
        ->middleware('throttle:6,1')->name('billing.cinetpay.initiate');
    Route::get('/billing/cinetpay/return/{order}', [CinetPayController::class, 'handleReturn'])
        ->name('billing.cinetpay.return');
});
Route::post('/webhooks/cinetpay', [CinetPayController::class, 'webhook'])
    ->middleware('throttle:60,1')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
    ->name('webhooks.cinetpay');

// FedaPay
Route::middleware(['auth'])->group(function () {
    Route::post('/billing/checkout/{order}/fedapay', [FedaPayController::class, 'initiate'])
        ->middleware('throttle:6,1')->name('billing.fedapay.initiate');
    Route::get('/billing/fedapay/return/{order}', [FedaPayController::class, 'handleReturn'])
        ->name('billing.fedapay.return');
});
Route::post('/webhooks/fedapay', [FedaPayController::class, 'webhook'])
    ->middleware('throttle:60,1')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
    ->name('webhooks.fedapay');

// Flutterwave
Route::middleware(['auth'])->group(function () {
    Route::post('/billing/checkout/{order}/flutterwave', [FlutterwaveController::class, 'initiate'])
        ->middleware('throttle:6,1')->name('billing.flutterwave.initiate');
    Route::get('/billing/flutterwave/return/{order}', [FlutterwaveController::class, 'handleReturn'])
        ->name('billing.flutterwave.return');
});
Route::post('/webhooks/flutterwave', [FlutterwaveController::class, 'webhook'])
    ->middleware('throttle:60,1')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
    ->name('webhooks.flutterwave');
