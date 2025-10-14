<?php
// routes/admin/orders_routes.php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Orders Management Routes
|--------------------------------------------------------------------------
*/

Route::prefix('orders')->name('orders.')->group(function () {

    // View orders
    Route::middleware('admin.permission:orders.read')->group(function () {
        Route::get('/', function () {
            return view('admin.orders.index');
        })->name('index');

        Route::get('/ajax-data', function () {
            // AJAX endpoint for datatables
        })->name('data');

        Route::get('/pending', function () {
            return view('admin.orders.status', ['status' => 'pending']);
        })->name('pending');

        Route::get('/processing', function () {
            return view('admin.orders.status', ['status' => 'processing']);
        })->name('processing');

        Route::get('/shipped', function () {
            return view('admin.orders.status', ['status' => 'shipped']);
        })->name('shipped');

        Route::get('/delivered', function () {
            return view('admin.orders.status', ['status' => 'delivered']);
        })->name('delivered');

        Route::get('/cancelled', function () {
            return view('admin.orders.status', ['status' => 'cancelled']);
        })->name('cancelled');

        Route::get('/today', function () {
            return view('admin.orders.today');
        })->name('today');

        Route::get('/with-notes', function () {
            return view('admin.orders.with-notes');
        })->name('with-notes');

        Route::get('/invoices', function () {
            return view('admin.orders.invoices');
        })->name('invoices');

        Route::get('/shipping', function () {
            return view('admin.orders.shipping');
        })->name('shipping');

        Route::get('/refunds', function () {
            return view('admin.orders.refunds');
        })->name('refunds');

        Route::get('/reports', function () {
            return view('admin.orders.reports');
        })->name('reports');

        Route::get('/{id}', function ($id) {
            return view('admin.orders.show', compact('id'));
        })->name('show');

        Route::get('/{id}/invoice', function ($id) {
            // Generate invoice PDF
        })->name('invoice');

        Route::get('/{id}/shipping-label', function ($id) {
            // Generate shipping label PDF
        })->name('shipping-label');
    });

    // Create order
    Route::middleware('admin.permission:orders.create')->group(function () {
        Route::get('/create', function () {
            return view('admin.orders.create');
        })->name('create');

        Route::post('/', function () {
            // Store logic
        })->name('store');
    });

    // Update order
    Route::middleware('admin.permission:orders.update')->group(function () {
        Route::get('/{id}/edit', function ($id) {
            return view('admin.orders.edit', compact('id'));
        })->name('edit');

        Route::put('/{id}', function ($id) {
            // Update logic
        })->name('update');

        Route::post('/{id}/update-status', function ($id) {
            // Update order status
        })->name('update-status');

        Route::post('/{id}/add-note', function ($id) {
            // Add internal note
        })->name('add-note');

        Route::post('/{id}/process-refund', function ($id) {
            // Process refund
        })->name('process-refund');
    });

    // Delete order
    Route::middleware('admin.permission:orders.delete')->group(function () {
        Route::delete('/{id}', function ($id) {
            // Delete logic
        })->name('destroy');
    });
});
