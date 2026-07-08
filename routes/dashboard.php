<?php
use App\Http\Controllers\DashboardController;

Route::middleware(['auth', 'active'])
->group(function () {

    //Routes for Balances                                              
    // Route::get('member/my-balance', [DashboardContorller::class, 'memberBalance'])->middleware('can: view member metrics')->name('balance.my-metrics');
    Route::get('member/my-balance', [DashboardController::class, 'memberBalance'])
    ->middleware('can:view member metrics') // Note: Removed the extra space after the colon
    ->name('balance.my-metrics');

});