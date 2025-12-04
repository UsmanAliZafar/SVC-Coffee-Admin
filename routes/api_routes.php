<?php
// routes/api_routes.php
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Main Entry Point
|--------------------------------------------------------------------------
|
| All routes here are automatically prefixed with /api
| Apply 'api.key' middleware to authenticate API requests
|
*/

Route::prefix('api')->name('api.')->group(function () {

    // Public API endpoints (NO authentication required)
    Route::prefix('public')->name('public.')->group(function () {
        Route::get('/health', function () {
            return response()->json([
                'status' => 'ok',
                'timestamp' => now(),
                'version' => '1.0'
            ]);
        });

        Route::get('/status', function () {
            return response()->json([
                'api' => 'SVC Ecommerce API',
                'status' => 'operational'
            ]);
        });
    });

    // Protected API endpoints (API Key authentication REQUIRED)
    Route::middleware('api.key')->group(function () {

        // Test endpoint to verify authentication
        Route::get('/test', function () {
            return response()->json([
                'success' => true,
                'message' => 'API authentication working!',
                'authenticated_key' => substr(request()->get('authenticated_api_key'), 0, 10) . '...',
                'timestamp' => now()
            ]);
        });

        // Load Module Routes (All protected by api.key middleware)
        require __DIR__ . '/apis/categories_routes.php';
        require __DIR__ . '/apis/products_routes.php';
        require __DIR__ . '/apis/orders_routes.php';
        require __DIR__ . '/apis/cart_routes.php';
        require __DIR__ . '/apis/checkout_routes.php';
        require __DIR__ . '/apis/customers_routes.php';
        require __DIR__ . '/apis/settings_routes.php';
        require __DIR__ . '/apis/contact_routes.php';
        require __DIR__ . '/apis/page_routes.php';
    });
});
