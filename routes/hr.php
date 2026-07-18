<?php

use App\Http\Controllers\HrController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'license'])->prefix('hr')->name('hr.')->group(function () {
    Route::get('/', [HrController::class, 'index'])->name('index');
    Route::post('/employees', [HrController::class, 'storeEmployee'])->name('employees.store');
    Route::put('/employees/{employee}', [HrController::class, 'updateEmployee'])->name('employees.update');
    Route::post('/payroll/generate', [HrController::class, 'generatePayroll'])->name('payroll.generate');
    Route::get('/payslips', [HrController::class, 'payslips'])->name('payslips.index');
    Route::get('/payslips/{payslip}', [HrController::class, 'showPayslip'])->name('payslips.show');
    Route::get('/payslips/{payslip}/pdf', [HrController::class, 'pdfPayslip'])->name('payslips.pdf');
    Route::post('/payslips/{payslip}/validate', [HrController::class, 'validatePayslip'])->name('payslips.validate');
    Route::post('/payroll/mass-action', [HrController::class, 'massAction'])->name('payroll.mass-action');
});
