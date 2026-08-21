<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Core\Security\Http\Controllers\AbacPolicyController;
use Modules\Core\Security\Http\Controllers\CspReportController;
use Modules\Core\Security\Http\Controllers\SecurityController;
use Modules\Core\Security\Http\Controllers\SiemExportController;
use Modules\Core\System\Http\Controllers\Console\DependencyVulnerabilityController;

Route::prefix('v1')->group(function (): void {
    // Security Public (Infrastructure Layer)
    Route::post('/security/csp-report', [CspReportController::class, 'store'])->middleware('throttle:100,1');
    Route::post('/security/crep-collect', [CspReportController::class, 'store'])->middleware('throttle:100,1');
    Route::post('/security/verify-connection', [SecurityController::class, 'verifyConnection'])->middleware('throttle:60,1');
    Route::post('/journal/frontend', [SecurityController::class, 'storeFrontendLog'])->middleware('throttle:100,1');

    // Console Management (Admin Layer)
    Route::prefix('manage/security')->middleware(['auth:sanctum', 'throttle:120,1'])->group(function (): void {
        Route::middleware('permission:manage security logs|manage security operations')->group(function (): void {
            Route::get('journal', [SecurityController::class, 'index']);
            Route::delete('journal', [SecurityController::class, 'clear']);
            Route::get('journal/{securityLog}', [SecurityController::class, 'show']);
            Route::get('stats', [SecurityController::class, 'stats']);
            Route::get('alerts', [SecurityController::class, 'alerts']);
            Route::get('health', [SecurityController::class, 'health']);
            Route::get('kpi', [SecurityController::class, 'kpi']);
            Route::get('settings', [SecurityController::class, 'getSettings']);
            Route::put('settings', [SecurityController::class, 'updateSettings']);
            Route::get('siem/exports', [SiemExportController::class, 'index']);
            Route::post('test-notification', [SecurityController::class, 'testNotification']);
            Route::get('csp-reports', [CspReportController::class, 'index']);
            Route::get('csp-reports/statistics', [CspReportController::class, 'statistics']);
            Route::post('csp-reports/bulk-action', [CspReportController::class, 'bulkAction']);

            if (class_exists('Modules\Intelligence\Analytics\Http\Controllers\SlowQueryController')) {
                Route::get('slow-queries', [\Modules\Intelligence\Analytics\Http\Controllers\SlowQueryController::class, 'index']);
                Route::get('slow-queries/statistics', [\Modules\Intelligence\Analytics\Http\Controllers\SlowQueryController::class, 'statistics']);
            }

            Route::get('dependency-vulnerabilities', [DependencyVulnerabilityController::class, 'index']);
            Route::get('dependency-vulnerabilities/statistics', [DependencyVulnerabilityController::class, 'statistics']);
            Route::put('dependency-vulnerabilities/{id}', [DependencyVulnerabilityController::class, 'update'])->whereNumber('id');
            Route::post('run-dependency-audit', [DependencyVulnerabilityController::class, 'runAudit']);

            Route::get('dependency-packages', [DependencyPackageController::class, 'index']);
            Route::get('dependency-packages/statistics', [DependencyPackageController::class, 'statistics']);

            Route::get('maintenance', [SecurityController::class, 'maintenanceStatus']);
            Route::post('maintenance/activate', [SecurityController::class, 'maintenanceActivate']);
            Route::post('maintenance/deactivate', [SecurityController::class, 'maintenanceDeactivate']);

            // Bot Shield
            Route::get('shield/journal', [SecurityController::class, 'shieldJournal']);
            Route::get('shield/stats', [SecurityController::class, 'shieldStats']);
            Route::post('shield/clear', [SecurityController::class, 'clearShieldLogs']);
        });

        Route::middleware('permission:manage security ip-lists|manage security operations')->group(function (): void {
            Route::get('blocklist', [SecurityController::class, 'getBlocklist']);
            Route::post('block-ip', [SecurityController::class, 'blockIp']);
            Route::post('unblock-ip', [SecurityController::class, 'unblockIp']);
            Route::post('bulk-block', [SecurityController::class, 'bulkBlock']);
            Route::post('bulk-unblock', [SecurityController::class, 'bulkUnblock']);

            Route::get('whitelist', [SecurityController::class, 'getWhitelist']);
            Route::post('whitelist', [SecurityController::class, 'addToWhitelist']);
            Route::post('remove-whitelist', [SecurityController::class, 'removeFromWhitelist']);
            Route::post('bulk-remove-whitelist', [SecurityController::class, 'bulkRemoveWhitelist']);

            Route::get('check-ip', [SecurityController::class, 'checkIp']);
        });

        Route::middleware('permission:manage security integrity|manage security operations')->group(function (): void {
            Route::get('threat-analysis', [SecurityController::class, 'threatAnalysis']);
            Route::get('auto-tune/logs', [SecurityController::class, 'autoTuneLogs']);
            Route::get('file-integrity', [SecurityController::class, 'fileIntegrityStatus']);
            Route::post('file-integrity/resync', [SecurityController::class, 'resyncFileIntegrity']);
            Route::post('run-integrity-check', [SecurityController::class, 'runIntegrityCheck']);
        });

        Route::middleware('permission:manage security operations')->group(function (): void {
            Route::apiResource('abac-policies', AbacPolicyController::class);
        });
    });
});
