<?php
// routes/admin/vendors_routes.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\VendorsController;

/*
|--------------------------------------------------------------------------
| Vendors Management Routes
|--------------------------------------------------------------------------
*/

Route::prefix('vendors')->name('vendors.')->group(function () {

    // Read/View Routes
    Route::middleware('admin.permission:vendors.read')->group(function () {
        Route::get('/', [VendorsController::class, 'index'])->name('index');
        Route::get('/data', [VendorsController::class, 'getData'])->name('data');
        Route::get('/statistics', [VendorsController::class, 'statistics'])->name('statistics');
        Route::post('/sync-products-count', [VendorsController::class, 'syncProductsCount'])->name('sync-products-count');
    });

    // Create Routes (MUST be before /{id} routes)
    Route::middleware('admin.permission:vendors.create')->group(function () {
        Route::get('/create', [VendorsController::class, 'create'])->name('create');
        Route::post('/', [VendorsController::class, 'store'])->name('store');
    });

    // Update Routes (MUST be before /{id} routes)
    Route::middleware('admin.permission:vendors.update')->group(function () {
        Route::get('/{id}/edit', [VendorsController::class, 'edit'])->name('edit');
        Route::put('/{id}', [VendorsController::class, 'update'])->name('update');
        Route::post('/{id}/toggle-verified', [VendorsController::class, 'toggleVerified'])->name('toggle-verified');
        Route::post('/{id}/toggle-featured', [VendorsController::class, 'toggleFeatured'])->name('toggle-featured');
    });

    // Delete Routes
    Route::middleware('admin.permission:vendors.delete')->group(function () {
        Route::delete('/{id}', [VendorsController::class, 'destroy'])->name('destroy');
        Route::post('/bulk-delete', [VendorsController::class, 'bulkDelete'])->name('bulk-delete');
    });

    // Show Route (MUST be LAST because it has /{id})
    Route::middleware('admin.permission:vendors.read')->group(function () {
        Route::get('/{id}', [VendorsController::class, 'show'])->name('show');
    });
});
