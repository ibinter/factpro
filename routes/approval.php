<?php

use App\Http\Controllers\ApprovalController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'license'])->prefix('approval')->name('approval.')->group(function () {
    Route::get('/', [ApprovalController::class, 'index'])->name('index');
    Route::get('/pending', [ApprovalController::class, 'myPending'])->name('pending');
    Route::post('/workflows', [ApprovalController::class, 'storeWorkflow'])->name('workflows.store');
    Route::post('/documents/{document}/submit', [ApprovalController::class, 'submit'])->name('submit');
    Route::post('/steps/{step}/approve', [ApprovalController::class, 'approve'])->name('approve');
    Route::post('/steps/{step}/reject', [ApprovalController::class, 'reject'])->name('reject');
    Route::post('/steps/{step}/delegate', [ApprovalController::class, 'delegate'])->name('delegate');
    Route::get('/documents/{document}/history', [ApprovalController::class, 'history'])->name('history');
});
