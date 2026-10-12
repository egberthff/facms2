<?php

use App\Http\Controllers\ReportController;

Route::middleware(['auth', 'active', 'verified', 'member.active'])
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

    // Campus Cash Flow (saved per-campus periods)
    Route::middleware('can:view reports')
        ->prefix('reports/cash-flow')
        ->name('reports.cash-flow.')
        ->controller(\App\Http\Controllers\CashFlowController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::put('items/{item}', 'updateItem')->name('items.update');
            Route::delete('items/{item}', 'destroyItem')->name('items.destroy');
            Route::get('{period}', 'show')->name('show');
            Route::delete('{period}', 'destroy')->name('destroy');
            Route::get('{period}/print', 'print')->name('print');
            Route::post('{period}/refresh', 'refresh')->name('refresh');
            Route::put('{period}/opening-balance', 'updateOpeningBalance')->name('opening-balance');
            Route::post('{period}/finalize', 'finalize')->name('finalize');
            Route::post('{period}/items', 'storeItem')->name('items.store');
        });
    });

    Route::get('reports/faculty-deductions-summary', [ReportController::class, 'facultyDeductionsSummaryView'])
        ->name('reports.faculty-deduction-summary');

    Route::get('reports/medical-assistance-report', [ReportController::class, 'medicalAssistanceReportView'])
        ->name('reports.medical-assistance-report');    
  
    Route::get('reports/monthly-claims-summary', [ReportController::class, 'monthlyClaimsSummaryReportView'])
        ->name('reports.monthly-claims-summary');

    Route::get('reports/generate-report', [ReportController::class, 'unifiedReportView'])
        ->name('reports.unified-report-url');
