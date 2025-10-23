<?php
// routes/admin/profile_routes.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ProfileController;

/*
|--------------------------------------------------------------------------
| Profile Management Routes
|--------------------------------------------------------------------------
*/

Route::prefix('profile')->name('profile.')->group(function () {
    Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
    Route::put('/update', [ProfileController::class, 'update'])->name('update');
    Route::put('/update-password', [ProfileController::class, 'updatePassword'])->name('update-password');
});
