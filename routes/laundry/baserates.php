<?php

use App\Http\Controllers\Laundry\BaseRateController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')
    ->prefix('laundry')
    ->group(function () {
        Route::get('base-rates', [BaseRateController::class, 'index']);
        Route::post('base-rates', [BaseRateController::class, 'store']);
        Route::get('base-rates/{id}', [BaseRateController::class, 'find']);
        Route::put('base-rates/{id}', [BaseRateController::class, 'update']);
        Route::delete('base-rates/{id}', [BaseRateController::class, 'delete']);
    });