<?php

use App\Http\Controllers\ReportController;

Route::middleware(['auth', 'active'])
    ->group(function () {

    //Report Admin
    Route::get('reports/index', [ReportController::class, 'index'])->middleware('can:view reports')->name('reports.index');
    Route::post('reports/generate', [ReportController::class, 'generate'])->middleware('can:view reports')->name('reports.generate');
    Route::get('reports/payroll-deduction-request', [ReportController::class, 'payrollDeductionRequest'])
        ->middleware('can:view reports')
        ->name('reports.payroll-deduction-request');

    Route::get('reports/collection-summary', [\App\Http\Controllers\CollectionSummaryController::class, 'index'])
        ->middleware('can:view reports')
        ->name('reports.collection-summary');

    Route::get('reports/collection-summary/print', [\App\Http\Controllers\CollectionSummaryController::class, 'print'])
        ->middleware('can:view reports')
        ->name('reports.collection-summary.print');

    Route::post('reports/collection-summary', [\App\Http\Controllers\CollectionSummaryController::class, 'store'])
        ->middleware('can:view reports')
        ->name('reports.collection-summary.store');

    Route::post('reports/fund-transfers/{transfer}/approve', [\App\Http\Controllers\CollectionSummaryController::class, 'approve'])
        ->middleware('can:view reports')
        ->name('reports.fund-transfers.approve');
    });

    Route::get('faculty-deductions-summary', [ReportController::class, 'facultyDeductionsSummaryView'])
        ->name('reports.faculty-deduction-summary');
  
