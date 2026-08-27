<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Member\Http\Controllers\Api\AuthController;
use Modules\Member\Http\Controllers\Api\BookmarkController;

Route::prefix('v1')->group(function (): void {
    Route::prefix('public/member')->middleware('throttle:30,1')->group(function (): void {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::get('verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])
            ->middleware('signed')
            ->name('member.verify-email');
    });

    Route::prefix('member')->middleware(['auth:member', 'throttle:120,1'])->group(function (): void {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('email/verification-notification', [AuthController::class, 'resendVerification']);
        Route::get('bookmarks', [BookmarkController::class, 'index']);
        Route::post('bookmarks', [BookmarkController::class, 'store']);
        Route::delete('bookmarks/{bookmark}', [BookmarkController::class, 'destroy']);
    });
});
