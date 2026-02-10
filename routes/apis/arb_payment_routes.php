<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ARBCheckoutController;

/*
|--------------------------------------------------------------------------
| ARB Payment Gateway API Routes
|--------------------------------------------------------------------------
| Pure REST API - No redirects, JSON responses only
*/

Route::prefix('arb')->name('arb.')->group(function () {

    // Initiate ARB payment
    Route::post('/payment/initiate', [ARBCheckoutController::class, 'initiatePayment'])
        ->name('initiate');

    // ARB callback
    // Route::post('/callback', [ARBCheckoutController::class, 'handleCallback'])
    //     ->name('callback');
    Route::match(['get', 'post'], '/callback', [ARBCheckoutController::class, 'handleCallback'])
        ->name('callback');

    // Check payment status
    Route::get('/status/{track_id}', [ARBCheckoutController::class, 'checkStatus'])
        ->name('status');
});
