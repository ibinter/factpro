<?php

use App\Http\Controllers\CompanyController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Multi-sociétés (cahier IBIG §3 MLT)
|--------------------------------------------------------------------------
| Liste / création / bascule : accessibles avec une licence expirée (un
| utilisateur doit toujours pouvoir gérer et basculer entre ses sociétés).
| Paramètres de la société courante : licence utilisable requise.
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
    Route::post('/companies', [CompanyController::class, 'store'])->name('companies.store');
    Route::post('/companies/{company}/switch', [CompanyController::class, 'switch'])->name('companies.switch');
});

Route::middleware(['auth', 'license'])->group(function () {
    Route::get('/company/settings', [CompanyController::class, 'settings'])->name('companies.settings');
    Route::patch('/company/settings', [CompanyController::class, 'updateSettings'])->name('companies.settings.update');
    Route::post('/company/logo', [CompanyController::class, 'uploadLogo'])->name('companies.logo');
});
