<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Search\Http\Controllers\Api\SearchController;

Route::prefix('v1')->group(function (): void {
    Route::prefix('public/search')->middleware(['extension.active:search', 'throttle:search-public'])->group(function (): void {
        Route::get('/', [SearchController::class, 'search']);
        Route::get('/suggestions', [SearchController::class, 'suggestions'])->middleware('throttle:search-suggestions');
    });

    Route::prefix('manage/search')->middleware(['auth:sanctum', 'extension.active:search', 'permission:manage search'])->group(function (): void {
        Route::get('queries', [SearchController::class, 'getQueries']);
        Route::delete('queries/{id}', [SearchController::class, 'deleteQuery']);
        Route::post('queries/clear', [SearchController::class, 'clearQueries']);
        Route::post('reindex', [SearchController::class, 'reindex']);
        Route::get('index-health', [SearchController::class, 'getIndexHealth']);
        Route::get('stats', [SearchController::class, 'getStats']);
    });
});
