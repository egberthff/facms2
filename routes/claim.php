<?php 
use App\Http\Controllers\ClaimController;

Route::middleware(['auth', 'active'])
    ->group(function () {

        // Route::resource('claims', ClaimController::class)->middleware('can:viewAny,claim');
        Route::get('claims/submit-claim', [ClaimController::class, 'create'])->middleware('can:create claims, claim')->name('claim.create');
        // Route::get('claims/submit-claim', [ClaimController::class, 'create'])->name('claim.create');
        Route::post('claims/add-new-claim', [ClaimController::class, 'store'])->middleware('can:create claims, claim')->name('claim.store');
        Route::put('claims/{claim}', [ClaimController::class, 'update'])->middleware('can:update claims, claim')->name('claim.update');

    });