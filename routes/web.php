<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::middleware(['auth', 'active'])
    ->group(function () {

        Route::get('/dashboard',
            [DashboardController::class,'index'])
            ->name('dashboard');

});
Route::get('/', function () {
    if (Auth::guard('web')->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});


// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
require __DIR__.'/member.php';
require __DIR__.'/claim.php';
require __DIR__.'/finance.php';
require __DIR__.'/report.php';
require __DIR__.'/setting.php';
require __DIR__.'/dashboard.php';
