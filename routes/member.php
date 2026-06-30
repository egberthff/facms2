<?php 
use App\Http\Controllers\MemberController;

Route::middleware(['auth', 'active'])
    ->group(function () {

        Route::resource('members', MemberController::class);

    });