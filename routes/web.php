<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;

// Route::middleware(['auth', 'active'])->group(function () {
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// });

require __DIR__.'/auth.php';
require __DIR__.'/member.php';
require __DIR__.'/claim.php';
require __DIR__.'/finance.php';
require __DIR__.'/report.php';
require __DIR__.'/setting.php';
require __DIR__.'/dashboard.php';
require __DIR__.'/disbursement.php';
