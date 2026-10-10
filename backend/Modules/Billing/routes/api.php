<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Billing\Http\Controllers\Api\MemberBillingController;

Route::prefix('v1')->group(function (): void {
    Route::prefix('member/billing')->middleware(['auth:member', 'throttle:60,1'])->group(function (): void {
        Route::get('/', [MemberBillingController::class, 'show']);
        Route::get('packages', [MemberBillingController::class, 'packages']);
        Route::post('order', [MemberBillingController::class, 'order']);
        Route::post('transactions/{id}/checkout', [MemberBillingController::class, 'checkoutTransaction']);
    });

    Route::post('public/billing/webhook', [MemberBillingController::class, 'webhook'])
        ->middleware('throttle:60,1');
});
