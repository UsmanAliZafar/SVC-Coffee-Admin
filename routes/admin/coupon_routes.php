<?php
// routes/admin/coupon_routes.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CouponController;

Route::prefix('coupons')->name('coupons.')->middleware(['auth:admin'])->group(function () {

    // List all coupons with filters
    Route::get('/', [CouponController::class, 'index'])->name('index');

    // Generate random coupon code
    Route::get('/generate-code', [CouponController::class, 'generateCode'])->name('generate-code');

    // Get single coupon details
    Route::get('/{id}', [CouponController::class, 'show'])->name('show');

    // Create new coupon
    Route::post('/', [CouponController::class, 'store'])->name('store');

    // Update coupon
    Route::put('/{id}', [CouponController::class, 'update'])->name('update');
    Route::patch('/{id}', [CouponController::class, 'update'])->name('patch');

    // Delete coupon (soft delete)
    Route::delete('/{id}', [CouponController::class, 'destroy'])->name('destroy');

    // Toggle coupon active status
    Route::post('/{id}/toggle-status', [CouponController::class, 'toggleStatus'])->name('toggle-status');

    // Get coupon usage statistics
    Route::get('/{id}/statistics', [CouponController::class, 'statistics'])->name('statistics');

    // Bulk update status
    Route::post('/bulk/update-status', [CouponController::class, 'bulkUpdateStatus'])->name('bulk-update-status');
});
