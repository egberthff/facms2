<?php 
use App\Http\Controllers\AnnualContributionController;
use App\Http\Controllers\ContributionBatchController;
use App\Http\Controllers\ContributionDetailsController;
use App\Http\Controllers\CNAShareController;
use App\Http\Controllers\MemberController;

Route::middleware(['auth', 'active', 'verified', 'member.active'])
    ->group(function () {

    //Contributions Monitoring
    // Map to ContributionBatchPolicy::viewAny
    Route::get('contributions/monitoring', [ContributionBatchController::class , 'monitorBatch'])->middleware('can:viewAny,App\\Models\\ContributionBatch')->name('contributions.monitoring');
    Route::post('contributions/details/{id}/mark-paid', [ContributionDetailsController::class, 'pay'])->name('contributions.details.mark-paid');

    Route::post('contributions/annual/create', [AnnualContributionController::class, 'storeBatch'])->name('contributions.create');
    Route::post('contributions/annual-contributions/{id}/mark-paid', [AnnualContributionController::class, 'pay'])->name('contributions.annual.mark-paid');
    Route::post('contributions/cna-share/{id}/mark-paid', [CNAShareController::class, 'pay'])->name('contributions.cna-share.mark-paid');
    Route::post('contributions/membership-fee/{id}/mark-paid', [MemberController::class, 'pay'])->name('contibutions.membership-fee.mark-paid');

    Route::post('contributions/contribution-adjustment', [ContributionDetailsController::class, 'contributionAdjustment'])->name('contributions.contribution-adjustment');
    Route::post('contributions/cna-share-setup', [CNAShareController::class, 'create'])->name('contributions.cna-share-setup');

    });
