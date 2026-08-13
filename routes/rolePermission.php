<?php

use App\Http\Controllers\RolePermissionController;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/access-control', [RolePermissionController::class, 'index'])->name('permissions.index');
    Route::put('/access-control/sync', [RolePermissionController::class, 'syncMatrix'])->name('permissions.sync');
});
