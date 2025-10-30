<?php
// routes/apis/orders_routes.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OrdersController;

Route::prefix('orders')->name('orders.')->group(function () {
    // Get customer orders (requires authentication)
    Route::get('/', [OrdersController::class, 'index'])->name('index');

    // Get single order by order number
    Route::get('/{orderNumber}', [OrdersController::class, 'show'])->name('show');

    // Track order status
    Route::get('/{orderNumber}/track', [OrdersController::class, 'track'])->name('track');

    // Cancel order
    Route::post('/{orderNumber}/cancel', [OrdersController::class, 'cancel'])->name('cancel');

    // Request return/refund
    Route::post('/{orderNumber}/return', [OrdersController::class, 'requestReturn'])->name('return');
});
