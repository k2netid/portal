<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Intelligence\Ai\Http\Controllers\AiController;
use Modules\Intelligence\Ai\Http\Controllers\AiTaxonomyBatchController;

Route::prefix('v1')->group(function (): void {
    // Console Management
    Route::prefix('manage/ai')->middleware(['auth:sanctum'])->group(function (): void {
        Route::get('providers', [AiController::class, 'getProviders']);
        Route::get('models/{provider}', [AiController::class, 'getModels']);
        Route::post('generate', [AiController::class, 'generate']);
        Route::post('draft-publishing', [AiController::class, 'draftPublishing']);
        Route::post('suggest-taxonomy', [AiController::class, 'suggestTaxonomy']);
        Route::get('taxonomy-batches', [AiTaxonomyBatchController::class, 'index']);
        Route::post('taxonomy-batches', [AiTaxonomyBatchController::class, 'store']);
        Route::get('taxonomy-batches/{id}', [AiTaxonomyBatchController::class, 'show']);
        Route::get('usage-stats', [AiController::class, 'usageStats']);
    });
});
