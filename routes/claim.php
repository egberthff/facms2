<?php 
use App\Http\Controllers\ClaimController;

Route::middleware(['auth', 'active'])
    ->group(function () {

        //Routes for claims
        // Route::resource('claims', ClaimController::class)->middleware('can:viewAny,claim');
        //Member claim routes
        // Uses ClaimPolicy::viewMyClaims(User $user, User $targetUser)
        Route::get('claims/my-list/{user}', [ClaimController::class, 'myClaims'])->middleware('can:view own claims,user')->name('claim.my');
        // Use standard ability names to map to ClaimPolicy::create
        Route::get('claims/create', [ClaimController::class, 'create'])->middleware('can:create,App\\Models\\Claim')->name('claim.create');
        Route::post('claims/store', [ClaimController::class, 'store'])->middleware('can:create,App\\Models\\Claim')->name('claim.store');
        
        //Admin claim routes
        Route::put('claims/{claim}', [ClaimController::class, 'update'])->middleware('can:update,claim')->name('claim.update');
        // Lists should use the `viewAny` ability on the model
        Route::get('claims/list', [ClaimController::class, 'index'])->middleware('can:viewAny,App\\Models\\Claim')->name('claim.list');


    });