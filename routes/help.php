<?php

use App\Http\Controllers\HelpController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/help', [HelpController::class, 'index'])->name('help.index');
    Route::get('/help/{slug}', [HelpController::class, 'article'])->name('help.article');
});
