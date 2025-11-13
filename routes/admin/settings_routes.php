<?php
// routes/admin/settings_routes.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\StoreSettingsController;
/*
|--------------------------------------------------------------------------
| Settings Routes
|--------------------------------------------------------------------------
*/

Route::prefix('settings')->name('settings.')->group(function () {

    Route::middleware('admin.permission:settings.read')->group(function () {
        Route::get('/', [StoreSettingsController::class, 'index'])->name('index');
    });

    // Update Settings
    Route::middleware('admin.permission:settings.update')->group(function () {
        Route::put('/basic-info', [StoreSettingsController::class, 'updateBasicInfo'])->name('update-basic');
        Route::post('/branding', [StoreSettingsController::class, 'updateBranding'])->name('update-branding');
        Route::put('/regional', [StoreSettingsController::class, 'updateRegional'])->name('update-regional');
        Route::put('/order', [StoreSettingsController::class, 'updateOrder'])->name('update-order');
        Route::put('/tax', [StoreSettingsController::class, 'updateTax'])->name('update-tax');
        Route::put('/shipping', [StoreSettingsController::class, 'updateShipping'])->name('update-shipping');
        Route::put('/inventory', [StoreSettingsController::class, 'updateInventory'])->name('update-inventory');
        Route::put('/email', [StoreSettingsController::class, 'updateEmail'])->name('update-email');
        Route::put('/social', [StoreSettingsController::class, 'updateSocial'])->name('update-social');
        Route::put('/seo', [StoreSettingsController::class, 'updateSeo'])->name('update-seo');
        Route::put('/maintenance', [StoreSettingsController::class, 'updateMaintenance'])->name('update-maintenance');
        Route::put('/checkout', [StoreSettingsController::class, 'updateCheckout'])->name('update-checkout');
    });
});
