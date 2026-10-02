<?php

use App\Http\Controllers\ClaimTypeController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active'])
    ->group(function () {
        Route::get('settings', [SettingController::class, 'index'])
            ->name('settings.index');

        Route::post('settings/claim-types', [ClaimTypeController::class, 'store'])
            ->name('settings.claim-types.store');

        Route::put('settings/claim-types/{claimType}', [ClaimTypeController::class, 'update'])
            ->name('settings.claim-types.update');

        Route::put('settings/{setting}', [SettingController::class, 'update'])
            ->name('settings.update');
    });
