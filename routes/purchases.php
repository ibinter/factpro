<?php

// Achats fournisseurs (cahier IBIG §10.1 « Journal des achats ») — répertoire
// fournisseurs + factures d'achat. Réservé BUSINESS/ENTERPRISE (§22.1).
use App\Http\Controllers\PurchaseController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'license'])->group(function () {
    Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');

    // Répertoire fournisseurs
    Route::post('/purchases/suppliers', [PurchaseController::class, 'storeSupplier'])->name('purchases.suppliers.store');
    Route::put('/purchases/suppliers/{supplier}', [PurchaseController::class, 'updateSupplier'])->name('purchases.suppliers.update');
    Route::delete('/purchases/suppliers/{supplier}', [PurchaseController::class, 'destroySupplier'])->name('purchases.suppliers.destroy');

    // Factures d'achat (justificatif multipart privé)
    Route::post('/purchases/invoices', [PurchaseController::class, 'storeInvoice'])->name('purchases.invoices.store');
    Route::put('/purchases/invoices/{invoice}', [PurchaseController::class, 'updateInvoice'])->name('purchases.invoices.update');
    Route::delete('/purchases/invoices/{invoice}', [PurchaseController::class, 'destroyInvoice'])->name('purchases.invoices.destroy');

    // Règlement (partiel ou solde)
    Route::post('/purchases/invoices/{invoice}/payment', [PurchaseController::class, 'payment'])->name('purchases.invoices.payment');

    // Justificatif privé streamé (pattern preuves de paiement)
    Route::get('/purchases/invoices/{invoice}/receipt', [PurchaseController::class, 'receipt'])->name('purchases.invoices.receipt');
});
