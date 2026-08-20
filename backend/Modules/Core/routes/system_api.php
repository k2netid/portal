<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Core\System\Http\Controllers\Api\ConfigServerController;
use Modules\Core\System\Http\Controllers\Api\DynamicApiController;
use Modules\Core\System\Http\Controllers\Api\MaintenanceApiController;
use Modules\Core\System\Http\Controllers\Api\ScaffolderApiController;
use Modules\Core\System\Http\Controllers\Api\ScimUserController;
use Modules\Core\System\Http\Controllers\Console\ActivityLogController;
use Modules\Core\System\Http\Controllers\Console\AuthController;
use Modules\Core\System\Http\Controllers\Console\CaptchaController;
use Modules\Core\System\Http\Controllers\Console\ConsoleThemeController;
use Modules\Core\System\Http\Controllers\Console\DashboardController;
use Modules\Core\System\Http\Controllers\Console\EmailTemplateController;
use Modules\Core\System\Http\Controllers\Console\EmailTestController;
use Modules\Core\System\Http\Controllers\Console\ExtensionController;
use Modules\Core\System\Http\Controllers\Console\KycReviewController;
use Modules\Core\System\Http\Controllers\Console\LanguageController;
use Modules\Core\System\Http\Controllers\Console\LicenseController;
use Modules\Core\System\Http\Controllers\Console\LogController;
use Modules\Core\System\Http\Controllers\Console\LoginHistoryController;
use Modules\Core\System\Http\Controllers\Console\NotificationController;
use Modules\Core\System\Http\Controllers\Console\OAuthClientController;
use Modules\Core\System\Http\Controllers\Console\OnboardingStatusController;
use Modules\Core\System\Http\Controllers\Console\PluginController;
use Modules\Core\System\Http\Controllers\Console\ProfileKycController;
use Modules\Core\System\Http\Controllers\Console\PublicSettingsController;
use Modules\Core\System\Http\Controllers\Console\RedisController;
use Modules\Core\System\Http\Controllers\Console\RoleController;
use Modules\Core\System\Http\Controllers\Console\ScheduledTaskController;
use Modules\Core\System\Http\Controllers\Console\SettingController;
use Modules\Core\System\Http\Controllers\Console\SystemController;
use Modules\Core\System\Http\Controllers\Console\TranslationController;
use Modules\Core\System\Http\Controllers\Console\TwoFactorController;
use Modules\Core\System\Http\Controllers\Console\UserController;
use Modules\Core\System\Http\Middleware\ScimAuth;

