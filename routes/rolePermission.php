<?php

use App\Http\Controllers\RolePermissionController;

Route::middleware(['auth', 'active', 'verified', 'member.active'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/access-control', [RolePermissionController::class, 'index'])->middleware('can:manage users')->name('permissions.index');
    //Role based access assignment
    Route::put('/access-control/sync', [RolePermissionController::class, 'syncMatrix'])->middleware('can:manage users')->name('permissions.sync');
    //Fine grained User base access assignment
    Route::put('/user-access/sync', [RolePermissionController::class, 'syncUserAccess'])->middleware('can:manage users')->name('users.sync-access');
    // Data fetching route for user
    Route::get('/user-access/{user}/access', [RolePermissionController::class, 'getUserAccess'])->middleware('can:manage users')->name('users.get-access');
});
