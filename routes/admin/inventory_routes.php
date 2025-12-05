<?php
// routes/admin/inventory_routes.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\WarehouseController;

/*
|--------------------------------------------------------------------------
| Inventory Management Routes
|--------------------------------------------------------------------------
*/

Route::prefix('inventory')->name('inventory.')->group(function () {

    // View inventory
    Route::middleware('admin.permission:inventory.read')->group(function () {
        // Main inventory listing
        Route::get('/', [InventoryController::class, 'index'])->name('index');

        // AJAX endpoint for datatables
        Route::get('/ajax-data', [InventoryController::class, 'getData'])->name('data');

        // Get inventory statistics
        Route::get('/statistics', [InventoryController::class, 'statistics'])->name('statistics');

        // Movement history
        Route::get('/movement', [InventoryController::class, 'movement'])->name('movement');
        Route::get('/movement/data', [InventoryController::class, 'getMovementsData'])->name('movement.data');

        Route::get('/movement/statistics', [InventoryController::class, 'getMovementStatistics'])->name('movement.statistics');
        Route::get('/movement/export', [InventoryController::class, 'exportMovements'])->name('movement.export');
        Route::get('/movement/{id}', [InventoryController::class, 'showMovement'])->name('movement.show');

        // Low stock products
        Route::get('/low-stock', [InventoryController::class, 'lowStock'])->name('low-stock');
        Route::get('/low-stock', [InventoryController::class, 'lowStock'])->name('low-stock');
        Route::get('/low-stock/data', [InventoryController::class, 'getLowStockData'])->name('low-stock.data');
        Route::get('/low-stock/statistics', [InventoryController::class, 'getLowStockStatistics'])->name('low-stock.statistics');

        // Out of stock products
        Route::get('/out-of-stock', [InventoryController::class, 'outOfStock'])->name('out-of-stock');
        Route::get('/out-of-stock/data', [InventoryController::class, 'getOutOfStockData'])->name('out-of-stock.data');
        // Warehouse sync dashboard
        Route::get('/warehouse-sync', [InventoryController::class, 'warehouseSync'])->name('warehouse-sync');
        Route::get('/warehouse-sync/status', [InventoryController::class, 'getSyncStatus'])->name('warehouse-sync.status');
        Route::get('/warehouse-sync/logs', [InventoryController::class, 'getSyncLogs'])->name('warehouse-sync.logs');

        // Sync settings
        Route::get('/sync-settings', [InventoryController::class, 'syncSettings'])->name('sync-settings');

        // Reports
        Route::get('/reports', [InventoryController::class, 'reports'])->name('reports');
        Route::get('/reports/generate', [InventoryController::class, 'generateReport'])->name('reports.generate');
        Route::get('/reports/export', [InventoryController::class, 'exportReport'])->name('reports.export');

        // Stock alerts
        Route::get('/alerts', [InventoryController::class, 'alerts'])->name('alerts');
        Route::get('/alerts/data', [InventoryController::class, 'getAlertsData'])->name('alerts.data');

        Route::get('/warehouse-counts', [InventoryController::class, 'getWarehouseCounts'])->name('warehouse-counts');
    });

    // Update inventory
    Route::middleware('admin.permission:inventory.update')->group(function () {
        // Adjust stock page
        Route::get('/adjust', [InventoryController::class, 'adjust'])->name('adjust');

        // Store stock adjustment
        Route::post('/adjust', [InventoryController::class, 'adjustStock'])->name('adjust.store');

        // Transfer stock between warehouses
        Route::post('/transfer', [InventoryController::class, 'transfer'])->name('transfer');

        // Bulk update page
        Route::get('/bulk-update', [InventoryController::class, 'bulkUpdate'])->name('bulk-update');

        // Store bulk update
        Route::post('/bulk-update', [InventoryController::class, 'bulkUpdateStore'])->name('bulk-update.store');

        // Bulk import via CSV
        Route::post('/bulk-import', [InventoryController::class, 'bulkImport'])->name('bulk-import');
        Route::get('/bulk-import/template', [InventoryController::class, 'downloadImportTemplate'])->name('bulk-import.template');

        // Execute warehouse sync
        Route::post('/warehouse-sync/execute', [InventoryController::class, 'executeSync'])->name('warehouse-sync.execute');
        Route::post('/warehouse-sync/manual', [InventoryController::class, 'manualSync'])->name('warehouse-sync.manual');
        Route::post('/warehouse-sync/test', [InventoryController::class, 'testSync'])->name('warehouse-sync.test');

        // Update sync settings
        Route::post('/sync-settings/update', [InventoryController::class, 'updateSyncSettings'])->name('sync-settings.update');

        // Resolve stock alerts
        Route::post('/alerts/{alert}/resolve', [InventoryController::class, 'resolveAlert'])->name('alerts.resolve');
        Route::post('/alerts/bulk-resolve', [InventoryController::class, 'bulkResolveAlerts'])->name('alerts.bulk-resolve');

        // Quick actions
        Route::post('/quick-restock', [InventoryController::class, 'quickRestock'])->name('quick-restock');
        Route::post('/set-threshold', [InventoryController::class, 'setThreshold'])->name('set-threshold');
    });

    // Create/Delete inventory (admin only)
    Route::middleware('admin.permission:inventory.delete')->group(function () {
        // Delete movement record (audit purposes - rarely used)
        Route::delete('/movement/{movement}', [InventoryController::class, 'deleteMovement'])->name('movement.delete');
        Route::post('/cleanup-orphaned', [InventoryController::class, 'cleanupOrphanedStock'])->name('cleanup-orphaned');

        // Clear old movements
        Route::post('/movement/clear-old', [InventoryController::class, 'clearOldMovements'])->name('movement.clear-old');
    });
});

