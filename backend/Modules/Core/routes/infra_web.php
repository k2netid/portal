<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Core\Infra\Http\Controllers\InfraController;

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::resource('infras', InfraController::class)->names('infra');
});
