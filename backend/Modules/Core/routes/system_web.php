<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Core\System\Http\Controllers\SystemController;

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::resource('systems', SystemController::class)->names('system');
});
