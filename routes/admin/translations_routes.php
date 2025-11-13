<?php
/**
 * Translation Routes for Admin
 *
 * Add these routes to your web.php or admin routes file
 * These should be inside the admin middleware group
 */
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\TranslationsController;

// Translations Management Routes
Route::prefix('translations')->name('translations.')->group(function () {
    Route::get('/get', [TranslationsController::class, 'getTranslations'])->name('get');
    Route::get('/get-all', [TranslationsController::class, 'getAllTranslations'])->name('get-all');
    Route::post('/save', [TranslationsController::class, 'saveTranslations'])->name('save');
    Route::post('/bulk-save', [TranslationsController::class, 'bulkSaveTranslations'])->name('bulk-save');
    Route::delete('/delete', [TranslationsController::class, 'deleteTranslation'])->name('delete');
    Route::delete('/delete-field', [TranslationsController::class, 'deleteFieldTranslation'])->name('delete-field');
    Route::get('/stats', [TranslationsController::class, 'getStats'])->name('stats');
    Route::post('/clone', [TranslationsController::class, 'cloneTranslation'])->name('clone');
    Route::get('/languages', [TranslationsController::class, 'getAvailableLanguages'])->name('languages');
    Route::get('/fields', [TranslationsController::class, 'getTranslatableFields'])->name('fields');
    Route::get('/search', [TranslationsController::class, 'searchTranslations'])->name('search');
});
