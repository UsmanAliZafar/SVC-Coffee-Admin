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
        require __DIR__ . '/api/categories_routes.php';
        require __DIR__ . '/api/products_routes.php';
        require __DIR__ . '/api/orders_routes.php';
        require __DIR__ . '/api/customers_routes.php';
});
