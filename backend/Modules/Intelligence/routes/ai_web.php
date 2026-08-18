<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Intelligence\Ai\Http\Controllers\AiController;

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::resource('ais', AiController::class)->names('ai');
});
