<?php
// routes/api/cart_routes.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CartController;

Route::prefix('cart')->name('cart.')->group(function () {

    // Get cart contents
    Route::get('/', [CartController::class, 'index'])->name('index');

    // Add item to cart
    Route::post('/items', [CartController::class, 'addItem'])->name('add-item');

    // Update cart item quantity
    Route::put('/items', [CartController::class, 'updateItem'])->name('update-item');

    // Remove item from cart
    Route::delete('/items', [CartController::class, 'removeItem'])->name('remove-item');

    // Clear entire cart
    Route::delete('/clear', [CartController::class, 'clear'])->name('clear');

    // Apply coupon code
    Route::post('/coupon', [CartController::class, 'applyCoupon'])->name('apply-coupon');
    // Remove coupon code
    Route::delete('/coupon', [CartController::class, 'removeCoupon'])->name('remove-coupon');

    // Calculate shipping costs
    Route::post('/calculate-shipping', [CartController::class, 'calculateShipping'])->name('calculate-shipping');

    // Get cart item count (optional - you may want to add this method)
    // Route::get('/count', [CartController::class, 'getCount'])->name('count');
});
