<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // Redirect to admin login or show 404-style landing
    return view('errors.404');
});

// Catch all other routes that don't exist
Route::fallback(function () {
    return view('errors.404');
});
