<?php 

use App\Http\Controllers\DisbursementController;

Route::middleware(['auth', 'active'])
    ->group(function () {

    Route::get('disbursements/list', [DisbursementController::class, 'index'])->middleware('can:view disbursment, disbursment')->name('disbursement.index');
    Route::get('disbursements/{claimPayment}', [DisbursementController::class, 'show'])->middleware('can:view disbursment, disbursment')->name('disbursement.show');

    });