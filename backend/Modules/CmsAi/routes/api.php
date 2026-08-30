<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\CmsAi\Http\Controllers\AiController;
use Modules\CmsAi\Http\Controllers\AiTaxonomyBatchController;

Route::prefix('v1')->group(function (): void {
    Route::prefix('manage/ai')->middleware(['auth:sanctum', 'extension.active:cms-ai'])->group(function (): void {
        Route::post('draft-publishing', [AiController::class, 'draftPublishing']);
        Route::post('suggest-taxonomy', [AiController::class, 'suggestTaxonomy']);
        Route::get('taxonomy-batches', [AiTaxonomyBatchController::class, 'index']);
        Route::post('taxonomy-batches', [AiTaxonomyBatchController::class, 'store']);
        Route::get('taxonomy-batches/{id}', [AiTaxonomyBatchController::class, 'show']);
        Route::get('usage-stats', [AiController::class, 'usageStats']);
    });
});
