<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Newsletter\Http\Controllers\Api\NewsletterController;

Route::prefix('v1')->group(function (): void {
    Route::prefix('public/newsletter')->middleware(['extension.active:newsletter'])->group(function (): void {
        Route::post('subscribe', [NewsletterController::class, 'subscribe']);
        Route::post('unsubscribe', [NewsletterController::class, 'unsubscribe']);
    });

    Route::prefix('manage/newsletter')->middleware(['auth:sanctum', 'extension.active:newsletter'])->group(function (): void {
        Route::get('subscribers', [NewsletterController::class, 'index']);
        Route::get('subscribers/export', [NewsletterController::class, 'export']);
        Route::post('subscribers/bulk', [NewsletterController::class, 'bulkAction']);
        Route::delete('subscribers/{id}', [NewsletterController::class, 'destroy']);
        Route::post('subscribers/{id}/restore', [NewsletterController::class, 'restore']);
        Route::delete('subscribers/{id}/force', [NewsletterController::class, 'forceDelete']);
    });
});
