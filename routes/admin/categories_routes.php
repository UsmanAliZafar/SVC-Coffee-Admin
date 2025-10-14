<?php
// routes/admin/categories_routes.php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Categories Management Routes
|--------------------------------------------------------------------------
*/

Route::prefix('categories')->name('categories.')->group(function () {

    // List all categories
    Route::middleware('admin.permission:categories.read')->group(function () {
        Route::get('/', function () {
            return view('admin.categories.index');
        })->name('index');

        Route::get('/ajax-data', function () {
            // AJAX endpoint for datatables
        })->name('data');

        Route::get('/tree', function () {
            return view('admin.categories.tree');
        })->name('tree');

        Route::get('/empty', function () {
            return view('admin.categories.empty');
        })->name('empty');

        Route::get('/inactive', function () {
            return view('admin.categories.inactive');
        })->name('inactive');

        Route::get('/featured', function () {
            return view('admin.categories.featured');
        })->name('featured');

        Route::get('/{id}', function ($id) {
            return view('admin.categories.show', compact('id'));
        })->name('show');
    });

    // Create category
    Route::middleware('admin.permission:categories.create')->group(function () {
        Route::get('/create', function () {
            return view('admin.categories.create');
        })->name('create');

        Route::post('/', function () {
            // Store logic
        })->name('store');
    });

    // Update category
    Route::middleware('admin.permission:categories.update')->group(function () {
        Route::get('/{id}/edit', function ($id) {
            return view('admin.categories.edit', compact('id'));
        })->name('edit');

        Route::put('/{id}', function ($id) {
            // Update logic
        })->name('update');

        Route::post('/reorder', function () {
            // Reorder categories
        })->name('reorder');

        Route::post('/{id}/toggle-status', function ($id) {
            // Toggle active/inactive status
        })->name('toggle-status');

        Route::post('/{id}/toggle-featured', function ($id) {
            // Toggle featured status
        })->name('toggle-featured');
    });

    // Delete category
    Route::middleware('admin.permission:categories.delete')->group(function () {
        Route::delete('/{id}', function ($id) {
            // Delete logic
        })->name('destroy');
    });
});
