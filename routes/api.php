<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

include __DIR__ . '/auth/auth.php';
include __DIR__ . '/laundry/customers.php';
include __DIR__ . '/laundry/baserates.php';
include __DIR__ . '/laundry/specialservices.php';
include __DIR__ . '/laundry/transactions.php';
