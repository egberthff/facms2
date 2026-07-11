<?php

use App\Http\Controllers\ContributionBatchController;
use App\Http\Controllers\ContributionDetailsController;

Route::middleware(['auth', 'active'])
    ->group(function () {

    //Contributions Monitoring
    Route::get('contributions/monitoring', [ContributionBatchController::class , 'monitorBatch'])->middleware('can:view all contribution, contributionBatch')->name('contributions.monitoring');
    Route::post('contributions/details/{id}/mark-paid', [ContributionDetailsController::class, 'pay'])->name('contributions.details.mark-paid');

    });
