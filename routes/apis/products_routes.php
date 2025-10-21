<?php
// routes/apis/products_routes.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductsController;

/*
|--------------------------------------------------------------------------
| Products API Routes
|--------------------------------------------------------------------------
|
| These routes are already protected by 'api.key' middleware
| from the parent group in api_routes.php
|
*/

Route::prefix('products')->name('products.')->group(function () {

    // List all products (with pagination and filters)
    Route::get('/', [ProductsController::class, 'index'])->name('index');

    // Get single product
    Route::get('/{id}', [ProductsController::class, 'show'])->name('show');

    // Search products
    Route::get('/search/{query}', [ProductsController::class, 'search'])->name('search');

    // Get product by SKU
    Route::get('/sku/{sku}', [ProductsController::class, 'getBySku'])->name('by_sku');

    // Create product
    Route::post('/', [ProductsController::class, 'store'])->name('store');

    // Update product
    Route::put('/{id}', [ProductsController::class, 'update'])->name('update');

    // Delete product
    Route::delete('/{id}', [ProductsController::class, 'destroy'])->name('destroy');

    // Bulk operations
    Route::prefix('bulk')->name('bulk.')->group(function () {
        Route::post('/update-stock', [ProductsController::class, 'bulkUpdateStock'])->name('update_stock');
        Route::post('/update-prices', [ProductsController::class, 'bulkUpdatePrices'])->name('update_prices');
        Route::post('/import', [ProductsController::class, 'bulkImport'])->name('import');
    });

    // Stock management
    Route::prefix('stock')->name('stock.')->group(function () {
        Route::get('/levels', [ProductsController::class, 'stockLevels'])->name('levels');
        Route::get('/low-stock', [ProductsController::class, 'lowStock'])->name('low_stock');
        Route::post('/adjust/{id}', [ProductsController::class, 'adjustStock'])->name('adjust');
    });

    // Product variants
    Route::prefix('{id}/variants')->name('variants.')->group(function () {
        Route::get('/', [ProductsController::class, 'getVariants'])->name('index');
        Route::post('/', [ProductsController::class, 'addVariant'])->name('store');
        Route::put('/{variantId}', [ProductsController::class, 'updateVariant'])->name('update');
        Route::delete('/{variantId}', [ProductsController::class, 'destroyVariant'])->name('destroy');
    });
});
