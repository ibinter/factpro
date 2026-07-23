<?php

// Pages de vente publiques (cahier §1/§22) — possédé par l'agent Landing.
use App\Http\Controllers\AboutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DemoController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\PublicVerifyController;
use App\Http\Controllers\StatusController;
use Illuminate\Support\Facades\Route;

// Accès démo instantané — connecte le compte demo@factpro.test sans mot de passe.
Route::get('/demo-login', [DemoController::class, 'login'])
    ->middleware('throttle:20,1')
    ->name('demo.login');

// Page de statut système publique.
Route::get('/status', [StatusController::class, 'index'])->name('status');

// Page À propos d'IBIG Soft.
Route::get('/a-propos', [AboutController::class, 'index'])->name('about');

// Page Contact + traitement formulaire.
Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:5,1')->name('contact.store');

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

// ── Pages légales ──────────────────────────────────────────────────────────
Route::prefix('legal')->name('legal.')->group(function () {
    Route::get('/mentions',        [LegalController::class, 'mentions'])->name('mentions');
    Route::get('/cgu',             [LegalController::class, 'cgu'])->name('cgu');
    Route::get('/confidentialite', [LegalController::class, 'confidentialite'])->name('confidentialite');
    Route::get('/cookies',         [LegalController::class, 'cookies'])->name('cookies');
    Route::get('/pi',              [LegalController::class, 'pi'])->name('pi');
    Route::get('/resiliation',           [LegalController::class, 'resiliation'])->name('resiliation');
    Route::get('/sla',                   [LegalController::class, 'sla'])->name('sla');
    Route::get('/securite',              [LegalController::class, 'securite'])->name('securite');
    Route::get('/accessibilite',         [LegalController::class, 'accessibilite'])->name('accessibilite');
    Route::get('/remboursement',         [LegalController::class, 'remboursement'])->name('remboursement');
    Route::get('/anti-spam',             [LegalController::class, 'antiSpam'])->name('anti-spam');
    Route::get('/conditions-api',        [LegalController::class, 'conditionsApi'])->name('conditions-api');
    Route::get('/partenaires',           [LegalController::class, 'partenaires'])->name('partenaires');
    Route::get('/utilisation-acceptable',[LegalController::class, 'utilisationAcceptable'])->name('utilisation-acceptable');
    Route::get('/rgpd-details',          [LegalController::class, 'rgpdDetails'])->name('rgpd-details');
    Route::get('/dpa',                   [LegalController::class, 'dpa'])->name('dpa');
    Route::get('/plan-continuite',       [LegalController::class, 'planContinuite'])->name('plan-continuite');
    Route::get('/charte-ethique',        [LegalController::class, 'charteEthique'])->name('charte-ethique');
});

