<?php

use App\Http\Controllers\Laundry\SpecialServiceController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')
    ->prefix('laundry')
    ->group(function() {
        Route::get('special-services', [SpecialServiceController::class, 'index']);
        Route::post('special-services', [SpecialServiceController::class, 'store']);
        Route::get('special-services/{id}', [SpecialServiceController::class, 'find']);
        Route::put('special-services/{id}', [SpecialServiceController::class, 'update']);
        Route::delete('special-services/{id}', [SpecialServiceController::class, 'delete']);
    });