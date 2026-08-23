<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Library\Http\Controllers\Api\CategoryController;
use Modules\Library\Http\Controllers\Api\CustomFieldController;
use Modules\Library\Http\Controllers\Api\FieldGroupController;
use Modules\Library\Http\Controllers\Api\TagController;

Route::prefix('v1')->group(function (): void {
    // Public Library API
    Route::prefix('public/library')->group(function (): void {
        Route::get('tags', [TagController::class, 'index']);
        Route::get('categories', [CategoryController::class, 'index']);
        Route::get('categories/{category}', [CategoryController::class, 'show']);
    });

    // Console Management (Library)
    Route::prefix('manage/library')->middleware(['auth:sanctum', 'extension.active:library'])->group(function (): void {
        Route::get('tags/statistics', [TagController::class, 'statistics'])->middleware('permission:manage tags');
        Route::post('tags/bulk-delete', [TagController::class, 'bulkDelete'])->middleware('permission:manage tags');
        Route::apiResource('tags', TagController::class)->middleware('permission:manage tags');
        Route::post('categories/bulk-destroy', [CategoryController::class, 'bulkDestroy']);
        Route::post('categories/{category}/move', [CategoryController::class, 'move']);
        Route::put('categories/{category}/restore', [CategoryController::class, 'restore']);
        Route::delete('categories/{category}/force-delete', [CategoryController::class, 'forceDelete']);
        Route::apiResource('categories', CategoryController::class);
        Route::apiResource('custom-fields', CustomFieldController::class);
        Route::apiResource('field-groups', FieldGroupController::class);
    });
});
