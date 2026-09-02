<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Mail\ClaimSubmittedAlert;

// Route::middleware(['auth', 'active'])->group(function () {
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// });

// Route::get('/send-test-email', function () {
//     $recipient = 'president@bisu.edu.ph';
//     $memberName = 'Test Account';
//     $claimNumber = 'CLM-09022026-01';
//     $claimType = 'Outpatient';
//     $amount = '2000';
//     $submissionDate = now(); 

//     Mail::to($recipient)->send(new ClaimSubmittedAlert($memberName, $claimType, $claimNumber, $amount, $submissionDate));

//     return 'Email sent successfully!';
// });

require __DIR__.'/auth.php';
require __DIR__.'/member.php';
require __DIR__.'/claim.php';
require __DIR__.'/finance.php';
require __DIR__.'/report.php';
require __DIR__.'/setting.php';
require __DIR__.'/dashboard.php';
require __DIR__.'/disbursement.php';
require __DIR__.'/banking.php';
require __DIR__.'/rolePermission.php';
require __DIR__.'/contribution.php';