<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Core\Security\Http\Controllers\SecurityController;

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::resource('securities', SecurityController::class)->names('security');
});
