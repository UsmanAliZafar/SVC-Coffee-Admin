<?php
// routes/apis/storesettings_routes.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StoreSettingsApiController;

/*
|--------------------------------------------------------------------------
| Store Settings API Routes
|--------------------------------------------------------------------------
|
| Public API routes for fetching store settings and configuration
|
*/

Route::prefix('store')->group(function () {

    // Get all settings
    Route::get('/settings', [StoreSettingsApiController::class, 'index']);

    // Get specific setting sections
    Route::get('/settings/basic-info', [StoreSettingsApiController::class, 'basicInfo']);
    Route::get('/settings/branding', [StoreSettingsApiController::class, 'branding']);
    Route::get('/settings/regional', [StoreSettingsApiController::class, 'regional']);
    Route::get('/settings/shipping', [StoreSettingsApiController::class, 'shipping']);
    Route::get('/settings/checkout', [StoreSettingsApiController::class, 'checkout']);
    Route::get('/settings/social-media', [StoreSettingsApiController::class, 'socialMedia']);
    Route::get('/settings/business-hours', [StoreSettingsApiController::class, 'businessHours']);

    // Utility endpoints
    Route::get('/settings/maintenance-status', [StoreSettingsApiController::class, 'maintenanceStatus']);
    Route::post('/settings/calculate-shipping', [StoreSettingsApiController::class, 'calculateShipping']);
    Route::post('/settings/format-currency', [StoreSettingsApiController::class, 'formatCurrency']);

});
