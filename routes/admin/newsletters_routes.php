<?php
// routes/admin/newsletters_routes.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\NewsletterController;

/*
|--------------------------------------------------------------------------
| Newsletter Management Routes
|--------------------------------------------------------------------------
*/

Route::prefix('newsletters')->name('newsletters.')->group(function () {

    // Read/View Routes
    Route::middleware('admin.permission:newsletters.read')->group(function () {
        Route::get('/', [NewsletterController::class, 'index'])->name('index');
        Route::get('/data', [NewsletterController::class, 'getData'])->name('data');
        Route::get('/statistics', [NewsletterController::class, 'statistics'])->name('statistics');
    });

    // Create Routes (MUST be before /{id} routes)
    Route::middleware('admin.permission:newsletters.create')->group(function () {
        Route::get('/create', [NewsletterController::class, 'create'])->name('create');
        Route::post('/', [NewsletterController::class, 'store'])->name('store');
    });

    // Update Routes (MUST be before /{id} routes)
    Route::middleware('admin.permission:newsletters.update')->group(function () {
        Route::get('/{id}/edit', [NewsletterController::class, 'edit'])->name('edit');
        Route::put('/{id}', [NewsletterController::class, 'update'])->name('update');
        Route::patch('/{id}', [NewsletterController::class, 'update'])->name('update.patch');
        Route::post('/{id}/resend-verification', [NewsletterController::class, 'resendVerification'])->name('resend-verification');
        Route::post('/{id}/verify-email', [NewsletterController::class, 'verifyEmail'])->name('verify-email');

        // Subscribe/Unsubscribe Actions
        Route::post('/{id}/subscribe', [NewsletterController::class, 'subscribe'])->name('subscribe');
        Route::post('/{id}/unsubscribe', [NewsletterController::class, 'unsubscribe'])->name('unsubscribe');

        // Bulk Update Status (NEW ROUTE)
        Route::post('/bulk-status', [NewsletterController::class, 'bulkUpdateStatus'])->name('bulk-status');
    });

    // Export Routes (requires read permission)
    Route::middleware('admin.permission:newsletters.read')->group(function () {
        Route::get('/export', [NewsletterController::class, 'export'])->name('export');
        Route::post('/export', [NewsletterController::class, 'export'])->name('export.post');
    });

    // Delete Routes
    Route::middleware('admin.permission:newsletters.delete')->group(function () {
        Route::delete('/{id}', [NewsletterController::class, 'destroy'])->name('destroy');
        Route::post('/bulk-delete', [NewsletterController::class, 'bulkDelete'])->name('bulk-delete');
    });

    // Show Route (MUST be LAST because it has /{id})
    Route::middleware('admin.permission:newsletters.read')->group(function () {
        Route::get('/{id}', [NewsletterController::class, 'show'])->name('show');
    });
});
