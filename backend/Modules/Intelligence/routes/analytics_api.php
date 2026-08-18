<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Intelligence\Analytics\Http\Controllers\AnalyticsController;

Route::prefix('v1')->group(function (): void {
    // Public Tracking
    Route::prefix('public/analytics')->group(function (): void {
        Route::post('/track-visit', [AnalyticsController::class, 'trackVisit'])->middleware('throttle:analytics-visit');
        Route::post('/track', [AnalyticsController::class, 'trackEvent'])->middleware('throttle:120,1');
        Route::post('/track/batch', [AnalyticsController::class, 'trackBatch'])->middleware('throttle:120,1');
    });

    // Console Management
    Route::prefix('manage/analytics')->middleware(['auth:sanctum', 'permission:view analytics'])->group(function (): void {
        Route::get('overview', [AnalyticsController::class, 'overview']);
        Route::get('visits', [AnalyticsController::class, 'visits']);
        Route::get('top-pages', [AnalyticsController::class, 'topPages']);
        Route::get('top-content', [AnalyticsController::class, 'topContent']);
        Route::get('devices', [AnalyticsController::class, 'devices']);
        Route::get('browsers', [AnalyticsController::class, 'browsers']);
        Route::get('countries', [AnalyticsController::class, 'countries']);
        Route::get('referrers', [AnalyticsController::class, 'referrers']);
        Route::get('events', [AnalyticsController::class, 'events']);
        Route::get('event-stats', [AnalyticsController::class, 'eventStats']);
        Route::get('realtime', [AnalyticsController::class, 'realTime'])->middleware('throttle:120,1');
        Route::get('export', [AnalyticsController::class, 'export']);
        Route::post('cleanup', [AnalyticsController::class, 'cleanup'])->middleware('permission:manage settings');
        Route::post('purge-all', [AnalyticsController::class, 'purgeAll'])->middleware(['permission:manage settings', 'throttle:5,60']);
    });

    // Legacy Bridge
    Route::prefix('manage/publishing/analytics')->middleware(['auth:sanctum', 'permission:view analytics'])->group(function (): void {
        Route::get('overview', [AnalyticsController::class, 'overview']);
        Route::get('visits', [AnalyticsController::class, 'visits']);
        Route::get('top-pages', [AnalyticsController::class, 'topPages']);
        Route::get('top-content', [AnalyticsController::class, 'topContent']);
        Route::get('devices', [AnalyticsController::class, 'devices']);
        Route::get('browsers', [AnalyticsController::class, 'browsers']);
        Route::get('countries', [AnalyticsController::class, 'countries']);
        Route::get('referrers', [AnalyticsController::class, 'referrers']);
        Route::get('events', [AnalyticsController::class, 'events']);
        Route::get('event-stats', [AnalyticsController::class, 'eventStats']);
    });

    Route::prefix('manage/publishing/analytics-legacy')->middleware(['auth:sanctum'])->group(function (): void {
        Route::get('overview', [AnalyticsController::class, 'overview']);
        Route::get('visits', [AnalyticsController::class, 'visits']);
    });
});
