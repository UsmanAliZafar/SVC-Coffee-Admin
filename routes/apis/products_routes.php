<?php
// routes/apis/products_routes.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductsController;

Route::prefix('products')->name('products.')->group(function () {


    // Search products (must come before /{id})
    Route::get('/search', [ProductsController::class, 'search'])->name('search');

    // Get product by SKU (must come before /{id})
    Route::get('/sku/{sku}', [ProductsController::class, 'getBySku'])->name('by_sku');

    // Get product by slug (must come before /{id})
    Route::get('/slug/{slug}', [ProductsController::class, 'getBySlug'])->name('by_slug');

    // Featured, homepage, on-sale (must come before /{id})
    Route::get('/featured', [ProductsController::class, 'featured'])->name('featured');
    Route::get('/homepage', [ProductsController::class, 'homepage'])->name('homepage');
    Route::get('/on-sale', [ProductsController::class, 'onSale'])->name('on_sale');

    // Stock management routes (must come before /{id})
    Route::prefix('stock')->name('stock.')->group(function () {
        Route::get('/levels', [ProductsController::class, 'stockLevels'])->name('levels');
        Route::get('/low-stock', [ProductsController::class, 'lowStock'])->name('low_stock');
        Route::post('/adjust/{id}', [ProductsController::class, 'adjustStock'])->name('adjust');
    });

    // Bulk operations (must come before /{id})
    Route::prefix('bulk')->name('bulk.')->group(function () {
        Route::post('/update-stock', [ProductsController::class, 'bulkUpdateStock'])->name('update_stock');
        Route::post('/update-prices', [ProductsController::class, 'bulkUpdatePrices'])->name('update_prices');
        Route::post('/import', [ProductsController::class, 'bulkImport'])->name('import');
    });

    // List all products (with pagination and filters)
    Route::get('/', [ProductsController::class, 'index'])->name('index');

    // NOW put the generic routes at the END
    // Get single product by ID
    Route::get('/{id}', [ProductsController::class, 'show'])->name('show');

    // Create product
    Route::post('/', [ProductsController::class, 'store'])->name('store');

    // Update product
    Route::put('/{id}', [ProductsController::class, 'update'])->name('update');

    // Delete product
    Route::delete('/{id}', [ProductsController::class, 'destroy'])->name('destroy');

    // Product variants
    Route::prefix('{id}/variants')->name('variants.')->group(function () {
        Route::get('/', [ProductsController::class, 'getVariants'])->name('index');
        Route::post('/', [ProductsController::class, 'addVariant'])->name('store');
        Route::put('/{variantId}', [ProductsController::class, 'updateVariant'])->name('update');
        Route::delete('/{variantId}', [ProductsController::class, 'destroyVariant'])->name('destroy');
    });
});
