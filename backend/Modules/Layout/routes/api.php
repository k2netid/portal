<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Layout\Http\Controllers\Api\MenuController;
use Modules\Layout\Http\Controllers\Api\RedirectController;
use Modules\Layout\Http\Controllers\Api\WidgetController;

Route::prefix('v1')->group(function (): void {
    Route::prefix('public/layout')->group(function (): void {
        Route::get('menus/location/{location}', [MenuController::class, 'getByLocation']);
        Route::get('widgets/location/{location}', [WidgetController::class, 'getByLocation']);
    });

    Route::prefix('manage/layout')->middleware(['auth:sanctum', 'extension.active:layout'])->group(function (): void {
        // Menus
        Route::get('menus/locations', [MenuController::class, 'locations']);
        Route::get('menus/location/{location}', [MenuController::class, 'getByLocation']);
        Route::get('menus/{menu}/usage', [MenuController::class, 'usage']);
        Route::post('menus/{menu}/restore', [MenuController::class, 'restore']);
        Route::delete('menus/{menu}/force-delete', [MenuController::class, 'forceDestroy']);
        Route::get('menus/{menu}/items', [MenuController::class, 'listItems']);
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

        // P3-3a: FE MenuModal still calls themes/active/locations — map to registry defaults.
        Route::get('themes/active/locations', [MenuController::class, 'locations']);
    });
});
