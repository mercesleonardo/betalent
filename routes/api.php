<?php

use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\{GatewayController, LoginController, ProductController, UserController};
use Illuminate\Support\Facades\Route;

Route::post('/login', LoginController::class);
Route::post('/checkout', CheckoutController::class);

Route::middleware('auth:sanctum')->group(function () {

    Route::apiResource('users', UserController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('gateways', GatewayController::class);
});
