<?php

use App\Http\Controllers\Admin\CustomersController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Customers Management Routes
|--------------------------------------------------------------------------
| All routes for customer management including CRUD, segments,
| and reporting features.
|
*/

Route::prefix('customers')->name('customers.')->group(function () {

    // Main customers listing and data
    Route::get('/', [CustomersController::class, 'index'])
        ->name('index')
        ->middleware('admin.permission:customers.read');

    Route::get('/data', [CustomersController::class, 'getData'])
        ->name('data')
        ->middleware('admin.permission:customers.read');

    // Customer Segments & Filters
    Route::middleware('admin.permission:customers.read')->group(function () {
        Route::get('/new', [CustomersController::class, 'newCustomers'])->name('new');
        Route::get('/returning', [CustomersController::class, 'returningCustomers'])->name('returning');
        Route::get('/top', [CustomersController::class, 'topCustomers'])->name('top');
        Route::get('/active', [CustomersController::class, 'activeCustomers'])->name('active');
        Route::get('/blocked', [CustomersController::class, 'blockedCustomers'])->name('blocked');
        Route::get('/inactive', [CustomersController::class, 'inactiveCustomers'])->name('inactive');
    });

    // Analytics & Reports
    Route::middleware('admin.permission:customers.read')->group(function () {
        Route::get('/reports', [CustomersController::class, 'reports'])->name('reports');
        Route::get('/reports/data', [CustomersController::class, 'reportsData'])->name('reports.data');
    });

    // Create Customer
    Route::middleware('admin.permission:customers.create')->group(function () {
        Route::get('/create', [CustomersController::class, 'create'])->name('create');
        Route::post('/', [CustomersController::class, 'store'])->name('store');
    });

    // Update Customer
    Route::middleware('admin.permission:customers.update')->group(function () {
        Route::post('/{id}/toggle-status', [CustomersController::class, 'toggleStatus'])->name('toggle-status');
    });

    // Export
    Route::get('/export', [CustomersController::class, 'export'])
        ->name('export')
        ->middleware('admin.permission:customers.read');

    // CRUD Routes (MUST BE LAST - after all specific routes)
    Route::middleware('admin.permission:customers.read')->group(function () {
        Route::get('/{id}', [CustomersController::class, 'show'])->name('show');
    });

    Route::middleware('admin.permission:customers.update')->group(function () {
        Route::get('/{id}/edit', [CustomersController::class, 'edit'])->name('edit');
        Route::put('/{id}', [CustomersController::class, 'update'])->name('update');
    });

    Route::middleware('admin.permission:customers.delete')->group(function () {
        Route::delete('/{id}', [CustomersController::class, 'destroy'])->name('destroy');
    });
});
