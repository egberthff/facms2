<?php 

use App\Http\Controllers\DisbursementController;

Route::middleware(['auth', 'active'])
    ->group(function () {

    Route::get('disbursements/list', [DisbursementController::class, 'index'])->middleware('can:view disbursment, disbursment')->name('disbursement.index');

    });