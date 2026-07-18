<?php

use App\Http\Controllers\ArchiveController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'license'])->prefix('archive')->name('archive.')->group(function () {
    Route::get('/', [ArchiveController::class, 'index'])->name('index');
    Route::post('/export-zip', [ArchiveController::class, 'exportZip'])->name('export-zip');
    Route::get('/documents/{document}/audit-trail', [ArchiveController::class, 'auditTrail'])->name('audit-trail');
    Route::get('/{archive}', [ArchiveController::class, 'show'])->name('show');
    Route::post('/{archive}/verify', [ArchiveController::class, 'verify'])->name('verify');
    Route::get('/{archive}/download', [ArchiveController::class, 'download'])->name('download');
});
