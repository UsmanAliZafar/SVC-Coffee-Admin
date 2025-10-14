<?php
// routes/admin/settings_routes.php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Settings Routes
|--------------------------------------------------------------------------
*/

Route::prefix('settings')->name('settings.')->group(function () {

    Route::middleware('admin.permission:settings.read')->group(function () {
        // Main Settings Page
        Route::get('/', function () {
            return view('admin.settings.index');
        })->name('index');

        // General Settings
        Route::get('/general', function () {
            return view('admin.settings.general');
        })->name('general');

        // Website Settings
        Route::get('/website', function () {
            return view('admin.settings.website');
        })->name('website');

        // Payment Gateway Settings
        Route::get('/payment-gateways', function () {
            return view('admin.settings.payment-gateways');
        })->name('payment-gateways');

        // Shipping Settings
        Route::get('/shipping', function () {
            return view('admin.settings.shipping');
        })->name('shipping');

        // Tax Settings
        Route::get('/tax', function () {
            return view('admin.settings.tax');
        })->name('tax');

        // Email Configuration
        Route::get('/email', function () {
            return view('admin.settings.email');
        })->name('email');

        // Email Templates
        Route::get('/email-templates', function () {
            return view('admin.settings.email-templates');
        })->name('email-templates');

        // Backup & Restore
        Route::get('/backup', function () {
            return view('admin.settings.backup');
        })->name('backup');
    });

    // Update Settings
    Route::middleware('admin.permission:settings.update')->group(function () {
        Route::post('/general/update', function () {
            // Update general settings
        })->name('general.update');

        Route::post('/website/update', function () {
            // Update website settings
        })->name('website.update');

        Route::post('/payment-gateways/update', function () {
            // Update payment gateway settings
        })->name('payment-gateways.update');

        Route::post('/shipping/update', function () {
            // Update shipping settings
        })->name('shipping.update');

        Route::post('/tax/update', function () {
            // Update tax settings
        })->name('tax.update');

        Route::post('/email/update', function () {
            // Update email configuration
        })->name('email.update');

        Route::post('/email-templates/{id}/update', function ($id) {
            // Update email template
        })->name('email-templates.update');

        Route::post('/backup/create', function () {
            // Create backup
        })->name('backup.create');

        Route::post('/backup/restore', function () {
            // Restore backup
        })->name('backup.restore');
    });
});
