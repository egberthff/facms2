<?php 
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ContributionDetailsController;

Route::middleware(['auth', 'active'])
    ->group(function () {

        Route::resource('members', MemberController::class)->middleware('can:viewAny,App\Models\Member');
        // Route::post('members/add-new-member', [MemberController::class, 'store'])->middleware('can:create,App\Models\Member')->name('members.store');
        Route::put('members/{member}', [MemberController::class, 'update'])->middleware('can:update,member')->name('members.update');
        Route::get('members/my-contributions/{user}', [ContributionDetailsController::class, 'myOwedContributions'])->middleware('can:view own contribution, member')->name('member.my-contributions');
        Route::get('members/{member}/{type}', [MemberController::class, 'getHistory'])->name('members.history');
    });
