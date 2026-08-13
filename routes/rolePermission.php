<?php

use App\Http\Controllers\RolePermissionController;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/access-control', [RolePermissionController::class, 'index'])->middleware('can:manage users')->name('permissions.index');
    //Role based access assignment
    Route::put('/access-control/sync', [RolePermissionController::class, 'syncMatrix'])->middleware('can:manage users')->name('permissions.sync');
    //Fine grained User base access assignment
    Route::put('/user-access/sync', [RolePermissionController::class, 'syncUserPermissions'])->middleware('can:manage users')->name('users.sync-permissions');
    // Data fetching route for user
    Route::get('/user-access/{user}/permissions', [RolePermissionController::class, 'getUserPermissions'])->middleware('can:manage users')->name('users.get-permissions');
});
