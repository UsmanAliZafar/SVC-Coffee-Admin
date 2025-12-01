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
        Route::patch('/{id}', [VendorsController::class, 'update'])->name('update.patch');

        // Sync Individual Vendor (NEW ROUTE)
        Route::post('/{id}/sync', [VendorsController::class, 'syncIndividual'])->name('sync-individual');
    });

    // Sync Routes (requires read permission)
    Route::middleware('admin.permission:vendors.read')->group(function () {
        Route::post('/sync-products-count', [VendorsController::class, 'syncProductsCount'])->name('sync-products-count');
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
