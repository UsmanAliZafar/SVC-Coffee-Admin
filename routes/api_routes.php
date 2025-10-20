<?php
// routes/api_routes.php
use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| Apis Routes - Main Entry Point
|--------------------------------------------------------------------------
*/
Route::prefix('api')->name('api.')->group(function () {
        // Load Module Routes
        require __DIR__ . '/apis/categories_routes.php';
        require __DIR__ . '/apis/products_routes.php';
        require __DIR__ . '/apis/orders_routes.php';
        require __DIR__ . '/apis/customers_routes.php';
});
