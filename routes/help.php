<?php

use App\Http\Controllers\AcademyController;
use App\Http\Controllers\HelpController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Routes publiques du centre d'aide (accessibles sans authentification)
Route::get('/faq', fn () => Inertia::render('Public/Faq'))->name('faq');
Route::get('/help', [HelpController::class, 'index'])->name('help.index');
Route::get('/help/academy', fn () => Inertia::render('Help/Academy'))->name('academy');
Route::get('/academy/download/{slug}', [AcademyController::class, 'download'])->name('academy.download');
Route::get('/help/{slug}', [HelpController::class, 'article'])->name('help.article');
