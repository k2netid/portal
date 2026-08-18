<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Content\Library\Http\Controllers\LibraryController;

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::resource('libraries', LibraryController::class)->names('library');
});
