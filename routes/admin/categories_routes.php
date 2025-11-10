<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CategoriesController;

/*
|--------------------------------------------------------------------------
| Categories Management Routes
|--------------------------------------------------------------------------
*/

Route::prefix('categories')->name('categories.')->group(function () {

    // Create category - MUST BE BEFORE /{id} routes
    Route::middleware('admin.permission:categories.create')->group(function () {
        Route::get('/create', [CategoriesController::class, 'create'])->name('create');
        Route::post('/', [CategoriesController::class, 'store'])->name('store');
    });

    // List all categories
    Route::middleware('admin.permission:categories.read')->group(function () {
        // Main index page
        Route::get('/', [CategoriesController::class, 'index'])->name('index');

        // Ajax DataTable data
        Route::get('/ajax-data', [CategoriesController::class, 'getData'])->name('data');

        // Tree view
        Route::get('/tree', [CategoriesController::class, 'tree'])->name('tree');

        // Empty categories
        Route::get('/empty', [CategoriesController::class, 'empty'])->name('empty');
    });

    // Update category
    Route::middleware('admin.permission:categories.update')->group(function () {
        Route::get('/{id}/edit', [CategoriesController::class, 'edit'])->name('edit');
        Route::put('/{id}', [CategoriesController::class, 'update'])->name('update');

        // Reorder categories
        Route::post('/reorder', [CategoriesController::class, 'reorder'])->name('reorder');

        // Toggle status
        Route::post('/{id}/toggle-status', [CategoriesController::class, 'toggleStatus'])->name('toggle-status');

        // Toggle featured
        Route::post('/{id}/toggle-featured', [CategoriesController::class, 'toggleFeatured'])->name('toggle-featured');

        Route::post('/{id}/update-url', [CategoriesController::class, 'updateUrl'])->name('update-url');
    });

    // Delete category
    Route::middleware('admin.permission:categories.delete')->group(function () {
        Route::delete('/{id}', [CategoriesController::class, 'destroy'])->name('destroy');
    });

    // Show single category - MUST BE LAST
    Route::middleware('admin.permission:categories.read')->group(function () {
        Route::get('/{id}', [CategoriesController::class, 'show'])->name('show');
    });
});
