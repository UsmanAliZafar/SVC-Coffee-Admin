<?php
// routes/admin/customers_routes.php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Customers Management Routes
|--------------------------------------------------------------------------
*/

Route::prefix('customers')->name('customers.')->group(function () {

    // View customers
    Route::middleware('admin.permission:customers.read')->group(function () {
        Route::get('/', function () {
            return view('admin.customers.index');
        })->name('index');

        Route::get('/ajax-data', function () {
            // AJAX endpoint for datatables
        })->name('data');

        Route::get('/groups', function () {
            return view('admin.customers.groups');
        })->name('groups');

        Route::get('/new', function () {
            return view('admin.customers.new');
        })->name('new');

        Route::get('/returning', function () {
            return view('admin.customers.returning');
        })->name('returning');

        Route::get('/top', function () {
            return view('admin.customers.top');
        })->name('top');

        Route::get('/active', function () {
            return view('admin.customers.active');
        })->name('active');

        Route::get('/blocked', function () {
            return view('admin.customers.blocked');
        })->name('blocked');

        Route::get('/inactive', function () {
            return view('admin.customers.inactive');
        })->name('inactive');

        Route::get('/purchase-patterns', function () {
            return view('admin.customers.purchase-patterns');
        })->name('purchase-patterns');

        Route::get('/communication', function () {
            return view('admin.customers.communication');
        })->name('communication');

        Route::get('/newsletter', function () {
            return view('admin.customers.newsletter');
        })->name('newsletter');

        Route::get('/bulk-email', function () {
            return view('admin.customers.bulk-email');
        })->name('bulk-email');

        Route::get('/reports', function () {
            return view('admin.customers.reports');
        })->name('reports');

        Route::get('/{id}', function ($id) {
            return view('admin.customers.show', compact('id'));
        })->name('show');
    });

    // Create customer
    Route::middleware('admin.permission:customers.create')->group(function () {
        Route::get('/create', function () {
            return view('admin.customers.create');
        })->name('create');

        Route::post('/', function () {
            // Store logic
        })->name('store');

        Route::post('/groups/create', function () {
            // Create customer group
        })->name('groups.store');
    });

    // Update customer
    Route::middleware('admin.permission:customers.update')->group(function () {
        Route::get('/{id}/edit', function ($id) {
            return view('admin.customers.edit', compact('id'));
        })->name('edit');

        Route::put('/{id}', function ($id) {
            // Update logic
        })->name('update');

        Route::post('/{id}/toggle-status', function ($id) {
            // Toggle active/blocked status
        })->name('toggle-status');

        Route::post('/bulk-email/send', function () {
            // Send bulk email
        })->name('bulk-email.send');

        Route::post('/newsletter/send', function () {
            // Send newsletter
        })->name('newsletter.send');
    });

    // Delete customer
    Route::middleware('admin.permission:customers.delete')->group(function () {
        Route::delete('/{id}', function ($id) {
            // Delete logic
        })->name('destroy');

        Route::delete('/groups/{groupId}', function ($groupId) {
            // Delete customer group
        })->name('groups.destroy');
    });
});
