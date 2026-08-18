<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Intelligence\Analytics\Http\Controllers\AnalyticsController;

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::resource('analytics', AnalyticsController::class)->names('analytics');
});
