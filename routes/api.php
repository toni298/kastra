<?php

use App\Http\Controllers\HR\AttendanceController;
use App\Http\Controllers\HR\CommissionController;
use App\Http\Controllers\HR\OvertimeController;
use App\Http\Controllers\HR\PayrollController;
use App\Http\Controllers\HR\PayrollItemController;
use App\Http\Controllers\HR\ShiftController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified', 'onboarded', 'company.context', 'cashier.only', 'throttle:60,1'])
   ->prefix('hr')
   ->name('api.hr.')
   ->group(function (): void {
      Route::get('attendances', [AttendanceController::class, 'index'])
         ->middleware('can:hr.attendance.view')
         ->name('attendances.index');
      Route::put('attendances/{attendance}', [AttendanceController::class, 'update'])
         ->middleware('can:hr.attendance.edit')
         ->name('attendances.update');
      Route::get('shifts/{shift}/employees', [ShiftController::class, 'employees'])
         ->middleware('can:hr.attendance.view')
         ->name('shifts.employees');
      Route::apiResource('shifts', ShiftController::class)
         ->except(['show'])
         ->middleware('can:hr.shifts.manage');
      Route::get('commissions', [CommissionController::class, 'index'])->middleware('can:hr.commissions.view')->name('commissions.index');
      Route::get('commissions/{employee}/details', [CommissionController::class, 'details'])->middleware('can:hr.commissions.view')->name('commissions.details');
      Route::get('overtimes', [OvertimeController::class, 'index'])->middleware('can:hr.overtime.view')->name('overtimes.index');
      Route::post('overtimes', [OvertimeController::class, 'store'])->middleware('can:hr.overtime.create')->name('overtimes.store');
      Route::put('overtimes/{overtime}/status', [OvertimeController::class, 'updateStatus'])->middleware('can:hr.overtime.approve')->name('overtimes.status');
      Route::get('payrolls', [PayrollController::class, 'index'])->middleware('can:hr.payroll.view')->name('payrolls.index');
      Route::post('payrolls/generate', [PayrollController::class, 'generate'])->middleware('can:hr.payroll.generate')->name('payrolls.generate');
      Route::post('payrolls/{payroll}/post', [PayrollController::class, 'postToJournal'])->middleware('can:hr.payroll.post')->name('payrolls.post');
      Route::get('payrolls/{payroll}/items', [PayrollItemController::class, 'index'])->middleware('can:hr.payroll.view')->name('payrolls.items.index');
      Route::get('payroll-items/{item}/print', [PayrollItemController::class, 'printPdf'])->middleware('can:hr.payroll.view')->name('payroll-items.print');
   });
