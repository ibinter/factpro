<?php

use App\Http\Controllers\ImportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'license'])->prefix('import')->name('import.')->group(function () {
    Route::get('/', [ImportController::class, 'index'])->name('index');
    Route::post('/customers/upload',  [ImportController::class, 'uploadCustomers'])->name('customers.upload');
    Route::post('/customers/execute', [ImportController::class, 'importCustomers'])->name('customers.execute');
    Route::post('/products/upload',   [ImportController::class, 'uploadProducts'])->name('products.upload');
    Route::post('/products/execute',  [ImportController::class, 'importProducts'])->name('products.execute');
    Route::get('/templates/customers', [ImportController::class, 'downloadCustomerTemplate'])->name('templates.customers');
    Route::get('/templates/products',  [ImportController::class, 'downloadProductTemplate'])->name('templates.products');
});
