<?php
// routes/admin/admin-notifications.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\NotificationsController;

// ==================== NOTIFICATION ROUTES ====================
Route::prefix('notifications')->name('notifications.')->group(function () {

    // Notification List & Management
    Route::get('/', [NotificationsController::class, 'index'])->name('index');
    Route::get('/data', [NotificationsController::class, 'getNotificationsdata'])->name('data');
    Route::get('/settings', [NotificationsController::class, 'settings'])->name('settings');
    Route::post('/settings', [NotificationsController::class, 'updateSettings'])->name('settings.update');
    Route::post('/settings/reset', [NotificationsController::class, 'resetSettings'])->name('settings.reset');

    // AJAX Routes for Notification Actions
    Route::post('/{id}/mark-as-read', [NotificationsController::class, 'markAsRead'])->name('mark-as-read');
    Route::post('/mark-all-as-read', [NotificationsController::class, 'markAllAsRead'])->name('mark-all-as-read');
    Route::delete('/{id}', [NotificationsController::class, 'destroy'])->name('destroy');
    Route::delete('/clear/read', [NotificationsController::class, 'clearRead'])->name('clear-read');
    Route::delete('/clear/all', [NotificationsController::class, 'clearAll'])->name('clear-all');

    // AJAX Data Routes
    Route::get('/unread', [NotificationsController::class, 'getUnread'])->name('unread');
    Route::get('/statistics', [NotificationsController::class, 'statistics'])->name('statistics');

    // Test Route (only in development)
    if (config('app.debug')) {
        Route::post('/test', [NotificationsController::class, 'test'])->name('test');
    }
});
