<?php

use App\Http\Controllers\BankReconciliationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'license'])->group(function () {
    Route::get('/rapprochement-bancaire', [BankReconciliationController::class, 'index'])->name('banking.index');
    Route::post('/rapprochement-bancaire/comptes', [BankReconciliationController::class, 'createAccount'])->name('banking.account.create');
    Route::get('/rapprochement-bancaire/{account}', [BankReconciliationController::class, 'show'])->name('banking.show');
    Route::post('/rapprochement-bancaire/{account}/import-csv', [BankReconciliationController::class, 'importCsv'])->name('banking.import');
    Route::post('/rapprochement-bancaire/correspondance', [BankReconciliationController::class, 'matchTransaction'])->name('banking.match');
    Route::delete('/rapprochement-bancaire/transactions/{transaction}/correspondance', [BankReconciliationController::class, 'unmatchTransaction'])->name('banking.unmatch');
});
