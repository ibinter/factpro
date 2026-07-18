<?php

use App\Http\Controllers\TemplateMarketplaceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'license'])->prefix('templates')->name('templates.')->group(function () {
    Route::get('/marketplace', [TemplateMarketplaceController::class, 'index'])->name('marketplace.index');
    Route::get('/marketplace/mine', [TemplateMarketplaceController::class, 'myTemplates'])->name('marketplace.mine');
    Route::post('/marketplace', [TemplateMarketplaceController::class, 'store'])->name('marketplace.store');
    Route::put('/marketplace/{template}', [TemplateMarketplaceController::class, 'update'])->name('marketplace.update');
    Route::delete('/marketplace/{template}', [TemplateMarketplaceController::class, 'destroy'])->name('marketplace.destroy');
    Route::post('/marketplace/{template}/download', [TemplateMarketplaceController::class, 'download'])->name('marketplace.download');
    Route::post('/marketplace/{template}/rate', [TemplateMarketplaceController::class, 'rate'])->name('marketplace.rate');
    Route::post('/marketplace/{template}/approve', [TemplateMarketplaceController::class, 'approve'])->name('marketplace.approve');
});
