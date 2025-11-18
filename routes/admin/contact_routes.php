<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ContactUsController;
// routes/admin/contact_routes.php
/*
|--------------------------------------------------------------------------
| Admin Contact Us Routes
|--------------------------------------------------------------------------
*/
Route::prefix('contact-us')->name('contact-us.')->group(function () {

    // Main listing page
    Route::get('/', [ContactUsController::class, 'index'])->name('index');

    // Get data for DataTable (AJAX)
    Route::get('/get-data', [ContactUsController::class, 'getData'])->name('get-data');

    // View single contact message
    Route::get('/{id}', [ContactUsController::class, 'show'])->name('show');

    // Update status
    Route::patch('/{id}/status', [ContactUsController::class, 'updateStatus'])->name('update-status');

    // Assign to admin
    Route::patch('/{id}/assign', [ContactUsController::class, 'assign'])->name('assign');

    // Update priority
    Route::patch('/{id}/priority', [ContactUsController::class, 'updatePriority'])->name('update-priority');

    // Mark as read
    Route::patch('/{id}/mark-as-read', [ContactUsController::class, 'markAsRead'])->name('mark-as-read');

    // Delete contact message
    Route::delete('/{id}', [ContactUsController::class, 'destroy'])->name('destroy');

    // Bulk operations
    Route::post('/bulk/status', [ContactUsController::class, 'bulkUpdateStatus'])->name('bulk-status');
    Route::post('/bulk/assign', [ContactUsController::class, 'bulkAssign'])->name('bulk-assign');
    Route::post('/bulk/mark-as-read', [ContactUsController::class, 'bulkMarkAsRead'])->name('bulk-mark-as-read');
    Route::post('/bulk/delete', [ContactUsController::class, 'bulkDelete'])->name('bulk-delete');

    // Export
    Route::get('/export', [ContactUsController::class, 'export'])->name('export');
});

