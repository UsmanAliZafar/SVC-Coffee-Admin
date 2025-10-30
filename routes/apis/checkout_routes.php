<?php
// routes/api/checkout_routes.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CheckoutController;

Route::prefix('checkout')->name('checkout.')->group(function () {

    // Create order from cart
    Route::post('/create-order', [CheckoutController::class, 'createOrder'])->name('create-order');

    // Optional: Calculate shipping (if you want to add this method)
    // Route::post('/calculate-shipping', [CheckoutController::class, 'calculateShipping'])->name('calculate-shipping');

    // Optional: Validate coupon (if you want to add this method)
    // Route::post('/validate-coupon', [CheckoutController::class, 'validateCoupon'])->name('validate-coupon');

    // Optional: Get checkout totals preview (if you want to add this method)
    // Route::post('/preview', [CheckoutController::class, 'preview'])->name('preview');
});
