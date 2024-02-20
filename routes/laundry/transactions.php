<?php

use App\Http\Controllers\Laundry\TransactionController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')
    ->prefix('laundry')
    ->group(function () {
        Route::get('transactions', [TransactionController::class, 'index']);
        Route::post('transactions', [TransactionController::class, 'store']);
        Route::get('transactions/{id}', [TransactionController::class, 'find']);
        Route::put('transactions/{id}', [TransactionController::class, 'update']);
        Route::delete('transactions/{id}', [TransactionController::class, 'delete']);
    });