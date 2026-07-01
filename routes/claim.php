<?php 
use App\Http\Controllers\ClaimController;

Route::middleware(['auth', 'active'])
    ->group(function () {

        Route::resource('claims', ClaimController::class)->middleware('can:viewAny,claim');
        Route::post('claims/add-new-claim', [ClaimController::class, 'store'])->middleware('can:create,claim')->name('claim.store');
        Route::put('claims/{claim}', [ClaimController::class, 'update'])->middleware('can:update,claim')->name('claim.update');

    });