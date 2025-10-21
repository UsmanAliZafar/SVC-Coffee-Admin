<?php
// routes/apis/categories_routes.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoriesController;

/*
|--------------------------------------------------------------------------
| Categories API Routes (Read-Only for Frontend)
|--------------------------------------------------------------------------
|
| These routes are already protected by 'api.key' middleware
| from the parent group in api_routes.php
|
| All routes are READ-ONLY (GET requests only)
| No Create, Update, or Delete operations
|
*/

Route::prefix('categories')->name('categories.')->group(function () {

    // Special routes (must come before {id} route)
    Route::get('/tree', [CategoriesController::class, 'tree'])->name('tree');
    Route::get('/roots', [CategoriesController::class, 'roots'])->name('roots');
    Route::get('/featured', [CategoriesController::class, 'featured'])->name('featured');
    Route::get('/menu', [CategoriesController::class, 'menu'])->name('menu');
    Route::get('/homepage', [CategoriesController::class, 'homepage'])->name('homepage');
    Route::get('/slug/{slug}', [CategoriesController::class, 'getBySlug'])->name('by_slug');
    Route::get('/slug/{slug}/products', [CategoriesController::class, 'productsBySlug'])->name('products_by_slug');

    // Get all categories with filters and pagination
    Route::get('/', [CategoriesController::class, 'index'])->name('index');

    // Get single category by ID
    Route::get('/{id}', [CategoriesController::class, 'show'])->name('show');

    // Get category breadcrumbs
    Route::get('/{id}/breadcrumbs', [CategoriesController::class, 'breadcrumbs'])->name('breadcrumbs');

    // Get products by category ID
    Route::get('/{id}/products', [CategoriesController::class, 'products'])->name('products');

    // Get featured products by category ID
    Route::get('/{id}/featured-products', [CategoriesController::class, 'featuredProducts'])->name('featured_products');
});
