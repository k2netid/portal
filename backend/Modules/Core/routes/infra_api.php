<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Core\Infra\Http\Controllers\Api\InfraRedirectController;
use Modules\Core\Infra\Http\Controllers\BackupController;
use Modules\Core\Infra\Http\Controllers\FileManagerController;
use Modules\Core\System\Http\Controllers\Console\WebhookController;

Route::prefix('v1')->group(function (): void {
    // Console Management
    Route::prefix('manage/infra')->middleware(['auth:sanctum'])->group(function (): void {
        // Backups
        Route::get('backups/stats', [BackupController::class, 'stats']);
        Route::get('backups/statistics', [BackupController::class, 'stats']);
        Route::post('backups/{backup}/restore', [BackupController::class, 'restore']);
        Route::get('backups/{backup}/download', [BackupController::class, 'download']);
        Route::apiResource('backups', BackupController::class);

        // Webhooks
        Route::get('webhooks/deliveries/recent', [WebhookController::class, 'recentDeliveries']);
        Route::get('webhooks/{webhook}/deliveries', [WebhookController::class, 'deliveries']);
        Route::post('webhooks/{webhook}/trigger', [WebhookController::class, 'trigger']);
        Route::apiResource('webhooks', WebhookController::class);

        // File Manager
        Route::get('file-manager', [FileManagerController::class, 'index']);
        Route::post('file-manager/upload', [FileManagerController::class, 'upload']);

        // Redirects
        Route::apiResource('redirects', InfraRedirectController::class)->names([
            'index' => 'infra.redirects.index',
            'store' => 'infra.redirects.store',
            'show' => 'infra.redirects.show',
            'update' => 'infra.redirects.update',
            'destroy' => 'infra.redirects.destroy',
        ]);
        Route::patch('redirects/{redirect}/toggle', [InfraRedirectController::class, 'toggle'])->name('infra.redirects.toggle');
    });

    // System Backups compatibility routes (maps api/v1/manage/system/backups to BackupController)
    Route::prefix('manage/system')->middleware(['auth:sanctum'])->group(function (): void {
        Route::get('backups/stats', [BackupController::class, 'stats'])->name('compat.api.backups.stats');
        Route::get('backups/statistics', [BackupController::class, 'stats'])->name('compat.api.backups.statistics');
        Route::post('backups/{backup}/restore', [BackupController::class, 'restore'])->name('compat.api.backups.restore');
        Route::get('backups/{backup}/download', [BackupController::class, 'download'])->name('compat.api.backups.download');
        Route::apiResource('backups', BackupController::class)->names([
            'index' => 'compat.api.backups.index',
            'store' => 'compat.api.backups.store',
            'show' => 'compat.api.backups.show',
            'update' => 'compat.api.backups.update',
            'destroy' => 'compat.api.backups.destroy',
        ]);
    });

});
