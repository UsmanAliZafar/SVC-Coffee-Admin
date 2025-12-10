<?php
// routes/apis/page_routes.php
use App\Http\Controllers\Api\PageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Page API Routes
|--------------------------------------------------------------------------
|
| Here are the API routes for the Page model.
| All routes are read-only and return published, public pages only.
|
*/

// Group all page routes under 'pages' prefix with rate limiting
Route::prefix('pages')->name('pages.')->group(function () {
    // Get all pages with pagination
    // GET /api/pages?per_page=20
    Route::get('/', [PageController::class, 'index'])
        ->name('index');
    // Get pages for header menu
    // GET /api/pages/menu/header
    Route::get('/menu/header', [PageController::class, 'headerMenu'])
        ->name('menu.header');

    // Get pages for footer menu
    // GET /api/pages/menu/footer
    Route::get('/menu/footer', [PageController::class, 'footerMenu'])
        ->name('menu.footer');

    // Get parent pages only
    // GET /api/pages/parents
    Route::get('/parents', [PageController::class, 'parents'])
        ->name('parents');

    // Search pages
    // GET /api/pages/search?query=about&per_page=10
    Route::get('/search', [PageController::class, 'search'])
        ->name('search');

    // Get sitemap data
    // GET /api/pages/sitemap
    Route::get('/sitemap', [PageController::class, 'sitemap'])
        ->name('sitemap');

    // Get pages by template type
    // GET /api/pages/template/{template}
    Route::get('/template/{template}', [PageController::class, 'byTemplate'])
        ->name('by-template');

    // Get page by ID
    // GET /api/pages/id/{id}
    Route::get('/id/{id}', [PageController::class, 'showById'])
        ->name('show-by-id');

    // Get child pages of a parent
    // GET /api/pages/{parentSlug}/children
    Route::get('/{parentSlug}/children', [PageController::class, 'children'])
        ->name('children');

    // Get page breadcrumbs
    // GET /api/pages/{slug}/breadcrumbs
    Route::get('/{slug}/breadcrumbs', [PageController::class, 'breadcrumbs'])
        ->name('breadcrumbs');

    // Get single page by slug (should be last to avoid conflicts)
    // GET /api/pages/{slug}
    Route::get('/{slug}', [PageController::class, 'show'])
        ->name('show');


});
