<?php
// routes/apis/newsletters_routes.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\NewsletterController;

/*
|--------------------------------------------------------------------------
| Newsletter API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('newsletter')->group(function () {
    Route::post('/subscribe', [NewsletterController::class, 'subscribe']);
    Route::post('/unsubscribe', [NewsletterController::class, 'unsubscribe']);
});
