<?php

use App\Http\Controllers\Api\{LoginController, ProductController};
use Illuminate\Support\Facades\Route;

Route::post('/login', LoginController::class);

Route::middleware('auth:sanctum')->group(function () {

    Route::apiResource('products', ProductController::class);
});
