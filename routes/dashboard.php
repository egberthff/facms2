<?php
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    if (Auth::guard('web')->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});

Route::middleware(['auth', 'active'])
->group(function () {
    //Routes for Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    //Routes for Balances                                              
    // Route::get('member/my-balance', [DashboardContorller::class, 'memberBalance'])->middleware('can: view member metrics')->name('balance.my-metrics');
    Route::get('member/my-balance', [DashboardController::class, 'memberBalance'])->middleware('can:view member metrics')->name('balance.my-metrics');

});