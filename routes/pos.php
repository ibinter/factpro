<?php

// Module POS / Caisse tactile (cahier des charges §7)
use App\Http\Controllers\PosController;
use App\Http\Controllers\PosTableController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'license'])->group(function () {
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos/session/open', [PosController::class, 'openSession'])->name('pos.session.open');
    Route::post('/pos/session/close', [PosController::class, 'closeSession'])->name('pos.session.close');
    Route::post('/pos/checkout', [PosController::class, 'checkout'])
        ->middleware('throttle:60,1')->name('pos.checkout');
    Route::get('/pos/report/{session}', [PosController::class, 'report'])->name('pos.report');

    // Tables restaurant
    Route::get('/pos/tables', [PosTableController::class, 'index'])->name('pos.tables.index');
    Route::post('/pos/tables', [PosTableController::class, 'store'])->name('pos.tables.store');
    Route::put('/pos/tables/{table}', [PosTableController::class, 'update'])->name('pos.tables.update');
    Route::delete('/pos/tables/{table}', [PosTableController::class, 'destroy'])->name('pos.tables.destroy');
    Route::post('/pos/tables/{table}/order', [PosTableController::class, 'assignOrder'])->name('pos.tables.order');
    Route::post('/pos/tables/{table}/free', [PosTableController::class, 'freeTable'])->name('pos.tables.free');

    // Rapport X (intermédiaire intraday, sans clôture)
    Route::get('/pos/sessions/{session}/report-x', [PosController::class, 'reportX'])->name('pos.session.reportX');
});
