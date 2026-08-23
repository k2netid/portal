<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Analytics\Http\Controllers\AnalyticsController;

Route::prefix('v1')->group(function (): void {
    Route::prefix('public/analytics')->middleware(['extension.active:analytics'])->group(function (): void {
        Route::post('track-visit', [AnalyticsController::class, 'trackVisit'])->middleware('throttle:analytics-visit');
        Route::post('track', [AnalyticsController::class, 'trackEvent'])->middleware('throttle:120,1');
        Route::post('track/batch', [AnalyticsController::class, 'trackBatch'])->middleware('throttle:120,1');
    });

    Route::prefix('manage/analytics')->middleware(['auth:sanctum', 'extension.active:analytics'])->group(function (): void {
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

    Route::prefix('manage/publishing/analytics')->middleware(['auth:sanctum', 'extension.active:analytics', 'permission:view analytics'])->group(function (): void {
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
});
