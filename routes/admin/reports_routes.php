<?php
// routes/admin/reports_routes.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ReportsController;

/*
|--------------------------------------------------------------------------
| Reports & Analytics Routes
|--------------------------------------------------------------------------
*/

Route::prefix('reports')->name('reports.')->group(function () {

    Route::middleware('admin.permission:reports.read')->group(function () {

        // Main Reports Dashboard
        Route::get('/', [ReportsController::class, 'index'])->name('index');

        // Sales Reports
        Route::prefix('sales')->name('sales.')->group(function () {
            Route::get('/daily', [ReportsController::class, 'salesDaily'])->name('daily');
            Route::get('/weekly', [ReportsController::class, 'salesWeekly'])->name('weekly');
            Route::get('/monthly', [ReportsController::class, 'salesMonthly'])->name('monthly');
            Route::get('/yearly', [ReportsController::class, 'salesYearly'])->name('yearly');
            Route::get('/custom-range', [ReportsController::class, 'salesCustomRange'])->name('custom-range');
        });

        // Revenue Analytics
        Route::prefix('revenue')->name('revenue.')->group(function () {
            Route::get('/', [ReportsController::class, 'revenueIndex'])->name('index');
            Route::get('/by-category', [ReportsController::class, 'revenueByCategory'])->name('by-category');
            Route::get('/by-product', [ReportsController::class, 'revenueByProduct'])->name('by-product');
        });

        // Product Reports
        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/top-selling', [ReportsController::class, 'productsTopSelling'])->name('top-selling');
            Route::get('/by-category', [ReportsController::class, 'productsByCategory'])->name('by-category');
            Route::get('/performance', [ReportsController::class, 'productsPerformance'])->name('performance');
        });

        // Inventory Reports
        Route::prefix('inventory')->name('inventory.')->group(function () {
            Route::get('/', [ReportsController::class, 'inventoryIndex'])->name('index');
            Route::get('/stock-levels', [ReportsController::class, 'inventoryStockLevels'])->name('stock-levels');
            Route::get('/movement', [ReportsController::class, 'inventoryMovement'])->name('movement');
            Route::get('/valuation', [ReportsController::class, 'inventoryValuation'])->name('valuation');
        });

        // Customer Analytics
        Route::prefix('customers')->name('customers.')->group(function () {
            Route::get('/', [ReportsController::class, 'customersIndex'])->name('index');
            Route::get('/new-vs-returning', [ReportsController::class, 'customersNewVsReturning'])->name('new-vs-returning');
            Route::get('/lifetime-value', [ReportsController::class, 'customersLifetimeValue'])->name('lifetime-value');
        });

        // Export Reports
        Route::get('/export/pdf/{reportType}', [ReportsController::class, 'exportPdf'])->name('export.pdf');
        Route::get('/export/excel/{reportType}', [ReportsController::class, 'exportExcel'])->name('export.excel');
        Route::get('/export/csv/{reportType}', [ReportsController::class, 'exportCsv'])->name('export.csv');
    });
});
