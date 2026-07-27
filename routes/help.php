<?php

use App\Http\Controllers\AcademyController;
use App\Http\Controllers\HelpController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Routes internes — réservées aux utilisateurs authentifiés
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/help/guide', fn () => Inertia::render('Help/Guide'))->name('help.guide');
    Route::get('/help/guide/pdf', [HelpController::class, 'guidePdf'])->name('help.guide.pdf');
    Route::get('/help/academy', fn () => Inertia::render('Help/Academy'))->name('academy');
    Route::get('/help/cas-pratiques', fn () => Inertia::render('Help/CasPratiques'))->name('help.cas-pratiques');
    Route::get('/academy/download/{slug}', [AcademyController::class, 'download'])->name('academy.download');
});

// Routes publiques du centre d'aide
Route::get('/faq', fn () => Inertia::render('Public/Faq'))->name('faq');
Route::get('/help', [HelpController::class, 'index'])->name('help.index');
Route::get('/help/{slug}', [HelpController::class, 'article'])->name('help.article');