/*
|--------------------------------------------------------------------------
| Warehouse Management Routes
|--------------------------------------------------------------------------
*/

Route::prefix('warehouses')->name('warehouses.')->group(function () {

    // Manage warehouses - CREATE routes MUST be first
    Route::middleware('admin.permission:inventory.update')->group(function () {
        Route::get('/create', [WarehouseController::class, 'create'])->name('create');
        Route::post('/', [WarehouseController::class, 'store'])->name('store');
    });

    // View warehouses
    Route::middleware('admin.permission:inventory.read')->group(function () {
        Route::get('/', [WarehouseController::class, 'index'])->name('index');
        Route::get('/data', [WarehouseController::class, 'getData'])->name('data');
    });

    // Edit warehouses
    Route::middleware('admin.permission:inventory.update')->group(function () {
        Route::get('/{warehouse}/edit', [WarehouseController::class, 'edit'])->name('edit');
        Route::put('/{warehouse}', [WarehouseController::class, 'update'])->name('update');
        Route::post('/{warehouse}/toggle-status', [WarehouseController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{warehouse}/set-default', [WarehouseController::class, 'setDefault'])->name('set-default');
    });

    // View warehouse details - These MUST come after /create but before /{warehouse}
    Route::middleware('admin.permission:inventory.read')->group(function () {
        Route::get('/{warehouse}/stock', [WarehouseController::class, 'stock'])->name('stock');
        Route::get('/{warehouse}/stock-data', [WarehouseController::class, 'getStockData'])->name('stock.data');
        Route::get('/{warehouse}/movements-data', [WarehouseController::class, 'getMovementsData'])->name('movements.data');
        Route::get('/{warehouse}/statistics', [WarehouseController::class, 'statistics'])->name('statistics');
        Route::get('/{warehouse}/export-stock', [WarehouseController::class, 'exportStock'])->name('export-stock');

        // THIS MUST BE LAST - catches any /{warehouse} pattern
        Route::get('/{warehouse}', [WarehouseController::class, 'show'])->name('show');
    });

    // Delete warehouses
    Route::middleware('admin.permission:inventory.delete')->group(function () {
        Route::delete('/{warehouse}', [WarehouseController::class, 'destroy'])->name('destroy');
    });
});

/*
|--------------------------------------------------------------------------
| Stock Alerts Routes
|--------------------------------------------------------------------------
*/

Route::prefix('stock-alerts')->name('stock-alerts.')->middleware('admin.permission:inventory.read')->group(function () {
    Route::get('/', [InventoryController::class, 'stockAlerts'])->name('index');
    Route::get('/data', [InventoryController::class, 'getStockAlertsData'])->name('data');
    Route::get('/count', [InventoryController::class, 'getAlertsCount'])->name('count');

    Route::middleware('admin.permission:inventory.update')->group(function () {
        Route::post('/{alert}/resolve', [InventoryController::class, 'resolveStockAlert'])->name('resolve');
        Route::post('/bulk-resolve', [InventoryController::class, 'bulkResolveStockAlerts'])->name('bulk-resolve');
        Route::post('/{alert}/snooze', [InventoryController::class, 'snoozeAlert'])->name('snooze');
    });
});

/*
|--------------------------------------------------------------------------
| Inventory API Routes (for external warehouse systems)
|--------------------------------------------------------------------------
*/

Route::prefix('api/inventory')->name('api.inventory.')->middleware(['admin.permission:inventory.update', 'throttle:60,1'])->group(function () {
    // External API for warehouse sync
    Route::post('/sync', [InventoryController::class, 'apiSync'])->name('sync');
    Route::post('/update-stock', [InventoryController::class, 'apiUpdateStock'])->name('update-stock');
    Route::get('/get-stock', [InventoryController::class, 'apiGetStock'])->name('get-stock');
    Route::post('/reserve-stock', [InventoryController::class, 'apiReserveStock'])->name('reserve-stock');
    Route::post('/release-stock', [InventoryController::class, 'apiReleaseStock'])->name('release-stock');
});
