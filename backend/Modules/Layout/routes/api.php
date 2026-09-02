<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Layout\Http\Controllers\Api\BuilderController;
use Modules\Layout\Http\Controllers\Api\BuilderPresetController;
use Modules\Layout\Http\Controllers\Api\MenuController;
use Modules\Layout\Http\Controllers\Api\PluginThemeSlotsController;
use Modules\Layout\Http\Controllers\Api\PublicPluginBlocksController;
use Modules\Layout\Http\Controllers\Api\RedirectController;
use Modules\Layout\Http\Controllers\Api\ThemeController;
use Modules\Layout\Http\Controllers\Api\WidgetController;

Route::prefix('v1')->group(function (): void {
    Route::prefix('public/layout')->group(function (): void {
        Route::get('menus/location/{location}', [MenuController::class, 'getByLocation']);
        Route::get('menus/{menu}', [MenuController::class, 'showPublic']);
        Route::get('widgets/location/{location}', [WidgetController::class, 'getByLocation']);
        Route::get('themes/active', [ThemeController::class, 'getActive']);
        Route::get('plugin-blocks', [PublicPluginBlocksController::class, 'index']);
    });

    Route::prefix('manage/layout')->middleware(['auth:sanctum', 'extension.active:layout'])->group(function (): void {
        // Menus
        Route::get('menus/locations', [MenuController::class, 'locations']);
        Route::get('menus/location/{location}', [MenuController::class, 'getByLocation']);
        Route::get('menus/{menu}/usage', [MenuController::class, 'usage']);
        Route::post('menus/{menu}/restore', [MenuController::class, 'restore']);
        Route::delete('menus/{menu}/force-delete', [MenuController::class, 'forceDestroy']);
        Route::get('menus/{menu}/items', [MenuController::class, 'listItems']);
        Route::post('menus/{menu}/items/sync', [MenuController::class, 'syncItems']);
        Route::post('menus/{menu}/items', [MenuController::class, 'addItem']);
        Route::put('menus/{menu}/items/{item}', [MenuController::class, 'updateItem']);
        Route::delete('menus/{menu}/items/{item}', [MenuController::class, 'deleteItem']);
        Route::post('menus/{menu}/reorder', [MenuController::class, 'reorderItems']);
        Route::apiResource('menus', MenuController::class);

        // Widgets
        Route::get('widgets/locations', [WidgetController::class, 'locations']);
        Route::post('widgets/reorder', [WidgetController::class, 'reorder']);
        Route::apiResource('widgets', WidgetController::class);

        // Redirects
        Route::get('redirects/statistics', [RedirectController::class, 'statistics'])->name('layout.redirects.statistics');
        Route::apiResource('redirects', RedirectController::class)->names([
            'index' => 'layout.redirects.index',
            'store' => 'layout.redirects.store',
            'show' => 'layout.redirects.show',
            'update' => 'layout.redirects.update',
            'destroy' => 'layout.redirects.destroy',
        ]);

        // Themes
        Route::get('themes/active', [ThemeController::class, 'getActive']);
        Route::get('themes/active/locations', [ThemeController::class, 'locations']);
        Route::get('themes/available', [ThemeController::class, 'available']);
        Route::post('themes/{theme}/activate', [ThemeController::class, 'activate']);
        Route::get('themes/upload-status', [ThemeController::class, 'uploadStatus']);
        Route::post('themes/install', [ThemeController::class, 'install']);
        Route::post('themes/scan', [ThemeController::class, 'scan']);
        Route::match(['put', 'patch'], 'themes/{theme}/customization', [ThemeController::class, 'updateCustomization']);
        Route::match(['put', 'patch'], 'themes/{theme}/settings', [ThemeController::class, 'updateSettings']);
        Route::match(['put', 'patch'], 'themes/{theme}/custom-css', [ThemeController::class, 'updateCustomCss']);
        Route::get('themes/{theme}/components', [ThemeController::class, 'getComponents']);
        Route::get('themes/{theme}/config', [ThemeController::class, 'getConfig']);
        Route::get('themes/{theme}/composables', [ThemeController::class, 'getComposables']);
        Route::post('themes/{theme}/validate', [ThemeController::class, 'validate']);
        Route::post('themes/{theme}/install-sample', [ThemeController::class, 'installSample']);
        Route::get('plugin-theme-slots', [PluginThemeSlotsController::class, 'index']);
        Route::apiResource('themes', ThemeController::class);

        // Visual Builder
        Route::prefix('builder')->group(function (): void {
            Route::get('dynamic-sources', [BuilderController::class, 'dynamicSources']);
            Route::post('resolve-dynamic', [BuilderController::class, 'resolveDynamic']);
            Route::post('generate-blocks', [BuilderController::class, 'generateBlocks']);
        });
        Route::apiResource('builder-presets', BuilderPresetController::class);
    });
});
