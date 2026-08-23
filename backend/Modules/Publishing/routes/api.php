<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Publishing\Http\Controllers\Api\CommentController;
use Modules\Publishing\Http\Controllers\Api\ContentController;
use Modules\Publishing\Http\Controllers\Api\ContentRevisionController;
use Modules\Publishing\Http\Controllers\Api\ContentTemplateController;
use Modules\Publishing\Http\Controllers\Api\MemberController;
use Modules\Publishing\Http\Controllers\Api\SeoController;
use Modules\Publishing\Http\Controllers\Api\SettingController;

Route::prefix('v1')->group(function (): void {
    // Public publishing API (canonical; former /public/Jejakawan removed)
    Route::prefix('public/publishing')->middleware('throttle:300,1')->group(function (): void {
        Route::get('/contents', [ContentController::class, 'index']);
        Route::get('/contents/{slug}', [ContentController::class, 'show']);
        Route::get('/contents/{slug}/related', [ContentController::class, 'related']);
        Route::get('/contents/{content}/comments', [CommentController::class, 'index']);
        Route::post('/contents/{content}/comments', [CommentController::class, 'store'])->middleware('throttle:10,1');
    });

    // Member Portal API (authenticated readers/members)
    Route::prefix('member')->middleware(['auth:sanctum', 'throttle:120,1'])->group(function (): void {
        // My Comments
        Route::get('comments', [MemberController::class, 'myComments']);

        // Bookmarks
        Route::get('bookmarks', [MemberController::class, 'myBookmarks']);
        Route::post('bookmarks', [MemberController::class, 'addBookmark']);
        Route::post('bookmarks/check', [MemberController::class, 'isBookmarked']);
        Route::delete('bookmarks/{bookmark}', [MemberController::class, 'removeBookmark']);

        // Newsletter Preferences
        Route::get('newsletter', [MemberController::class, 'newsletterStatus']);
        Route::put('newsletter', [MemberController::class, 'updateNewsletter']);
    });

    // Console Management (Jejakawan)
    Route::prefix('manage/publishing')->middleware(['auth:sanctum', 'extension.active:publishing'])->group(function (): void {
        // Contents
        Route::get('contents/stats', [ContentController::class, 'stats'])->middleware('permission:view content');
        Route::get('contents', [ContentController::class, 'adminIndex'])->middleware('permission:view content');
        Route::get('contents/{content}', [ContentController::class, 'adminShow'])->middleware('permission:view content');
        Route::post('contents', [ContentController::class, 'store'])->middleware('permission:create content');
        Route::post('contents/autosave', [ContentController::class, 'autosave'])->middleware('permission:create content');
        Route::put('contents/{content}', [ContentController::class, 'update'])->middleware('permission:edit content');
        Route::patch('contents/{content}/autosave', [ContentController::class, 'autosave'])->middleware('permission:edit content');
        Route::delete('contents/{content}', [ContentController::class, 'destroy'])->middleware('permission:delete content');
        Route::delete('contents/trash/empty', [ContentController::class, 'emptyTrash'])->middleware('permission:delete content');
        Route::post('contents/{content}/duplicate', [ContentController::class, 'duplicate'])->middleware('permission:create content');
        Route::post('contents/bulk-action', [ContentController::class, 'bulkAction'])->middleware('permission:edit content');
        Route::put('contents/{content}/approve', [ContentController::class, 'approve'])->middleware('permission:approve content');
        Route::put('contents/{content}/reject', [ContentController::class, 'reject'])->middleware('permission:approve content');
        Route::put('contents/{id}/restore', [ContentController::class, 'restore'])->middleware('permission:delete content');
        Route::patch('contents/{content}/toggle-featured', [ContentController::class, 'toggleFeatured'])->middleware('permission:edit content');
        Route::delete('contents/{id}/force-delete', [ContentController::class, 'forceDelete'])->middleware('permission:delete content');
        Route::post('contents/{content}/lock', [ContentController::class, 'lock'])->middleware('permission:edit content');
        Route::get('contents/{content}/lock-status', [ContentController::class, 'lockStatus'])->middleware('permission:edit content');
        Route::post('contents/{content}/unlock', [ContentController::class, 'unlock'])->middleware('permission:edit content');
        Route::get('contents/{content}/preview', [ContentController::class, 'preview'])->middleware('permission:view content');
        Route::get('contents/{content}/seo-analysis', [SeoController::class, 'analyzeContent'])->middleware('permission:view seo');
        Route::get('contents/{content}/schema', [SeoController::class, 'generateSchema'])->middleware('permission:view seo');

        // Comments
        Route::get('comments', [CommentController::class, 'adminIndex'])->middleware('permission:manage comments');
        Route::get('comments/statistics', [CommentController::class, 'statistics'])->middleware('permission:manage comments');
        Route::post('comments/bulk', [CommentController::class, 'bulkAction'])->middleware('permission:manage comments');
        Route::put('comments/{comment}/approve', [CommentController::class, 'approve'])->middleware('permission:manage comments');
        Route::put('comments/{comment}/reject', [CommentController::class, 'reject'])->middleware('permission:manage comments');
        Route::put('comments/{comment}/spam', [CommentController::class, 'markAsSpam'])->middleware('permission:manage comments');
        Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->middleware('permission:manage comments');

        // Content Revisions
        Route::get('contents/{content}/revisions', [ContentRevisionController::class, 'index'])->middleware('permission:edit content');
        Route::get('contents/{content}/revisions/{revision}', [ContentRevisionController::class, 'show'])->middleware('permission:edit content');
        Route::post('contents/{content}/revisions', [ContentRevisionController::class, 'store'])->middleware('permission:edit content');
        Route::post('contents/{content}/revisions/{revision}/restore', [ContentRevisionController::class, 'restore'])->middleware('permission:edit content');
        Route::delete('contents/{content}/revisions/{revision}', [ContentRevisionController::class, 'destroy'])->middleware('permission:edit content');

        // Content Templates
        Route::post('content-templates/bulk-action', [ContentTemplateController::class, 'bulkAction'])->middleware('permission:edit content templates');
        Route::put('content-templates/{id}/restore', [ContentTemplateController::class, 'restore'])->middleware('permission:edit content templates');
        Route::delete('content-templates/{id}/force-delete', [ContentTemplateController::class, 'forceDelete'])->middleware('permission:delete content templates');
        Route::post('content-templates/{content_template}/create-content', [ContentTemplateController::class, 'createContent'])->middleware('permission:create content');
        Route::apiResource('content-templates', ContentTemplateController::class)->middleware('permission:view content templates');

        // SEO
        Route::get('seo/stats', [SeoController::class, 'stats'])->middleware('permission:view seo');
        Route::get('seo/check-url', [SeoController::class, 'checkUrl'])->middleware('permission:view seo');
        Route::get('seo/sitemap', [SeoController::class, 'generateSitemap'])->middleware('permission:view seo');
        Route::get('seo/robots-txt', [SeoController::class, 'getRobotsTxt'])->middleware('permission:view seo');
        Route::put('seo/robots-txt', [SeoController::class, 'updateRobotsTxt'])->middleware('permission:edit seo');

        // Settings
        Route::get('settings', [SettingController::class, 'index'])->middleware('permission:view settings');
        Route::put('settings', [SettingController::class, 'update'])->middleware('permission:edit settings');
        Route::post('settings/bulk-update', [SettingController::class, 'bulkUpdate'])->middleware('permission:manage settings');
    });
});
