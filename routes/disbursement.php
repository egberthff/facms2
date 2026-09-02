<?php 

use App\Http\Controllers\DisbursementController;

Route::middleware(['auth', 'active'])
    ->group(function () {

    // Use ClaimPayment policy abilities: viewAny for listing, view for single item
    Route::get('disbursements/list', [DisbursementController::class, 'index'])->middleware('can:view disbursement,App\\Models\\ClaimPayment')->name('disbursement.index');
    Route::get('disbursements/{claimPayment}', [DisbursementController::class, 'show'])->middleware('can:view,claimPayment')->name('disbursement.show');

    });