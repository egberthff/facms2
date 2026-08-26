<?php

use App\Http\Controllers\ReportController;

Route::middleware(['auth', 'active'])
    ->group(function () {

    //Report Admin
    Route::get('reports/index', [ReportController::class, 'index'])->middleware('can:view reports')->name('reports.index');
    Route::post('reports/generate', [ReportController::class, 'generate'])->middleware('can:view reports')->name('reports.generate');
    });
