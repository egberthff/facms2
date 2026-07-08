<?php 
use App\Http\Controllers\ClaimController;

Route::middleware(['auth', 'active'])
    ->group(function () {

        //Routes for claims
        // Route::resource('claims', ClaimController::class)->middleware('can:viewAny,claim');
        Route::get('claims/my-list/{user}', [ClaimController::class, 'myClaims'])->middleware('can:view claims, claim')->name('claim.my');
        Route::get('claims/create', [ClaimController::class, 'create'])->middleware('can:create claims, claim')->name('claim.create');
        Route::post('claims/store', [ClaimController::class, 'store'])->middleware('can:create claims, claim')->name('claim.store');
        Route::put('claims/{claim}', [ClaimController::class, 'update'])->middleware('can:update claims, claim')->name('claim.update');

    });