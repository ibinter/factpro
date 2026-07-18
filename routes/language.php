<?php

use App\Http\Controllers\LanguageController;
use Illuminate\Support\Facades\Route;

Route::post('/language/switch', [LanguageController::class, 'switch'])
    ->name('language.switch')
    ->middleware('auth');
