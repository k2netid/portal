<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Intelligence\Search\Http\Controllers\SearchController;

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::resource('searches', SearchController::class)->names('search');
});
