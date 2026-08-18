<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Content\Layout\Http\Controllers\LayoutController;

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::resource('layouts', LayoutController::class)->names('layout');
});
