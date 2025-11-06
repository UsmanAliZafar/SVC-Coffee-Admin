<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PageController;
// routes/admin/pages_routes.php
/*
|--------------------------------------------------------------------------
| Admin Pages Routes
|--------------------------------------------------------------------------
|
*/
// Pages Management
Route::controller(PageController::class)->prefix('pages')->name('pages.')->group(function () {

    // Standard CRUD routes
    Route::get('/', 'index')->name('index');
    Route::get('/create', 'create')->name('create');
    Route::post('/', 'store')->name('store');
    Route::get('/{page}', 'show')->name('show');
    Route::get('/{page}/edit', 'edit')->name('edit');
    Route::put('/{page}', 'update')->name('update');
    Route::delete('/{page}', 'destroy')->name('destroy');

    // Additional actions
    Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
    Route::post('/{page}/publish', 'publish')->name('publish');
    Route::post('/{page}/unpublish', 'unpublish')->name('unpublish');
    Route::post('/{page}/archive', 'archive')->name('archive');
    Route::post('/{page}/duplicate', 'duplicate')->name('duplicate');
    Route::post('/update-order', 'updateOrder')->name('update-order');
    Route::get('/{page}/preview', 'preview')->name('preview');
});