Route::prefix('v1')->group(function (): void {
    // Captcha endpoints
    Route::prefix('captcha')->group(function (): void {
        Route::get('settings', [CaptchaController::class, 'settings']);
        Route::get('generate', [CaptchaController::class, 'generate']);
        Route::post('verify', [CaptchaController::class, 'verify']);
    });

    // Auth & Public (Surface canonical)
    Route::prefix('public/system/auth')->group(function (): void {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
        Route::get('me', [AuthController::class, 'user'])->middleware('auth:sanctum');
    });

    Route::get('public/system/settings', [PublicSettingsController::class, 'index']);
    Route::get('public/system/languages', [LanguageController::class, 'index']);
    Route::get('public/system/console-theme', [ConsoleThemeController::class, 'showPublic']);

    // Dashboard routes
    Route::prefix('dashboard')->middleware(['auth:sanctum'])->group(function (): void {
        Route::get('admin', [DashboardController::class, 'admin']);
        Route::get('creator', [DashboardController::class, 'creator']);
        Route::get('viewer', [DashboardController::class, 'viewer']);
    });

    // Two Factor Authentication
    Route::prefix('two-factor')->middleware(['auth:sanctum'])->group(function (): void {
        Route::get('status', [TwoFactorController::class, 'status']);
        Route::post('generate', [TwoFactorController::class, 'generate']);
        Route::post('verify', [TwoFactorController::class, 'verify']);
        Route::post('disable', [TwoFactorController::class, 'disable']);
        Route::post('regenerate-backup-codes', [TwoFactorController::class, 'regenerateBackupCodes']);
        Route::post('verify-code', [TwoFactorController::class, 'verifyCode']);
    });

    // Manage API (Canonical)
    Route::prefix('manage/system')->middleware(['auth:sanctum'])->group(function (): void {
        Route::get('dashboard', [DashboardController::class, 'admin']);
        Route::get('onboarding-status', [OnboardingStatusController::class, 'show']);
        Route::post('onboarding/dismiss', [OnboardingStatusController::class, 'dismiss']);

        // System Info, Health, Statistics, and Cache
        Route::get('info', [SystemController::class, 'info']);
        Route::get('health', [SystemController::class, 'health']);
        Route::get('health/detailed', [SystemController::class, 'systemHealth']);
        Route::get('statistics', [SystemController::class, 'statistics']);
        Route::get('cache-status', [SystemController::class, 'cacheStatus']);
        Route::post('cache/clear', [SystemController::class, 'clearCache']);
        Route::post('cache/warm', [SystemController::class, 'warmCache']);
        Route::get('cache-warming-stats', [SystemController::class, 'cacheWarmingStats']);
        Route::post('clear-rate-limit', [SystemController::class, 'clearRateLimit']);

        // OS Maintenance & Care Centre
        Route::post('maintenance/clean-junk', [MaintenanceApiController::class, 'cleanJunk']);
        Route::post('maintenance/optimize-db', [MaintenanceApiController::class, 'optimizeDatabase']);
        Route::post('maintenance/boost', [MaintenanceApiController::class, 'boost']);
        Route::post('maintenance/factory-reset', [MaintenanceApiController::class, 'factoryReset']);
        Route::post('maintenance/factory-reset/step-1', [MaintenanceApiController::class, 'factoryResetStep1']);
        Route::post('maintenance/factory-reset/step-2', [MaintenanceApiController::class, 'factoryResetStep2']);
        Route::post('maintenance/factory-reset/step-3', [MaintenanceApiController::class, 'factoryResetStep3']);
        Route::get('maintenance/post-reset-welcome', [MaintenanceApiController::class, 'checkWelcomePhase']);
        Route::post('maintenance/seed-fresh', [MaintenanceApiController::class, 'seedFresh']);
        Route::post('maintenance/dismiss-welcome', [MaintenanceApiController::class, 'dismissWelcome']);

        // Profile Management
        Route::get('profile', [UserController::class, 'profile']);
        Route::put('profile', [UserController::class, 'updateProfile']);
        Route::post('profile/username/check', [UserController::class, 'checkUsername']);
        Route::post('profile/avatar', [UserController::class, 'uploadAvatar']);
        Route::put('profile/password', [UserController::class, 'updatePassword']);
        Route::get('profile/preferences', [UserController::class, 'getPreferences']);
        Route::put('profile/preferences', [UserController::class, 'updatePreferences']);
        Route::get('profile/login-history', [UserController::class, 'loginHistory']);
        Route::get('profile/sessions', [UserController::class, 'activeSessions']);
        Route::delete('profile/sessions/{id}', [UserController::class, 'revokeSession']);
        Route::get('profile/passkeys', [UserController::class, 'passkeys']);
        Route::get('profile/kyc', [ProfileKycController::class, 'status']);
        Route::post('profile/kyc/basic', [ProfileKycController::class, 'completeBasic']);
        Route::post('profile/kyc/contact', [ProfileKycController::class, 'completeContact']);
        Route::post('profile/kyc/documents', [ProfileKycController::class, 'uploadDocument']);
        Route::post('profile/kyc/submit', [ProfileKycController::class, 'submit']);
        Route::get('profile/kyc/documents/{document}/download', [ProfileKycController::class, 'downloadOwnDocument']);
        Route::post('profile/kyc/step', [UserController::class, 'updateKycStep']);

        Route::get('users/stats', [UserController::class, 'stats']);
        Route::post('users/{user}/verify', [UserController::class, 'verify']);
        Route::post('users/{user}/force-logout', [UserController::class, 'forceLogout']);
        Route::post('users/{user}/restore', [UserController::class, 'restore']);
        Route::delete('users/{user}/force-delete', [UserController::class, 'forceDelete']);
        Route::post('users/bulk-action', [UserController::class, 'bulkAction']);
        Route::apiResource('users', UserController::class);
        Route::get('roles/permissions', [RoleController::class, 'permissions']);
        Route::apiResource('roles', RoleController::class);
        Route::middleware('permission:manage kyc reviews')->prefix('kyc')->group(function (): void {
            Route::get('submissions', [KycReviewController::class, 'index']);
            Route::get('submissions/{submission}', [KycReviewController::class, 'show']);
            Route::post('submissions/{submission}/approve', [KycReviewController::class, 'approve']);
            Route::post('submissions/{submission}/reject', [KycReviewController::class, 'reject']);
            Route::get('submissions/{submission}/documents/{document}/download', [KycReviewController::class, 'downloadDocument']);
        });

        Route::apiResource('oauth-clients', OAuthClientController::class)->except(['show']);

        Route::get('console-theme', [ConsoleThemeController::class, 'show']);
        Route::get('settings/group/{group}', [SettingController::class, 'getGroup']);
        Route::post('settings/test-storage', [SettingController::class, 'testStorage']);
        Route::post('settings/bulk-update', [SettingController::class, 'bulkUpdate']);
        Route::apiResource('settings', SettingController::class);

        Route::post('plugins/{plugin}/activate', [PluginController::class, 'activate']);
        Route::post('plugins/{plugin}/deactivate', [PluginController::class, 'deactivate']);
        Route::put('plugins/{plugin}/settings', [PluginController::class, 'updateSettings']);
        Route::apiResource('plugins', PluginController::class);
        Route::apiResource('languages', LanguageController::class);
        Route::post('languages/{language}/set-default', [LanguageController::class, 'setDefault']);
        Route::get('languages/{language}/export-pack', [LanguageController::class, 'exportPack']);
        Route::post('languages/import-pack', [LanguageController::class, 'importPack']);

        Route::get('notifications', [NotificationController::class, 'index']);
        Route::put('notifications/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::put('notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
        Route::delete('notifications/{notification}', [NotificationController::class, 'destroy']);

        Route::get('activity-journal', [ActivityLogController::class, 'index']);

        Route::get('translations', [TranslationController::class, 'getTranslations']);
        Route::post('translations', [TranslationController::class, 'setTranslation']);

        Route::get('scheduled-tasks/allowed-commands', [ScheduledTaskController::class, 'allowedCommands']);
        Route::post('scheduled-tasks/bulk', [ScheduledTaskController::class, 'bulk']);
        Route::post('scheduled-tasks/apply-preset', [ScheduledTaskController::class, 'applyPreset']);
        Route::post('scheduled-tasks/run-adhoc', [ScheduledTaskController::class, 'runAdhoc']);
        Route::post('scheduled-tasks/{id}/run', [ScheduledTaskController::class, 'run']);
        Route::apiResource('scheduled-tasks', ScheduledTaskController::class);

        Route::get('email-test/recent-journal', [EmailTestController::class, 'recentJournal']);
        Route::post('email-test/send', [EmailTestController::class, 'sendTestEmail']);

        Route::post('email-templates/preview', [EmailTemplateController::class, 'previewUnsaved']);
        Route::post('email-templates/{email_template}/preview', [EmailTemplateController::class, 'preview']);
        Route::post('email-templates/{email_template}/send-test', [EmailTemplateController::class, 'sendTest']);
        Route::apiResource('email-templates', EmailTemplateController::class);

        Route::get('logs', [LogController::class, 'index']);
        Route::get('logs/{filename}', [LogController::class, 'show']);
        Route::delete('logs/{filename}', [LogController::class, 'destroy']);
    });

    // Notifications Management API (Canonical for SPA)
    Route::prefix('manage/notifications')->middleware(['auth:sanctum'])->group(function (): void {
        Route::get('', [NotificationController::class, 'index']);
        Route::put('read-all', [NotificationController::class, 'markAllAsRead']);
        Route::put('{notification}/read', [NotificationController::class, 'markAsRead']);
        Route::delete('{notification}', [NotificationController::class, 'destroy']);

        Route::get('system', [NotificationController::class, 'indexSystem']);
        Route::post('system/revoke', [NotificationController::class, 'revokeSystem']);
        Route::post('system/bulk-revoke', [NotificationController::class, 'bulkRevokeSystem']);
        Route::post('broadcast', [NotificationController::class, 'broadcast']);
    });

    // System Journal routes for frontend compatibility (registered as api/v1/manage/system-journal)
    Route::prefix('manage/system-journal')->middleware(['auth:sanctum'])->group(function (): void {
        Route::get('', [LogController::class, 'index']);
        Route::get('{filename}', [LogController::class, 'show']);
        Route::get('{filename}/download', [LogController::class, 'download']);
        Route::post('clear', [LogController::class, 'clear']);
        Route::delete('{filename}', [LogController::class, 'destroy']);
    });

    // License Management & JA-CP Integration routes
    Route::prefix('system/license')->group(function (): void {
        Route::get('', [LicenseController::class, 'index']);
        Route::post('activate', [LicenseController::class, 'activate']);
        Route::post('refresh', [LicenseController::class, 'refresh']);
        Route::post('deactivate', [LicenseController::class, 'deactivate']);
    });

    // Redis Management routes
    Route::prefix('manage/redis')->middleware(['auth:sanctum'])->group(function (): void {
        Route::get('settings', [RedisController::class, 'index']);
        Route::put('settings', [RedisController::class, 'update']);
        Route::post('test-connection', [RedisController::class, 'testConnection']);
        Route::get('info', [RedisController::class, 'info']);
        Route::post('flush-cache', [RedisController::class, 'flushCache']);
        Route::post('warm-cache', [RedisController::class, 'warmCache']);
        Route::get('cache-stats', [RedisController::class, 'cacheStats']);
    });

    // Activity Journal routes for frontend compatibility
    Route::prefix('manage/activity-journal')->middleware(['auth:sanctum'])->group(function (): void {
        Route::get('', [ActivityLogController::class, 'index']);
        Route::get('statistics', [ActivityLogController::class, 'statistics']);
        Route::get('recent', [ActivityLogController::class, 'recent']);
        Route::post('clear', [ActivityLogController::class, 'clear']);
        Route::get('export', [ActivityLogController::class, 'export']);
    });

    // Access Journal / Login History routes for frontend compatibility
    Route::prefix('manage/access-journal')->middleware(['auth:sanctum'])->group(function (): void {
        Route::get('', [LoginHistoryController::class, 'index']);
        Route::get('statistics', [LoginHistoryController::class, 'statistics']);
        Route::get('suspicious', [LoginHistoryController::class, 'suspicious']);
        Route::post('clear', [LoginHistoryController::class, 'clear']);
        Route::get('export', [LoginHistoryController::class, 'export']);
    });

    // Extension Store & Plugin Manager
    Route::prefix('manage/infra/extensions')->middleware(['auth:sanctum'])->group(function (): void {
        Route::get('', [ExtensionController::class, 'index']);
        Route::get('navigation', [ExtensionController::class, 'navigation']);
        Route::post('upload', [ExtensionController::class, 'upload']);
        Route::post('git-clone', [ExtensionController::class, 'gitClone']);
        Route::put('features/{slug}/toggle', [ExtensionController::class, 'toggleFeature']);
        Route::post('{slug}/activate', [ExtensionController::class, 'activate']);
        Route::post('{slug}/deactivate', [ExtensionController::class, 'deactivate']);
        Route::put('{slug}/settings', [ExtensionController::class, 'updateSettings']);
        Route::delete('{slug}/uninstall', [ExtensionController::class, 'uninstall']);
    });

    // CCK Dynamic Content Type Schema Builder & Scaffolder
    Route::prefix('manage/infra/cck')->middleware(['auth:sanctum'])->group(function (): void {
        Route::get('types', [DynamicApiController::class, 'listTypes']);
        Route::post('types', [DynamicApiController::class, 'storeType']);
        Route::get('types/openapi-index', [DynamicApiController::class, 'openApiIndex']);
        Route::get('types/by-slug/{slug}/openapi', [DynamicApiController::class, 'openApiBySlug']);
        Route::get('types/by-slug/{slug}', [DynamicApiController::class, 'showTypeBySlug']);
        Route::get('types/{id}/validation-rules', [DynamicApiController::class, 'typeValidationRules']);
        Route::get('types/{id}', [DynamicApiController::class, 'showType']);
        Route::put('types/{id}', [DynamicApiController::class, 'updateType']);
        Route::delete('types/{id}', [DynamicApiController::class, 'destroyType']);
        Route::post('scaffold', [ScaffolderApiController::class, 'scaffold']);
    });

    // Instant API Generation EAV Endpoints
    Route::prefix('dynamic/{slug}')->group(function (): void {
        Route::get('', [DynamicApiController::class, 'index']);
        Route::post('', [DynamicApiController::class, 'store']);
        Route::get('{id}', [DynamicApiController::class, 'show']);
        Route::put('{id}', [DynamicApiController::class, 'update']);
        Route::delete('{id}', [DynamicApiController::class, 'destroy']);
    });
});

// SCIM 2.0 API (System for Cross-domain Identity Management)
// Placed outside the v1 prefix as SCIM uses its own v2 scheme
Route::prefix('scim/v2')->middleware([ScimAuth::class])->group(function (): void {
    Route::get('Users', [ScimUserController::class, 'index']);
    Route::get('Users/{id}', [ScimUserController::class, 'show']);
    Route::post('Users', [ScimUserController::class, 'store']);
    Route::put('Users/{id}', [ScimUserController::class, 'update']);
    Route::patch('Users/{id}', [ScimUserController::class, 'patch']);
    Route::delete('Users/{id}', [ScimUserController::class, 'destroy']);
});

// Centralized Config Server API
// Protected by OAuth2 Client Credentials (machine-to-machine)
Route::prefix('v1/config')->middleware(['client'])->group(function (): void {
    Route::get('resolve', [ConfigServerController::class, 'resolve']);
    Route::post('sync', [ConfigServerController::class, 'sync']);
});
