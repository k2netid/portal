<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Intelligence\Newsletter\Http\Controllers\NewsletterController;

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::resource('newsletters', NewsletterController::class)->names('newsletter');
});
