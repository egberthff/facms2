<?php 
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\TreasuryController;

Route::middleware(['auth', 'active', 'verified', 'member.active'])
    ->group(function () {

    Route::get('treasury', [TreasuryController::class, 'index'])->middleware('can:view ledger')->name('treasury.index');

    });