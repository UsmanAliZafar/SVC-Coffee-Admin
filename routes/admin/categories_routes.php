<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CategoriesController;

/*
|--------------------------------------------------------------------------
| Categories Management Routes
|--------------------------------------------------------------------------
*/

Route::prefix('categories')->name('categories.')->group(function () {

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

        // Show single category
        Route::get('/{id}', [CategoriesController::class, 'show'])->name('show');
    });

    // Create category
    Route::middleware('admin.permission:categories.create')->group(function () {
        Route::get('/create', [CategoriesController::class, 'create'])->name('create');
        Route::post('/', [CategoriesController::class, 'store'])->name('store');
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
    });

    // Delete category
    Route::middleware('admin.permission:categories.delete')->group(function () {
        Route::delete('/{id}', [CategoriesController::class, 'destroy'])->name('destroy');
    });
});
