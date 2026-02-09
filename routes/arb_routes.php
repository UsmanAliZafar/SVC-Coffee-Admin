<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ARBPaymentController;

/*
|--------------------------------------------------------------------------
| ARB Payment Gateway Routes
|--------------------------------------------------------------------------
*/

Route::prefix('arb')->name('arb.')->group(function () {

    // Display payment form
    Route::get('/payment', [ARBPaymentController::class, 'showPaymentForm'])
        ->name('payment.form');

    // Initiate payment
    Route::post('/payment/initiate', [ARBPaymentController::class, 'initiatePayment'])
        ->name('payment.initiate');

    // Payment callback (response from gateway)
    Route::match(['get', 'post'], '/callback', [ARBPaymentController::class, 'handleCallback'])
        ->name('payment.callback');

    // Payment error handler
    Route::match(['get', 'post'], '/error', [ARBPaymentController::class, 'handleError'])
        ->name('payment.error');

    // Webhook endpoint (for async notifications)
    Route::post('/webhook', [ARBPaymentController::class, 'handleWebhook'])
        ->name('payment.webhook');

    // Transaction verification
    Route::post('/verify', [ARBPaymentController::class, 'verifyTransaction'])
        ->name('payment.verify');

    // Process refund (requires authentication)
    Route::post('/refund', [ARBPaymentController::class, 'processRefund'])
        ->name('payment.refund');
        // ->middleware(['auth']); // Uncomment if you have authentication

    // Payment status pages
    Route::get('/payment/success', [ARBPaymentController::class, 'paymentSuccess'])
        ->name('payment.success');

    Route::get('/payment/failed', [ARBPaymentController::class, 'paymentFailed'])
        ->name('payment.failed');

    Route::get('/payment/error', [ARBPaymentController::class, 'paymentError'])
        ->name('payment.error.page');
});
