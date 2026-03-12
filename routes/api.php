<?php

use App\Http\Controllers\Api\{CheckoutController, ClientController, TransactionController};
use App\Http\Controllers\Api\{GatewayController, LoginController, ProductController, UserController};
use Illuminate\Support\Facades\Route;

Route::post('/login', LoginController::class);
Route::post('/checkout', CheckoutController::class);

Route::middleware('auth:sanctum')->group(function () {

    Route::apiResource('users', UserController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('gateways', GatewayController::class);

    Route::apiResource('clients', ClientController::class)->only(['index', 'show']);

    Route::apiResource('transactions', TransactionController::class)->only(['index', 'show']);

    Route::post('transactions/{transaction}/refund', [TransactionController::class, 'refund']);
});
