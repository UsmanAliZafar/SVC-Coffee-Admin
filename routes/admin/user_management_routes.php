<?php
// routes/admin/user_management_routes.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserManagement\AdminUserController;
use App\Http\Controllers\Admin\UserManagement\RoleController;
use App\Http\Controllers\Admin\UserManagement\PermissionController;

/*
|--------------------------------------------------------------------------
| User Management Routes (Admin Users, Roles, Permissions)
|--------------------------------------------------------------------------
*/

// Admin Users Management
Route::prefix('users')->name('users.')->group(function () {

    // AJAX Routes (define BEFORE resource routes)
    Route::get('/ajax-data', [AdminUserController::class, 'getData'])->name('data');
    Route::get('/ajax-stats', [AdminUserController::class, 'getStats'])->name('stats');

    // Specific routes (define BEFORE resource routes)
    Route::post('/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('toggle-status');

    // Resource Routes (define LAST)
    Route::resource('/', AdminUserController::class)->parameters(['' => 'user'])->names([
        'index' => 'index',
        'create' => 'create',
        'store' => 'store',
        'show' => 'show',
        'edit' => 'edit',
        'update' => 'update',
        'destroy' => 'destroy'
    ]);
});

// Roles Management
Route::prefix('roles')->name('roles.')->group(function () {

    // AJAX Routes (define BEFORE resource routes)
    Route::get('/ajax-data', [RoleController::class, 'getData'])->name('data');
    Route::get('/ajax-stats', [RoleController::class, 'getStats'])->name('stats');

    // Specific routes (define BEFORE resource routes)
    Route::post('/{role}/toggle-status', [RoleController::class, 'toggleStatus'])->name('toggle-status');

    // Resource Routes (define LAST)
    Route::resource('/', RoleController::class)->parameters(['' => 'role'])->names([
        'index' => 'index',
        'create' => 'create',
        'store' => 'store',
        'show' => 'show',
        'edit' => 'edit',
        'update' => 'update',
        'destroy' => 'destroy'
    ]);
});

// Permissions Management
Route::prefix('permissions')->name('permissions.')->group(function () {

    // AJAX Routes (define BEFORE resource routes)
    Route::get('/ajax-data', [PermissionController::class, 'getData'])->name('data');
    Route::get('/ajax-stats', [PermissionController::class, 'getStats'])->name('stats');

    // Bulk Operations Routes (define BEFORE resource routes)
    Route::get('/bulk-create-form', [PermissionController::class, 'showBulkCreate'])->name('bulk-create');
    Route::post('/bulk-create', [PermissionController::class, 'bulkCreate'])->name('bulk-create.store');

    // Existing Ajax Routes (define BEFORE resource routes)
    Route::get('/check-availability', [PermissionController::class, 'checkAvailability'])->name('check-availability');
    Route::get('/get-by-module', [PermissionController::class, 'getByModule'])->name('get-by-module');

    // Resource Routes (define LAST)
    Route::resource('/', PermissionController::class)->parameters(['' => 'permission'])->names([
        'index' => 'index',
        'create' => 'create',
        'store' => 'store',
        'show' => 'show',
        'edit' => 'edit',
        'update' => 'update',
        'destroy' => 'destroy'
    ]);
});
