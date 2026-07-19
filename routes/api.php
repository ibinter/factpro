<?php

/*
|--------------------------------------------------------------------------
| API REST publique v1 (cahier §20)
|--------------------------------------------------------------------------
| Authentification : tokens Sanctum (Authorization: Bearer …).
| Réservée aux forfaits BUSINESS (1000 req/h) et ENTERPRISE (illimité)
| — cahier §22.1, contrôle via EnsureApiPlanAccess.
| Préfixe automatique : /api (bootstrap/app.php).
*/

use App\Http\Controllers\Api\CustomerApiController;
use App\Http\Controllers\Api\MakeController;
use App\Http\Controllers\Api\ZapierController;
use App\Http\Controllers\Api\DocsController;
use App\Http\Controllers\Api\DocumentApiController;
use App\Http\Controllers\Api\MeController;
use App\Http\Controllers\Api\OpenApiController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Middleware\EnsureApiPlanAccess;
use Illuminate\Support\Facades\Route;

// Webhooks entrants — Zapier
Route::prefix('zapier')->middleware('validate.incoming.webhook')->group(function () {
    Route::post('/customers', [ZapierController::class, 'createCustomer']);
    Route::post('/documents', [ZapierController::class, 'createDocument']);
    Route::post('/payments', [ZapierController::class, 'registerPayment']);
    Route::get('/triggers/new-invoice', [ZapierController::class, 'triggerNewInvoice']);
    Route::get('/triggers/new-customer', [ZapierController::class, 'triggerNewCustomer']);
});

// Webhooks entrants — Make
Route::prefix('make')->middleware('validate.incoming.webhook')->group(function () {
    Route::post('/customers', [MakeController::class, 'createCustomer']);
    Route::post('/documents', [MakeController::class, 'createDocument']);
    Route::get('/triggers/invoices', [MakeController::class, 'triggerInvoices']);
});

// Mini-documentation publique (sans authentification)
Route::get('/v1/docs', DocsController::class)->name('api.v1.docs');

// Spec OpenAPI 3.0 publique
Route::get('/openapi.json', OpenApiController::class)->name('api.openapi');

Route::prefix('v1')
    ->middleware(['auth:sanctum', EnsureApiPlanAccess::class])
    ->name('api.v1.')
    ->group(function () {
        Route::get('/me', MeController::class)->name('me');

        // Clients
        Route::get('/customers', [CustomerApiController::class, 'index'])->name('customers.index');
        Route::post('/customers', [CustomerApiController::class, 'store'])->name('customers.store');
        Route::get('/customers/{id}', [CustomerApiController::class, 'show'])->whereNumber('id')->name('customers.show');
        Route::put('/customers/{id}', [CustomerApiController::class, 'update'])->whereNumber('id')->name('customers.update');
        Route::delete('/customers/{id}', [CustomerApiController::class, 'destroy'])->whereNumber('id')->name('customers.destroy');

        // Produits
        Route::get('/products', [ProductApiController::class, 'index'])->name('products.index');
        Route::post('/products', [ProductApiController::class, 'store'])->name('products.store');
        Route::get('/products/{id}', [ProductApiController::class, 'show'])->whereNumber('id')->name('products.show');
        Route::put('/products/{id}', [ProductApiController::class, 'update'])->whereNumber('id')->name('products.update');
        Route::delete('/products/{id}', [ProductApiController::class, 'destroy'])->whereNumber('id')->name('products.destroy');

        // Documents (devis, factures, …)
        Route::get('/documents', [DocumentApiController::class, 'index'])->name('documents.index');
        Route::post('/documents', [DocumentApiController::class, 'store'])->name('documents.store');
        Route::get('/documents/{uuid}', [DocumentApiController::class, 'show'])->whereUuid('uuid')->name('documents.show');
        Route::get('/documents/{uuid}/pdf', [DocumentApiController::class, 'pdf'])->whereUuid('uuid')->name('documents.pdf');
    });

// SARA — chatbot IA Groq (public, sans auth, throttlé)
use App\Http\Controllers\SaraController;
Route::post('/sara/chat', [SaraController::class, 'chat'])
    ->middleware('throttle:30,1')
    ->name('sara.chat');
