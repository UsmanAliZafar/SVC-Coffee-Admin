<?php
// routes/admin/products_routes.php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Products Management Routes
|--------------------------------------------------------------------------
*/

Route::prefix('products')->name('products.')->group(function () {

    // List all products
    Route::middleware('admin.permission:products.read')->group(function () {
        Route::get('/', function () {
            return view('admin.products.index');
        })->name('index');

        Route::get('/ajax-data', function () {
            // AJAX endpoint for datatables
        })->name('data');

        Route::get('/low-stock', function () {
            return view('admin.products.low-stock');
        })->name('low-stock');

        Route::get('/inactive', function () {
            return view('admin.products.inactive');
        })->name('inactive');

        Route::get('/featured', function () {
            return view('admin.products.featured');
        })->name('featured');

        Route::get('/{id}', function ($id) {
            return view('admin.products.show', compact('id'));
        })->name('show');
    });

    // Create product
    Route::middleware('admin.permission:products.create')->group(function () {
        Route::get('/create', function () {
            return view('admin.products.create');
        })->name('create');

        Route::post('/', function () {
            // Store logic
        })->name('store');

        Route::post('/bulk-import', function () {
            // Bulk import via CSV/Excel
        })->name('bulk-import');
    });

    // Update product
    Route::middleware('admin.permission:products.update')->group(function () {
        Route::get('/{id}/edit', function ($id) {
            return view('admin.products.edit', compact('id'));
        })->name('edit');

        Route::put('/{id}', function ($id) {
            // Update logic
        })->name('update');

        Route::post('/{id}/toggle-status', function ($id) {
            // Toggle active/inactive status
        })->name('toggle-status');

        Route::post('/{id}/toggle-featured', function ($id) {
            // Toggle featured status
        })->name('toggle-featured');
    });

    // Delete product
    Route::middleware('admin.permission:products.delete')->group(function () {
        Route::delete('/{id}', function ($id) {
            // Delete logic
        })->name('destroy');

        Route::post('/bulk-delete', function () {
            // Bulk delete
        })->name('bulk-delete');
    });
});
