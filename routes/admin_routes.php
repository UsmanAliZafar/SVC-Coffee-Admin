<?php
// routes/admin_routes.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;

/*
|--------------------------------------------------------------------------
| Admin Routes - Main Entry Point
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {

    // Guest admin routes (login, forgot password, etc.)
    Route::middleware('admin.guest')->group(function () {
        Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
        Route::post('login', [AuthController::class, 'login']);
        Route::get('forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('forgot-password');
        Route::post('forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
    });

    // Protected admin routes
    Route::middleware('admin.auth')->group(function () {

        // Logout
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');

        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('dashboard', [DashboardController::class, 'index']);

        // Load Module Routes
        require __DIR__ . '/admin/products_routes.php';
        require __DIR__ . '/admin/categories_routes.php';
        require __DIR__ . '/admin/inventory_routes.php';
        require __DIR__ . '/admin/orders_routes.php';
        require __DIR__ . '/admin/customers_routes.php';
        require __DIR__ . '/admin/reports_routes.php';
        require __DIR__ . '/admin/user_management_routes.php';
        require __DIR__ . '/admin/settings_routes.php';
    });
});
