<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Media\Http\Controllers\Api\FolderController;
use Modules\Media\Http\Controllers\Api\MediaController;

Route::prefix('v1')->group(function (): void {
    Route::prefix('manage')->middleware(['auth:sanctum', 'extension.active:media'])->group(function (): void {
        Route::prefix('media')->group(function (): void {
            Route::get('/', [MediaController::class, 'index']);
            Route::get('/statistics', [MediaController::class, 'statistics']);
            Route::get('/filters', [MediaController::class, 'filters']);
            Route::post('/upload', [MediaController::class, 'upload']);
            Route::post('/bulk-action', [MediaController::class, 'bulk']);
            Route::post('/empty-trash', [MediaController::class, 'emptyTrash']);
            Route::get('/{file}', [MediaController::class, 'show']);
            Route::put('/{file}', [MediaController::class, 'update']);
            Route::delete('/{file}', [MediaController::class, 'destroy']);
            Route::post('/{id}/restore', [MediaController::class, 'restore']);
            Route::get('/{file}/usage', [MediaController::class, 'usage']);
            Route::post('/{file}/thumbnail', [MediaController::class, 'thumbnail']);
            Route::post('/{file}/resize', [MediaController::class, 'resize']);
        });

        Route::prefix('folders')->group(function (): void {
            Route::get('/', [FolderController::class, 'index']);
            Route::post('/', [FolderController::class, 'store']);
            Route::put('/{folder}', [FolderController::class, 'update']);
            Route::delete('/{folder}', [FolderController::class, 'destroy']);
        });
    });
});
