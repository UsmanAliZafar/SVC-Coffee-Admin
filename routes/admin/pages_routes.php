<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PageController;

/*
|--------------------------------------------------------------------------
| Admin Pages Routes
|--------------------------------------------------------------------------
|
*/

Route::controller(PageController::class)->prefix('pages')->name('pages.')->group(function () {

    // List and Data
    Route::get('/', 'index')->name('index');
    Route::get('/get-data', 'getData')->name('get-data'); // AJAX DataTables endpoint

    // Create
    Route::get('/create', 'create')->name('create');
    Route::post('/', 'store')->name('store');

    // View
    Route::get('/{page}', 'show')->name('show');
    Route::get('/{page}/preview', 'preview')->name('preview');

    // Edit
    Route::get('/{page}/edit', 'edit')->name('edit');
    Route::put('/{page}', 'update')->name('update');

    // Delete
    Route::delete('/{page}', 'destroy')->name('destroy');

    // Bulk Actions
    Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
    Route::post('/bulk-status', 'bulkUpdateStatus')->name('bulk-status');

    // Status Management
    Route::post('/{page}/publish', 'publish')->name('publish');
    Route::post('/{page}/unpublish', 'unpublish')->name('unpublish');
    Route::post('/{page}/archive', 'archive')->name('archive');

    // Additional Actions
    Route::post('/{page}/duplicate', 'duplicate')->name('duplicate');
    Route::post('/update-order', 'updateOrder')->name('update-order');
});
