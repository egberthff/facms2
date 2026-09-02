<?php 
use App\Http\Controllers\AnnualContributionController;
use App\Http\Controllers\ContributionBatchController;
use App\Http\Controllers\ContributionDetailsController;

Route::middleware(['auth', 'active'])
    ->group(function () {

    //Contributions Monitoring
    // Map to ContributionBatchPolicy::viewAny
    Route::get('contributions/monitoring', [ContributionBatchController::class , 'monitorBatch'])->middleware('can:viewAny,App\\Models\\ContributionBatch')->name('contributions.monitoring');
    Route::post('contributions/details/{id}/mark-paid', [ContributionDetailsController::class, 'pay'])->name('contributions.details.mark-paid');

    Route::post('contributions/annual/create', [AnnualContributionController::class, 'storeBatch'])->name('contributions.create');
    Route::post('contributions/annual-contributions/{id}/mark-paid', [AnnualContributionController::class, 'pay'])->name('contributions.annual.mark-paid');

    });
