<?php 
use App\Http\Controllers\ClaimController;

Route::middleware(['auth', 'active'])
    ->group(function () {

        //Routes for claims
        // Route::resource('claims', ClaimController::class)->middleware('can:viewAny,claim');
        //Member claim routes
        Route::get('claims/my-list/{user}', [ClaimController::class, 'myClaims'])->middleware('can:view own claims, claim')->name('claim.my');
        Route::get('claims/create', [ClaimController::class, 'create'])->middleware('can:create claims, claim')->name('claim.create');
        Route::post('claims/store', [ClaimController::class, 'store'])->middleware('can:create claims, claim')->name('claim.store');
        
        //Admin claim routes
        Route::put('claims/{claim}', [ClaimController::class, 'update'])->middleware('can:update claims, claim')->name('claim.update');
        Route::get('claims/list', [ClaimController::class, 'index'])->middleware('can:view all claims, claim')->name('claim.list');


    });