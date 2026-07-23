<?php

// Health check endpoint (CI/CD + monitoring)
Route::get('/health', function () {
    return response()->json([
        'status'    => 'ok',
        'app'       => config('app.name'),
        'env'       => config('app.env'),
        'timestamp' => now()->toISOString(),
        'php'       => PHP_VERSION,
        'laravel'   => app()->version(),
    ]);
})->name('health');

use App\Http\Controllers\Admin\DeliveryAdminController;
use App\Http\Controllers\Admin\DeliveryAgentController;
use App\Http\Controllers\Admin\PaymentValidationController;
use App\Http\Controllers\Admin\RevenueController;
use App\Http\Controllers\ApiDocsController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentSendController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VerifyController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
})->name('home');

/*
|--------------------------------------------------------------------------
| VÃ©rification publique d'authenticitÃ© (QR anti-falsification â€” cahier Â§5)
|--------------------------------------------------------------------------
*/
Route::get('/verify/{uuid}', VerifyController::class)->name('verify');

/*
|--------------------------------------------------------------------------
| Espace authentifiÃ©
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    // Documentation API & SDK
    Route::get('/api-docs', ApiDocsController::class)->name('api-docs');

    // Abonnement & facturation â€” accessible mÃªme licence expirÃ©e (pour payer)
    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
    Route::get('/billing/plans', [BillingController::class, 'plans'])->name('billing.plans');
    Route::post('/billing/subscribe', [BillingController::class, 'subscribe'])
        ->middleware('throttle:10,1')->name('billing.subscribe');
    Route::get('/billing/checkout/{order}', [BillingController::class, 'checkout'])->name('billing.checkout');
    Route::post('/billing/checkout/{order}/proof', [BillingController::class, 'submitProof'])
        ->middleware('throttle:6,1')->name('billing.proof');
    Route::get('/billing/proof-status/{order}', [BillingController::class, 'proofStatus'])
        ->name('billing.proof-status');
    Route::post('/billing/proof/{order}/complement', [BillingController::class, 'addComplement'])
        ->middleware('throttle:6,1')->name('billing.proof.complement');
    Route::get('/billing/receipt/{order}/download', [BillingController::class, 'downloadReceipt'])
        ->name('billing.receipt.download');
    Route::post('/billing/initiate/crypto', [BillingController::class, 'initiateCrypto'])
        ->middleware('throttle:6,1')->name('billing.initiate.crypto');
    Route::post('/billing/initiate/cheque', [BillingController::class, 'initiateCheque'])
        ->middleware('throttle:6,1')->name('billing.initiate.cheque');
    Route::post('/billing/initiate/delivery', [BillingController::class, 'initiateDelivery'])
        ->middleware('throttle:6,1')->name('billing.initiate.delivery');
    Route::get('/billing/delivery-status/{order}', [BillingController::class, 'deliveryStatus'])
        ->name('billing.delivery-status');

    // Profil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Changelog / Nouveautés
    Route::get('/nouveautes', [\App\Http\Controllers\ChangelogController::class, 'index'])->name('changelog.index');
});

/*
|--------------------------------------------------------------------------
| FonctionnalitÃ©s mÃ©tier â€” licence utilisable requise
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'license'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('customers', CustomerController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::post('/customers/quick', [CustomerController::class, 'quickStore'])->name('customers.quick');
    Route::resource('products', ProductController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::resource('documents', DocumentController::class);
    Route::post('/documents/{document}/finalize', [DocumentController::class, 'finalize'])->name('documents.finalize');
    Route::post('/documents/{document}/status', [DocumentController::class, 'changeStatus'])->name('documents.status');
    Route::post('/documents/{document}/template', [DocumentController::class, 'updateTemplate'])->name('documents.template');
    Route::post('/documents/{document}/convert', [DocumentController::class, 'convert'])->name('documents.convert');
    Route::post('/documents/{document}/payments', [DocumentController::class, 'registerPayment'])->name('documents.payments');
    Route::get('/documents/{document}/pdf', [DocumentController::class, 'pdf'])->name('documents.pdf');
    Route::get('/documents/{document}/docx', [DocumentController::class, 'docx'])->name('documents.docx');
    Route::get('/documents/export/excel', [DocumentController::class, 'exportExcel'])->name('documents.export.excel');
    Route::post('/documents/{document}/send', DocumentSendController::class)->name('documents.send');
    Route::post('/documents/{document}/clone', [DocumentController::class, 'clone'])->name('documents.clone');

    Route::get('/search', \App\Http\Controllers\GlobalSearchController::class)->name('search.global');
});

/*
|--------------------------------------------------------------------------
| Console Superadmin (script Â§16)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'superadmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('crypto-wallets', \App\Http\Controllers\Admin\CryptoWalletAdminController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->names('crypto-wallets');

    Route::get('/revenue', [RevenueController::class, 'index'])->name('revenue');
    Route::get('/payments', [PaymentValidationController::class, 'index'])->name('payments');
    Route::post('/payments/{transaction}/validate', [PaymentValidationController::class, 'validatePayment'])->name('payments.validate');
    Route::post('/payments/{transaction}/reject', [PaymentValidationController::class, 'reject'])->name('payments.reject');
    Route::post('/payments/{transaction}/complement', [PaymentValidationController::class, 'requestComplement'])->name('payments.complement');
    Route::get('/proofs/{proof}', [PaymentValidationController::class, 'proof'])->name('proofs.show');

    // Livraisons COD
    Route::get('/deliveries', [DeliveryAdminController::class, 'index'])->name('deliveries.index');
    Route::post('/deliveries/{delivery}/assign', [DeliveryAdminController::class, 'assign'])->name('deliveries.assign');
    Route::post('/deliveries/{delivery}/confirm', [DeliveryAdminController::class, 'confirmPayment'])->name('deliveries.confirm');
    Route::resource('delivery-agents', DeliveryAgentController::class)->only(['index', 'store', 'update', 'destroy']);
});

require __DIR__.'/pos.php';
require __DIR__.'/stock.php';
require __DIR__.'/reminders.php';
require __DIR__.'/thermal.php';
require __DIR__.'/portal.php';
require __DIR__.'/api-tokens.php';
require __DIR__.'/companies.php';
require __DIR__.'/labels.php';
require __DIR__.'/projects.php';
require __DIR__.'/project-milestones.php';
require __DIR__.'/expenses.php';
require __DIR__.'/accounting.php';
require __DIR__.'/recurring.php';
require __DIR__.'/reports.php';
require __DIR__.'/admin-extra.php';
require __DIR__.'/purchases.php';
require __DIR__.'/payment-plans.php';
require __DIR__.'/commissions.php';
require __DIR__.'/team.php';
require __DIR__.'/coupons.php';
require __DIR__.'/public.php';

require __DIR__.'/import.php';
require __DIR__.'/incoming-webhooks.php';
require __DIR__.'/auto-reorder.php';
require __DIR__.'/outgoing-webhooks.php';
require __DIR__.'/notification-channels.php';
require __DIR__.'/gdpr.php';

require __DIR__.'/tax-config.php';
require __DIR__.'/payment-gateways.php';
require __DIR__.'/referral.php';
require __DIR__.'/help.php';

require __DIR__.'/white-label.php';
require __DIR__.'/facturx.php';
require __DIR__.'/loyalty.php';

require __DIR__.'/template-marketplace.php';

require __DIR__.'/push.php';

// Phase 17 — Monitoring Sentry & UptimeRobot
require __DIR__.'/monitoring.php';

require __DIR__.'/ocr.php';
require __DIR__.'/ai-assist.php';
require __DIR__.'/crm.php';
require __DIR__.'/offline-sync.php';
require __DIR__.'/vouchers.php';

// Phase 15C — Analytics & BI
require __DIR__.'/analytics.php';

require __DIR__.'/auth.php';
require __DIR__.'/language.php';
require __DIR__.'/notifications-center.php';
require __DIR__.'/quote-links.php';

// Phase 12 — Exports Excel natifs
require __DIR__.'/excel-export.php';

// Phase 13 — Forecasting & Objectifs
require __DIR__.'/forecasting.php';

// Phase 13 — Workflow d'approbation
require __DIR__.'/approval.php';

// Phase 13 — Module RH & Paie
require __DIR__.'/hr.php';

// Phase 13 — Email Tracking
require __DIR__.'/email-tracking.php';

// Phase 14 — Archivage Immuable Légal
require __DIR__.'/archive.php';

// Phase 14 — Exports comptables tiers (Sage, QuickBooks, Pennylane)
require __DIR__.'/accounting-export.php';

// Phase 15 — Rapports Z/X POS & Fonds de Caisse
require __DIR__.'/pos-reports.php';

// Phase 15 — TVA Intracommunautaire OSS/UE
require __DIR__.'/oss-vat.php';

// Phase 15 — Boutique Publique & Analyse ABC
require __DIR__.'/public-products.php';

// Phase 15 — Thermique 110mm & Étiquettes spéciales
require __DIR__.'/special-labels.php';

// Phase 16 — Scan caméra POS Mobile
require __DIR__.'/barcode.php';

// Phase 16A/16B — Chiffrement AES-256 & Coffre-fort numérique immuable
require __DIR__.'/vault.php';

// Phase 17 — Mobile Money Direct
require __DIR__.'/mobile-money.php';

// Phase 17 — Statut Système & Ops
require __DIR__.'/status.php';

// Phase 14 — Intelligence & Automatisation IA
// Module D — Visites terrain & Signature tablette
use App\Http\Controllers\VisitController;
use App\Http\Controllers\SignatureController;
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/visits', [VisitController::class, 'index'])->name('visits.index');
    Route::post('/visits', [VisitController::class, 'store'])->name('visits.store');
    Route::patch('/visits/{visit}', [VisitController::class, 'update'])->name('visits.update');
    Route::delete('/visits/{visit}', [VisitController::class, 'destroy'])->name('visits.destroy');
    Route::post('/visits/{visit}/checkin', [VisitController::class, 'checkin'])->name('visits.checkin');
    Route::post('/visits/{visit}/checkout', [VisitController::class, 'checkout'])->name('visits.checkout');

    // Signature tablette
    Route::get('/documents/{document}/signature', [SignatureController::class, 'show'])->name('documents.signature.show');
    Route::post('/documents/{document}/signature', [SignatureController::class, 'store'])->name('documents.signature.store');
    Route::delete('/documents/{document}/signature', [SignatureController::class, 'destroy'])->name('documents.signature.destroy');
});

use App\Http\Controllers\AiReminderController;
Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/ai/reminder/{customer}', [AiReminderController::class, 'generate'])->name('ai.reminder');
    Route::get('/ai/suggest-price-v2', [AiReminderController::class, 'suggestPrice'])->name('ai.suggest-price-v2');
});

// Phase 14 — Immobilisations
use App\Http\Controllers\AssetController;
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('assets', AssetController::class);
});

// Phase 15B — Contrats commerciaux
use App\Http\Controllers\ContractController;
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('contracts', ContractController::class)->except(['create', 'edit']);
    Route::post('/contracts/{contract}/upload-version', [ContractController::class, 'uploadVersion'])->name('contracts.upload-version');
});

// Phase 15B — GED (Gestion Électronique de Documents)
use App\Http\Controllers\GedController;
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/ged', [GedController::class, 'index'])->name('ged.index');
    Route::post('/ged/documents', [GedController::class, 'store'])->name('ged.store');
    Route::put('/ged/documents/{gedDocument}', [GedController::class, 'update'])->name('ged.update');
    Route::delete('/ged/documents/{gedDocument}', [GedController::class, 'destroy'])->name('ged.destroy');
    Route::get('/ged/documents/{gedDocument}/download', [GedController::class, 'download'])->name('ged.download');
    Route::post('/ged/folders', [GedController::class, 'createFolder'])->name('ged.folders.store');
    Route::delete('/ged/folders/{gedFolder}', [GedController::class, 'deleteFolder'])->name('ged.folders.destroy');
});

// Phase 16E — Signatures qualifiées eIDAS niveau avancé
require __DIR__.'/signatures.php';

// Phase 14 — Portail Fournisseur (pages publiques)
use App\Http\Controllers\SupplierPortalController;
Route::get('/supplier/portal/{token}', [SupplierPortalController::class, 'show'])->name('supplier.portal.show');
Route::post('/supplier/portal/{token}/respond', [SupplierPortalController::class, 'respond'])->name('supplier.portal.respond');

// Phase 14 — Portail Fournisseur (pages internes)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/documents/{document}/invite-supplier', [SupplierPortalController::class, 'invite'])->name('supplier.invite');
    Route::get('/documents/{document}/supplier-compare', [SupplierPortalController::class, 'compare'])->name('supplier.compare');
    Route::post('/supplier-offers/{offer}/select', [SupplierPortalController::class, 'select'])->name('supplier.select');
});


// Phase 16C — Politique de sécurité & sessions
use App\Http\Controllers\SecurityPolicyController;
Route::middleware(['auth', 'verified'])->prefix('security')->name('security.')->group(function () {
    Route::get('/policy', [SecurityPolicyController::class, 'show'])->name('policy');
    Route::put('/policy', [SecurityPolicyController::class, 'update'])->name('policy.update');
    Route::get('/access-logs', [SecurityPolicyController::class, 'accessLogs'])->name('access-logs');
    Route::get('/sessions', [SecurityPolicyController::class, 'sessions'])->name('sessions');
    Route::delete('/sessions/{sessionId}', [SecurityPolicyController::class, 'killSession'])->name('sessions.kill');
    Route::delete('/sessions', [SecurityPolicyController::class, 'killAllSessions'])->name('sessions.kill-all');
});

// Phase 16D — Conformité RGPD
use App\Http\Controllers\GdprComplianceController;
Route::middleware(['auth', 'verified'])->prefix('gdpr')->name('gdpr.')->group(function () {
    Route::get('/', [GdprComplianceController::class, 'dashboard'])->name('dashboard');
    Route::get('/requests', [GdprComplianceController::class, 'requests'])->name('requests');
    Route::post('/requests', [GdprComplianceController::class, 'createRequest'])->name('requests.store');
    Route::put('/requests/{gdprRequest}', [GdprComplianceController::class, 'updateRequest'])->name('requests.update');
    Route::post('/export-data', [GdprComplianceController::class, 'exportData'])->name('export-data');
    Route::post('/delete-subject', [GdprComplianceController::class, 'deleteSubject'])->name('delete-subject');
    Route::get('/report', [GdprComplianceController::class, 'generateReport'])->name('report');
});
