<?php

use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active'])
    ->group(function () {
        Route::get('settings', [SettingController::class, 'index'])
            ->name('settings.index');

        Route::put('settings/{setting}', [SettingController::class, 'update'])
            ->name('settings.update');
    });
