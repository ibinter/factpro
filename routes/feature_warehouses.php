<?php

use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\WarehouseTransferController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'license'])->group(function () {
    Route::resource('entrepots', WarehouseController::class)->names('warehouses');
    Route::post('entrepots/{warehouse}/ajuster-stock', [WarehouseController::class, 'adjustStock'])->name('warehouses.adjust-stock');

    Route::resource('transferts-stock', WarehouseTransferController::class)->names('warehouse-transfers');
    Route::post('transferts-stock/{warehouseTransfer}/expedition', [WarehouseTransferController::class, 'ship'])->name('warehouse-transfers.ship');
    Route::post('transferts-stock/{warehouseTransfer}/reception', [WarehouseTransferController::class, 'receive'])->name('warehouse-transfers.receive');
});
