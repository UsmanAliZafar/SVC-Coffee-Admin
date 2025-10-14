<?php
// routes/admin/inventory_routes.php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Inventory Management Routes
|--------------------------------------------------------------------------
*/

Route::prefix('inventory')->name('inventory.')->group(function () {

    // View inventory
    Route::middleware('admin.permission:inventory.read')->group(function () {
        Route::get('/', function () {
            return view('admin.inventory.index');
        })->name('index');

        Route::get('/ajax-data', function () {
            // AJAX endpoint for datatables
        })->name('data');

        Route::get('/movement', function () {
            return view('admin.inventory.movement');
        })->name('movement');

        Route::get('/low-stock', function () {
            return view('admin.inventory.low-stock');
        })->name('low-stock');

        Route::get('/out-of-stock', function () {
            return view('admin.inventory.out-of-stock');
        })->name('out-of-stock');

        Route::get('/warehouse-sync', function () {
            return view('admin.inventory.warehouse-sync');
        })->name('warehouse-sync');

        Route::get('/sync-settings', function () {
            return view('admin.inventory.sync-settings');
        })->name('sync-settings');

        Route::get('/reports', function () {
            return view('admin.inventory.reports');
        })->name('reports');
    });

    // Update inventory
    Route::middleware('admin.permission:inventory.update')->group(function () {
        Route::get('/adjust', function () {
            return view('admin.inventory.adjust');
        })->name('adjust');

        Route::post('/adjust', function () {
            // Adjust stock logic
        })->name('adjust.store');

        Route::get('/bulk-update', function () {
            return view('admin.inventory.bulk-update');
        })->name('bulk-update');

        Route::post('/bulk-update', function () {
            // Bulk update logic
        })->name('bulk-update.store');

        Route::post('/warehouse-sync/execute', function () {
            // Execute warehouse sync
        })->name('warehouse-sync.execute');

        Route::post('/sync-settings/update', function () {
            // Update sync settings
        })->name('sync-settings.update');
    });
});
