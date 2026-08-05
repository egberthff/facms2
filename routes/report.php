<?php

use App\Http\Controllers\ContributionBatchController;
use App\Http\Controllers\ContributionDetailsController;
use App\Http\Controllers\ReportController;

Route::middleware(['auth', 'active'])
    ->group(function () {

    //Contributions Monitoring
    // Map to ContributionBatchPolicy::viewAny
    Route::get('contributions/monitoring', [ContributionBatchController::class , 'monitorBatch'])->middleware('can:viewAny,App\\Models\\ContributionBatch')->name('contributions.monitoring');
    Route::post('contributions/details/{id}/mark-paid', [ContributionDetailsController::class, 'pay'])->name('contributions.details.mark-paid');

    //Report Admin
    Route::get('reports/index', [ReportController::class, 'index'])->middleware('can:view reports')->name('reports.index');
    Route::post('reports/generate', [ReportController::class, 'generate'])->middleware('can:view reports')->name('reports.generate');
    });
