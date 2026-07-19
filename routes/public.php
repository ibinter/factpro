<?php

// Pages de vente publiques (cahier §1/§22) — possédé par l'agent Landing.
use App\Http\Controllers\DemoController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\PublicVerifyController;
use Illuminate\Support\Facades\Route;

// Accès démo instantané — connecte le compte demo@factpro.test sans mot de passe.
Route::get('/demo-login', [DemoController::class, 'login'])
    ->middleware('throttle:20,1')
    ->name('demo.login');

// Grille tarifaire détaillée (Inertia).
Route::get('/pricing', [PublicController::class, 'pricing'])->name('public.pricing');

// Données JSON des forfaits actifs (consommé par la landing).
Route::get('/pricing-data', [PublicController::class, 'plansJson'])
    ->middleware('throttle:60,1')
    ->name('public.plans-data');

// ── Vérification publique de documents (Phase 16 — §verify.factpro.ibigsoft.com) ──
// Page Inertia multi-langue (FR/EN/AR/PT/ES) — sans authentification.
Route::get('/public/verify/{uuid}', [PublicVerifyController::class, 'show'])
    ->middleware('throttle:120,1')
    ->name('public.verify');

// API JSON de vérification (pour intégration externe, webhooks, etc.).
Route::get('/api/public/verify/{uuid}', [PublicVerifyController::class, 'api'])
    ->middleware('throttle:120,1')
    ->name('public.verify.api');

