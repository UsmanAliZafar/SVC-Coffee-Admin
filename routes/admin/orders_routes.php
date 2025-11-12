<?php
// routes/admin/orders_routes.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\OrdersController;

/*
|--------------------------------------------------------------------------
| Orders Management Routes
|--------------------------------------------------------------------------
*/

Route::prefix('orders')->name('orders.')->group(function () {

    // Create order (MUST BE BEFORE /{id} routes)
    Route::middleware('admin.permission:orders.create')->group(function () {
        Route::get('/create', [OrdersController::class, 'create'])->name('create');
        Route::post('/', [OrdersController::class, 'store'])->name('store');
    });

    // View orders
    Route::middleware('admin.permission:orders.read')->group(function () {
        // Main listing page
        Route::get('/', [OrdersController::class, 'index'])->name('index');

        // AJAX endpoint for datatables
        Route::get('/ajax-data', [OrdersController::class, 'getData'])->name('data');

        // Status-based views (specific routes before /{id})
        Route::get('/pending', [OrdersController::class, 'status'])->defaults('status', 'pending')->name('pending');
        Route::get('/processing', [OrdersController::class, 'status'])->defaults('status', 'processing')->name('processing');
        Route::get('/shipped', [OrdersController::class, 'status'])->defaults('status', 'shipped')->name('shipped');
        Route::get('/delivered', [OrdersController::class, 'status'])->defaults('status', 'delivered')->name('delivered');
        Route::get('/cancelled', [OrdersController::class, 'status'])->defaults('status', 'cancelled')->name('cancelled');

        // Special views (specific routes before /{id})
        Route::get('/today', [OrdersController::class, 'today'])->name('today');
        Route::get('/with-notes', [OrdersController::class, 'withNotes'])->name('with-notes');
        Route::get('/invoices', [OrdersController::class, 'invoices'])->name('invoices');
        Route::get('/shipping', [OrdersController::class, 'shipping'])->name('shipping');
        Route::get('/refunds', [OrdersController::class, 'refunds'])->name('refunds');
        Route::get('/reports', [OrdersController::class, 'reports'])->name('reports');
        Route::get('/reports/data', [OrdersController::class, 'reportsData'])->name('reports.data');
        // Individual order views (dynamic routes MUST BE LAST)
        Route::get('/{id}', [OrdersController::class, 'show'])->name('show');
        Route::get('/{id}/invoice', [OrdersController::class, 'invoice'])->name('invoice');
        Route::get('/{id}/shipping-label', [OrdersController::class, 'shippingLabel'])->name('shipping-label');
        Route::get('/{id}/data', [OrdersController::class, 'getOrderData'])->name('get-data');
        //
        //
        Route::get('/products/{product}/variants', [OrdersController::class, 'getProductVariants'])->name('products.variants');
    });

    // Update order
    Route::middleware('admin.permission:orders.update')->group(function () {
        Route::get('/{id}/edit', [OrdersController::class, 'edit'])->name('edit');
        Route::put('/{id}', [OrdersController::class, 'update'])->name('update');
        Route::post('/{id}/update-status', [OrdersController::class, 'updateStatus'])->name('update-status');
        Route::post('/{id}/add-note', [OrdersController::class, 'addNote'])->name('add-note');
        Route::post('/{id}/process-refund', [OrdersController::class, 'processRefund'])->name('process-refund');
        Route::delete('/{orderId}/items/{itemId}', [OrdersController::class, 'removeItem'])->name('remove-item');
        Route::post('/{id}/mark-shipped', [OrdersController::class, 'markShipped'])->name('mark-shipped');
        Route::post('/{id}/update-notes', [OrdersController::class, 'updateNotes'])->name('update-notes');
        //Bulk status update routes
        Route::post('/bulk-update-status', [OrdersController::class, 'bulkUpdateStatus'])->name('bulk-update-status');
        Route::post('/bulk-update-payment-status', [OrdersController::class, 'bulkUpdatePaymentStatus'])->name('bulk-update-payment-status');
        //Single quick status update (from index page)
        Route::post('/{id}/quick-update-status', [OrdersController::class, 'quickUpdateStatus'])->name('quick-update-status');
    });

    // Delete order
    Route::middleware('admin.permission:orders.delete')->group(function () {
        Route::delete('/{id}', [OrdersController::class, 'destroy'])->name('destroy');
    });
});
