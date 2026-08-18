<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Content\Media\Http\Controllers\Api\FolderController;
use Modules\Content\Media\Http\Controllers\Api\MediaController;
use Modules\Core\Infra\Http\Controllers\FileManagerController;

Route::prefix('v1/manage')->middleware(['auth:sanctum'])->group(function (): void {
    // Media Routes
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

    // Folder Routes
    Route::prefix('folders')->group(function (): void {
        Route::get('/', [FolderController::class, 'index']);
        Route::post('/', [FolderController::class, 'store']);
        Route::put('/{folder}', [FolderController::class, 'update']);
        Route::delete('/{folder}', [FolderController::class, 'destroy']);
    });

    // File Manager Routes (from FileManagerController)
    Route::prefix('infra/file-manager')->group(function (): void {
        Route::get('/', [FileManagerController::class, 'index']);
        Route::post('/upload', [FileManagerController::class, 'upload']);
        Route::get('/download', [FileManagerController::class, 'download']);
        Route::post('/delete', [FileManagerController::class, 'delete']);
        Route::post('/folder/delete', [FileManagerController::class, 'deleteFolder']);
        Route::post('/folder', [FileManagerController::class, 'createFolder']);
        Route::post('/move', [FileManagerController::class, 'move']);
        Route::post('/copy', [FileManagerController::class, 'copy']);
        Route::post('/rename', [FileManagerController::class, 'rename']);
        Route::get('/trash', [FileManagerController::class, 'trash']);
        Route::post('/restore', [FileManagerController::class, 'restore']);
        Route::post('/trash/empty', [FileManagerController::class, 'emptyTrash']);
        Route::post('/trash/permanent', [FileManagerController::class, 'deletePermanently']);
        Route::post('/extract', [FileManagerController::class, 'extract']);
        Route::post('/compress', [FileManagerController::class, 'compress']);
    });
});
