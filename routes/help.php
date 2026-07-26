<?php

use App\Http\Controllers\HelpController;
use Illuminate\Support\Facades\Route;

// Routes publiques du centre d'aide (accessibles sans authentification)
Route::get('/help', [HelpController::class, 'index'])->name('help.index');
Route::get('/help/{slug}', [HelpController::class, 'article'])->name('help.article');
