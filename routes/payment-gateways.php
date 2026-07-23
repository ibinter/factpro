<?php

use App\Http\Controllers\CinetPayController;
use App\Http\Controllers\FedaPayController;
use App\Http\Controllers\FlutterwaveController;
use App\Http\Controllers\GatewayConfigController;
use App\Http\Controllers\WaveCiController;
use App\Http\Controllers\MtnMomoController;
use App\Http\Controllers\OrangeMoneyController;
use App\Http\Controllers\PayDunyaController;
use App\Http\Controllers\StripeController;
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

// Wave CI
Route::middleware(['auth'])->group(function () {
    Route::post('/billing/checkout/{order}/wave-ci', [WaveCiController::class, 'initiate'])
        ->middleware('throttle:6,1')->name('billing.wave_ci.initiate');
    Route::get('/billing/wave-ci/return/{order}', [WaveCiController::class, 'handleReturn'])
        ->name('billing.wave_ci.return');
});
Route::post('/webhooks/wave-ci', [WaveCiController::class, 'webhook'])
    ->middleware('throttle:60,1')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
    ->name('webhooks.wave_ci');

// MTN MoMo
Route::middleware(['auth'])->group(function () {
    Route::post('/billing/checkout/{order}/mtn-momo', [MtnMomoController::class, 'initiate'])
        ->middleware('throttle:6,1')->name('billing.mtn_momo.initiate');
    Route::get('/billing/mtn-momo/return/{order}', [MtnMomoController::class, 'handleReturn'])
        ->name('billing.mtn_momo.return');
});
Route::post('/webhooks/mtn-momo', [MtnMomoController::class, 'webhook'])
    ->middleware('throttle:60,1')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
    ->name('webhooks.mtn_momo');

// Orange Money CI
Route::middleware(['auth'])->group(function () {
    Route::post('/billing/checkout/{order}/orange-money', [OrangeMoneyController::class, 'initiate'])
        ->middleware('throttle:6,1')->name('billing.orange_money.initiate');
    Route::get('/billing/orange-money/return/{order}', [OrangeMoneyController::class, 'handleReturn'])
        ->name('billing.orange_money.return');
});
Route::post('/webhooks/orange-money', [OrangeMoneyController::class, 'webhook'])
    ->middleware('throttle:60,1')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
    ->name('webhooks.orange_money');

// PayDunya (Sénégal)
Route::middleware(['auth'])->group(function () {
    Route::post('/billing/checkout/{order}/paydunya', [PayDunyaController::class, 'initiate'])
        ->middleware('throttle:6,1')->name('billing.paydunya.initiate');
    Route::get('/billing/paydunya/return/{order}', [PayDunyaController::class, 'handleReturn'])
        ->name('billing.paydunya.return');
});
Route::post('/webhooks/paydunya', [PayDunyaController::class, 'webhook'])
    ->middleware('throttle:60,1')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
    ->name('webhooks.paydunya');

// Stripe (international)
Route::middleware(['auth'])->group(function () {
    Route::post('/billing/checkout/{order}/stripe', [StripeController::class, 'initiate'])
        ->middleware('throttle:6,1')->name('billing.stripe.initiate');
    Route::get('/billing/stripe/return/{order}', [StripeController::class, 'handleReturn'])
        ->name('billing.stripe.return');
});
Route::post('/webhooks/stripe', [StripeController::class, 'webhook'])
    ->middleware('throttle:60,1')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
    ->name('webhooks.stripe');
