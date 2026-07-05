<?php 
use App\Http\Controllers\MemberController;

Route::middleware(['auth', 'active'])
    ->group(function () {

        Route::resource('members', MemberController::class)->middleware('can:viewAny,App\Models\Member');
        // Route::post('members/add-new-member', [MemberController::class, 'store'])->middleware('can:create,App\Models\Member')->name('members.store');
        Route::put('members/{member}', [MemberController::class, 'update'])->middleware('can:update,member')->name('members.update');

    });
