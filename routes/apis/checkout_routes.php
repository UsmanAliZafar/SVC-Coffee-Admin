<?php
// routes/api/checkout_routes.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CheckoutController;

Route::prefix('checkout')->name('checkout.')->group(function () {

    // Create order from cart
    Route::post('/create-order', [CheckoutController::class, 'createOrder'])->name('create-order');

    // ✅ NEW: Confirm payment (for online payments)
    Route::post('/confirm-payment', [CheckoutController::class, 'confirmPayment'])->name('confirm-payment');

    // ✅ NEW: Handle payment failure
    Route::post('/payment-failed', [CheckoutController::class, 'paymentFailed'])->name('payment-failed');

    // Optional: Calculate shipping
    Route::post('/calculate-shipping', [CheckoutController::class, 'calculateShipping'])->name('calculate-shipping');

    // Optional: Validate coupon
    Route::post('/validate-coupon', [CheckoutController::class, 'validateCoupon'])->name('validate-coupon');

    // Optional: Get checkout totals preview
    Route::post('/preview', [CheckoutController::class, 'preview'])->name('preview');
});
