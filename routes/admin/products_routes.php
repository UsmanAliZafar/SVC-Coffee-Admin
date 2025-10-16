<?php
// routes/admin/products_routes.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ProductsController;

/*
|--------------------------------------------------------------------------
| Products Management Routes
|--------------------------------------------------------------------------
*/

Route::prefix('products')->name('products.')->group(function () {

    // Create product (MUST be before /{id} routes)
    Route::middleware('admin.permission:products.create')->group(function () {
        Route::get('/create', [ProductsController::class, 'create'])->name('create');

        Route::post('/', [ProductsController::class, 'store'])->name('store');

        Route::post('/bulk-import', [ProductsController::class, 'import'])->name('import');

        // Quick add category
        Route::post('/categories/quick-add', [ProductsController::class, 'quickAddCategory'])->name('categories.quick-add');

        // Quick add vendor
        Route::post('/vendors/quick-add', [ProductsController::class, 'quickAddVendor'])->name('vendors.quick-add');

        // Tags search and create
        Route::get('/tags/search', [ProductsController::class, 'searchTags'])->name('tags.search');
        Route::post('/tags/create', [ProductsController::class, 'createTag'])->name('tags.create');

        // Temp image upload/delete (for create page)
        Route::post('/temp-images/upload', [ProductsController::class, 'uploadTempImages'])->name('temp-images.upload');
        Route::post('/temp-images/delete', [ProductsController::class, 'deleteTempImage'])->name('temp-images.delete');

        // Generate session ID
        Route::get('/generate-session-id', [ProductsController::class, 'generateSessionId'])->name('generate-session-id');
    });

    // List and filter routes (specific paths before /{id})
    Route::middleware('admin.permission:products.read')->group(function () {
        Route::get('/', [ProductsController::class, 'index'])->name('index');

        Route::get('/ajax-data', [ProductsController::class, 'getData'])->name('data');

        Route::get('/statistics', [ProductsController::class, 'statistics'])->name('statistics');

        Route::get('/low-stock', [ProductsController::class, 'lowStock'])->name('low-stock');

        Route::get('/inactive', [ProductsController::class, 'inactive'])->name('inactive');

        Route::get('/featured', [ProductsController::class, 'featured'])->name('featured');

        Route::get('/export', [ProductsController::class, 'export'])->name('export');

        Route::get('/get-by-sku', [ProductsController::class, 'getBySku'])->name('get-by-sku');
    });

    // Update routes (specific paths before /{id})
    Route::middleware('admin.permission:products.update')->group(function () {
        Route::post('/bulk-status-update', [ProductsController::class, 'bulkStatusUpdate'])->name('bulk-status-update');

        Route::post('/bulk-stock-update', [ProductsController::class, 'bulkStockUpdate'])->name('bulk-stock-update');
        Route::post('/bulk-toggle-featured', [ProductsController::class, 'bulkToggleFeatured'])->name('bulk-toggle-featured');
        Route::post('/bulk-activate', [ProductsController::class, 'bulkActivate'])->name('bulk-activate');
        Route::post('/update-sort-order', [ProductsController::class, 'updateSortOrder'])->name('update-sort-order');
    });

    // Delete routes (specific paths before /{id})
    Route::middleware('admin.permission:products.delete')->group(function () {
        Route::post('/bulk-delete', [ProductsController::class, 'bulkDelete'])->name('bulk-delete');
    });

    // Dynamic ID routes (MUST be at the end)
    Route::middleware('admin.permission:products.read')->group(function () {
        Route::get('/{id}', [ProductsController::class, 'show'])->name('show')->where('id', '[0-9a-f-]+');

        Route::get('/{id}/variants', [ProductsController::class, 'getVariants'])->name('variants')->where('id', '[0-9a-f-]+');
    });

    Route::middleware('admin.permission:products.create')->group(function () {
        Route::post('/{id}/duplicate', [ProductsController::class, 'duplicate'])->name('duplicate')->where('id', '[0-9a-f-]+');
    });

    Route::middleware('admin.permission:products.update')->group(function () {
        Route::get('/{id}/edit', [ProductsController::class, 'edit'])->name('edit')->where('id', '[0-9a-f-]+');

        Route::put('/{id}', [ProductsController::class, 'update'])->name('update')->where('id', '[0-9a-f-]+');
        Route::post('/{id}/activate', [ProductsController::class, 'activate'])->name('activate')->where('id', '[0-9a-f-]+');
        Route::post('/{id}/toggle-featured', [ProductsController::class, 'toggleFeatured'])->name('toggle-featured')->where('id', '[0-9a-f-]+');

        Route::post('/{id}/toggle-homepage', [ProductsController::class, 'toggleHomepage'])->name('toggle-homepage')->where('id', '[0-9a-f-]+');

        Route::post('/{id}/toggle-publish', [ProductsController::class, 'togglePublish'])->name('toggle-publish')->where('id', '[0-9a-f-]+');

        Route::post('/{id}/quick-edit', [ProductsController::class, 'quickEdit'])->name('quick-edit')->where('id', '[0-9a-f-]+');

        // Product Images
        Route::post('/{id}/images/upload', [ProductsController::class, 'uploadImages'])->name('images.upload')->where('id', '[0-9a-f-]+');

        Route::delete('/{productId}/images/{imageId}', [ProductsController::class, 'deleteImage'])->name('images.delete')
            ->where(['productId' => '[0-9a-f-]+', 'imageId' => '[0-9a-f-]+']);

        Route::post('/{productId}/images/{imageId}/set-primary', [ProductsController::class, 'setPrimaryImage'])->name('images.set-primary')
            ->where(['productId' => '[0-9a-f-]+', 'imageId' => '[0-9a-f-]+']);
    });

    Route::middleware('admin.permission:products.delete')->group(function () {
        Route::delete('/{id}', [ProductsController::class, 'destroy'])->name('destroy')->where('id', '[0-9a-f-]+');
    });
});
