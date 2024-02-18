<?php

use App\Http\Controllers\Laundry\CunstomerController;
use Illuminate\Support\Facades\Route;


Route::prefix('laundry')->group(function () {
    Route::get('customers', [CunstomerController::class, 'index']);
    Route::post('customers', [CunstomerController::class, 'store']);
    Route::get('customers/{id}', [CunstomerController::class, 'find']);
    Route::delete('customers/{id}', [CunstomerController::class, 'delete']);
    Route::put('customers/{id}', [CunstomerController::class, 'update']);
})->middleware('auth:sanctum');