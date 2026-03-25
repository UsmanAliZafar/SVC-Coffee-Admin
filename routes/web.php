<?php

// Path: routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\EmailPreviewController;

Route::prefix('admin/email-preview')
    ->name('admin.email-preview.')
    ->group(function () {
        Route::get('/',        [EmailPreviewController::class, 'index'])->name('index');
        Route::get('/{email}', [EmailPreviewController::class, 'show'])->name('show');
    });

Route::get('/', function () {
    return view('errors.404');
});

Route::fallback(function () {
    return view('errors.404');
});
