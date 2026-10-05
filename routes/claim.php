<?php 
use App\Http\Controllers\ClaimController;

Route::middleware(['auth', 'active', 'verified', 'member.active'])
    ->group(function () {

        // 1. Member claim listings (Maps to ClaimPolicy::viewMyClaims)
        Route::get('claims/my-list/{user}', [ClaimController::class, 'myClaims'])
            ->middleware('can:viewMyClaims,App\\Models\\Claim')
            ->name('claim.my');

        // 2. Submit & Store Claims (Maps to ClaimPolicy::create)
        Route::get('claims/create', [ClaimController::class, 'create'])
            ->middleware('can:create,App\\Models\\Claim')
            ->name('claim.create');
            
        Route::post('claims/store', [ClaimController::class, 'store'])
            ->middleware('can:create,App\\Models\\Claim')
            ->name('claim.store');
        
        // 3. Admin update (Maps to ClaimPolicy::update)
        Route::put('claims/{claim}', [ClaimController::class, 'update'])
            ->middleware('can:update,claim')
            ->name('claim.update');

        // 4. Admin claims listing dashboard (Maps to ClaimPolicy::viewAny)
        Route::get('claims/list', [ClaimController::class, 'index'])
            ->middleware('can:viewAny,App\\Models\\Claim')
            ->name('claim.list');
        
        Route::get('claims/running-balance/{user}', [ClaimController::class, 'getRunningBalance'])
            ->name('claim.running-balance');

        // 5. Printable FCFA medical claim forms (Forms 1-4)
        Route::get('claims/{claim}/forms', [ClaimController::class, 'printForms'])
            ->middleware('can:printForms,claim')
            ->name('claim.forms');

    });


// Route::middleware(['auth', 'active'])
//     ->group(function () {

//         //Routes for claims
//         // Route::resource('claims', ClaimController::class)->middleware('can:viewAny,claim');
//         //Member claim routes
//         // Uses ClaimPolicy::viewMyClaims(User $user, User $targetUser)
//         // Route::get('claims/my-list/{user}', [ClaimController::class, 'myClaims'])->middleware('can:view own claims,user')->name('claim.my');
//         Route::get('claims/my-list/{user}', [ClaimController::class, 'myClaims'])->middleware('can:view own claims')->name('claim.my');
//         // Use standard ability names to map to ClaimPolicy::create
//         Route::get('claims/create', [ClaimController::class, 'create'])->middleware('can:create,App\\Models\\Claim')->name('claim.create');
//         Route::post('claims/store', [ClaimController::class, 'store'])->middleware('can:create,App\\Models\\Claim')->name('claim.store');
//         // New (Fixed - Matches your 'create claims' permission string):
//         // Route::get('claims/create', [ClaimController::class, 'create'])
//         //     ->middleware('can:create claims')
//         //     ->name('claim.create');

//         // Route::post('claims/store', [ClaimController::class, 'store'])
//         //     ->middleware('can:create claims')
//         //     ->name('claim.store');
//         //Admin claim routes
//         Route::put('claims/{claim}', [ClaimController::class, 'update'])->middleware('can:update,claim')->name('claim.update');
//         // Lists should use the `viewAny` ability on the model
//         Route::get('claims/list', [ClaimController::class, 'index'])->middleware('can:viewAny,App\\Models\\Claim')->name('claim.list');
//         // Route::get('claims/list', [ClaimController::class, 'index'])
//         //     ->middleware('can:view all claims')
//         //     ->name('claim.list');


//     });