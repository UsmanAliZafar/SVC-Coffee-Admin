<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ContactUsController;

/*
|--------------------------------------------------------------------------
| Contact Us API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('contact-us')->group(function () {
    // Public route - Submit contact form
    Route::post('/', [ContactUsController::class, 'store']);
});
