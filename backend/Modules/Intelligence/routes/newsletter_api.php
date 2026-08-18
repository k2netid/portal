<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Intelligence\Newsletter\Http\Controllers\Api\NewsletterController;

Route::prefix('v1')->group(function (): void {
    // Public API
    Route::prefix('public/newsletter')->group(function (): void {
        Route::post('subscribe', [NewsletterController::class, 'subscribe']);
        Route::post('unsubscribe', [NewsletterController::class, 'unsubscribe']);
    });

    // Manage API
    Route::middleware(['auth:sanctum'])->prefix('manage/newsletter')->group(function (): void {
        Route::get('subscribers', [NewsletterController::class, 'index']);
        Route::delete('subscribers/{id}', [NewsletterController::class, 'destroy']);
        Route::post('subscribers/{id}/restore', [NewsletterController::class, 'restore']);
        Route::delete('subscribers/{id}/force', [NewsletterController::class, 'forceDelete']);
        Route::get('subscribers/export', [NewsletterController::class, 'export']);
        Route::post('subscribers/bulk', [NewsletterController::class, 'bulkAction']);
    });
});
