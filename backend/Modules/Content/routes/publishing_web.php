<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Content\Publishing\Http\Controllers\PublishingController;

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::resource('publishing', PublishingController::class)->names('publishing');
});
