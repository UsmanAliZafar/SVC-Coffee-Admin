<?php
// routes/admin/reports_routes.php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Reports & Analytics Routes
|--------------------------------------------------------------------------
*/

Route::prefix('reports')->name('reports.')->group(function () {

    Route::middleware('admin.permission:reports.read')->group(function () {
        // Main Reports Dashboard
        Route::get('/', function () {
            return view('admin.reports.index');
        })->name('index');

        // Sales Reports
        Route::prefix('sales')->name('sales.')->group(function () {
            Route::get('/daily', function () {
                return view('admin.reports.sales.daily');
            })->name('daily');

            Route::get('/weekly', function () {
                return view('admin.reports.sales.weekly');
            })->name('weekly');

            Route::get('/monthly', function () {
                return view('admin.reports.sales.monthly');
            })->name('monthly');

            Route::get('/yearly', function () {
                return view('admin.reports.sales.yearly');
            })->name('yearly');

            Route::get('/custom-range', function () {
                return view('admin.reports.sales.custom-range');
            })->name('custom-range');
        });

        // Revenue Analytics
        Route::prefix('revenue')->name('revenue.')->group(function () {
            Route::get('/', function () {
                return view('admin.reports.revenue.index');
            })->name('index');

            Route::get('/analytics', function () {
                // AJAX endpoint for charts data
            })->name('analytics');

            Route::get('/by-category', function () {
                return view('admin.reports.revenue.by-category');
            })->name('by-category');

            Route::get('/by-product', function () {
                return view('admin.reports.revenue.by-product');
            })->name('by-product');
        });

        // Product Reports
        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/top-selling', function () {
                return view('admin.reports.products.top-selling');
            })->name('top-selling');

            Route::get('/by-category', function () {
                return view('admin.reports.products.by-category');
            })->name('by-category');

            Route::get('/performance', function () {
                return view('admin.reports.products.performance');
            })->name('performance');
        });

        // Inventory Reports
        Route::prefix('inventory')->name('inventory.')->group(function () {
            Route::get('/', function () {
                return view('admin.reports.inventory.index');
            })->name('index');

            Route::get('/stock-levels', function () {
                return view('admin.reports.inventory.stock-levels');
            })->name('stock-levels');

            Route::get('/movement', function () {
                return view('admin.reports.inventory.movement');
            })->name('movement');

            Route::get('/valuation', function () {
                return view('admin.reports.inventory.valuation');
            })->name('valuation');
        });

        // Customer Analytics
        Route::prefix('customers')->name('customers.')->group(function () {
            Route::get('/', function () {
                return view('admin.reports.customers.index');
            })->name('index');

            Route::get('/new-vs-returning', function () {
                return view('admin.reports.customers.new-vs-returning');
            })->name('new-vs-returning');

            Route::get('/analytics', function () {
                // AJAX endpoint for customer analytics
            })->name('analytics');

            Route::get('/lifetime-value', function () {
                return view('admin.reports.customers.lifetime-value');
            })->name('lifetime-value');
        });

        // Export Reports
        Route::get('/export/pdf/{reportType}', function ($reportType) {
            // Export report to PDF
        })->name('export.pdf');

        Route::get('/export/excel/{reportType}', function ($reportType) {
            // Export report to Excel
        })->name('export.excel');

        Route::get('/export/csv/{reportType}', function ($reportType) {
            // Export report to CSV
        })->name('export.csv');
    });
});
