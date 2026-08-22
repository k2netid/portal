<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Mail\Http\Controllers\MailAccountController;
use Modules\Mail\Http\Controllers\MailController;

Route::prefix('v1/manage/mail')
    ->middleware(['auth:sanctum', 'mail.extension', 'permission:manage system'])
    ->group(function (): void {
        Route::get('messages', [MailController::class, 'index']);
        Route::get('messages/{id}', [MailController::class, 'show']);
        Route::get('messages/{id}/attachments/{index}', [MailController::class, 'downloadAttachment'])
            ->whereNumber('index');
        Route::post('messages/draft', [MailController::class, 'saveDraft']);
        Route::post('messages/schedule', [MailController::class, 'schedule'])->middleware('throttle:20,1');
        Route::post('messages/{id}/snooze', [MailController::class, 'snooze']);
        Route::post('messages/{id}/move', [MailController::class, 'move']);
        Route::post('messages/{id}/label', [MailController::class, 'toggleMessageLabel']);
        Route::post('send', [MailController::class, 'send'])->middleware('throttle:30,1');
        Route::post('sync', [MailController::class, 'sync']);
        Route::patch('messages/{id}/star', [MailController::class, 'toggleStar']);
        Route::patch('messages/{id}/read', [MailController::class, 'markRead']);
        Route::delete('messages/{id}/trash', [MailController::class, 'moveToTrash']);
        Route::post('messages/{id}/restore', [MailController::class, 'restore']);
        Route::delete('messages/{id}', [MailController::class, 'destroy']);
        Route::delete('trash/empty', [MailController::class, 'emptyTrash']);
        Route::get('labels', [MailController::class, 'getLabels']);
        Route::post('labels', [MailController::class, 'saveLabels']);
        Route::get('templates', [MailController::class, 'getTemplates']);
        Route::post('templates', [MailController::class, 'saveTemplates']);
        Route::get('settings', [MailController::class, 'getSettings']);
        Route::post('settings', [MailController::class, 'saveSettings']);
        Route::get('accounts', [MailAccountController::class, 'index']);
        Route::post('accounts', [MailAccountController::class, 'store']);
        Route::post('accounts/test', [MailAccountController::class, 'testConnection'])->middleware('throttle:10,1');
        Route::get('accounts/{id}', [MailAccountController::class, 'show']);
        Route::put('accounts/{id}', [MailAccountController::class, 'update']);
        Route::delete('accounts/{id}', [MailAccountController::class, 'destroy']);
        Route::post('accounts/{id}/default', [MailAccountController::class, 'setDefault']);
    });
