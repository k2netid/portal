<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Member\Http\Controllers\Api\AuthController;
use Modules\Member\Http\Controllers\Api\BookmarkController;
use Modules\Member\Http\Controllers\Api\MemberDirectoryController;
use Modules\Member\Http\Controllers\Api\PortalController;
use Modules\Member\Http\Controllers\Api\ProfileController;
use Modules\Member\Http\Controllers\Api\ReaderCommentController;
use Modules\Member\Http\Controllers\Api\ReaderFormSubmissionController;
use Modules\Member\Http\Controllers\Api\ReaderNewsletterController;

Route::prefix('v1')->group(function (): void {
    Route::prefix('public/member')->middleware(['throttle:30,1', 'extension.active:member'])->group(function (): void {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::get('verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])
            ->middleware('signed')
            ->name('member.verify-email');
    });

    Route::prefix('member')->middleware(['auth:member', 'throttle:120,1', 'extension.active:member'])->group(function (): void {
        Route::get('portal', [PortalController::class, 'show']);
        Route::get('me', [AuthController::class, 'me']);
        Route::patch('profile', [ProfileController::class, 'update']);
        Route::put('password', [ProfileController::class, 'updatePassword']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('email/verification-notification', [AuthController::class, 'resendVerification']);
        Route::middleware(['member.verified', 'extension.active:publishing'])->group(function (): void {
            Route::get('bookmarks', [BookmarkController::class, 'index']);
            Route::post('bookmarks', [BookmarkController::class, 'store']);
            Route::delete('bookmarks/{bookmark}', [BookmarkController::class, 'destroy']);
            Route::get('comments', [ReaderCommentController::class, 'index']);
        });

        Route::middleware('extension.active:newsletter')->group(function (): void {
            Route::get('newsletter', [ReaderNewsletterController::class, 'show']);
            Route::put('newsletter', [ReaderNewsletterController::class, 'update']);
        });

        Route::middleware('extension.active:forms')->group(function (): void {
            Route::get('submissions', [ReaderFormSubmissionController::class, 'index']);
        });
    });

    Route::prefix('manage/members')->middleware(['auth:sanctum', 'extension.active:member'])->group(function (): void {
        Route::get('/', [MemberDirectoryController::class, 'index'])
            ->middleware('permission:view members');
        Route::patch('{member}', [MemberDirectoryController::class, 'update'])
            ->middleware('permission:manage members');
    });
});
