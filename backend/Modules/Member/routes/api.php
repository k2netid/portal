<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Member\Http\Controllers\Api\AuthController;
use Modules\Member\Http\Controllers\Api\BookmarkController;
use Modules\Member\Http\Controllers\Api\MemberDirectoryController;
use Modules\Member\Http\Controllers\Api\PasswordResetController;
use Modules\Member\Http\Controllers\Api\PortalController;
use Modules\Member\Http\Controllers\Api\ProfileController;
use Modules\Member\Http\Controllers\Api\ReaderCommentController;
use Modules\Member\Http\Controllers\Api\ReaderFormSubmissionController;
use Modules\Member\Http\Controllers\Api\ReaderNewsletterController;

Route::prefix('v1')->group(function (): void {
    Route::prefix('public/member')->middleware(['throttle:30,1', 'extension.active:member'])->group(function (): void {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('forgot-password', [PasswordResetController::class, 'forgot']);
        Route::post('reset-password', [PasswordResetController::class, 'reset']);
        Route::get('verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])
            ->middleware('signed')
            ->name('member.verify-email');
        Route::get('confirm-email-change/{id}/{hash}', [ProfileController::class, 'confirmEmailChange'])
            ->middleware('signed')
            ->name('member.confirm-email-change');
    });

    Route::prefix('member')->middleware(['auth:member', 'throttle:120,1', 'extension.active:member'])->group(function (): void {
        Route::get('portal', [PortalController::class, 'show']);
        Route::get('me', [AuthController::class, 'me']);
        Route::patch('profile', [ProfileController::class, 'update']);
        Route::put('password', [ProfileController::class, 'updatePassword']);
        Route::put('email', [ProfileController::class, 'requestEmailChange']);
        Route::delete('account', [ProfileController::class, 'destroy']);
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
        Route::get('stats', [MemberDirectoryController::class, 'stats'])
            ->middleware('permission:view members');
        Route::get('export', [MemberDirectoryController::class, 'export'])
            ->middleware('permission:view members');
        Route::post('bulk-action', [MemberDirectoryController::class, 'bulkAction'])
            ->middleware('permission:manage members');
        Route::post('/', [MemberDirectoryController::class, 'store'])
            ->middleware('permission:manage members');
        Route::get('/', [MemberDirectoryController::class, 'index'])
            ->middleware('permission:view members');
        Route::get('{member}', [MemberDirectoryController::class, 'show'])
            ->middleware('permission:view members');
        Route::patch('{member}', [MemberDirectoryController::class, 'update'])
            ->middleware('permission:manage members');
        Route::delete('{member}', [MemberDirectoryController::class, 'destroy'])
            ->middleware('permission:manage members');
        Route::post('{member}/restore', [MemberDirectoryController::class, 'restore'])
            ->middleware('permission:manage members');
        Route::delete('{member}/force', [MemberDirectoryController::class, 'forceDelete'])
            ->middleware('permission:manage members');
    });
});
