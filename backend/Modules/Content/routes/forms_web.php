<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Content\Forms\Http\Controllers\FormsController;

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::resource('forms', FormsController::class)->names('forms');
});
