<?php

use App\Http\Controllers\StorefrontController;
use Illuminate\Support\Facades\Route;

Route::prefix('storefront')
    ->middleware('throttle:api')
    ->group(function () {
        Route::get('/categories', [StorefrontController::class, 'categories']);
        Route::get('/products', [StorefrontController::class, 'products']);
        Route::get('/promotions', [StorefrontController::class, 'promotions']);
        Route::post('/checkout', [StorefrontController::class, 'checkout']);
    });
