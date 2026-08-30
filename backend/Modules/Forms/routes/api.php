<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Forms\Http\Controllers\Api\FormController;
use Modules\Forms\Http\Controllers\Api\FormSubmissionController;

Route::prefix('v1')->group(function (): void {
    Route::prefix('public/forms')->middleware(['extension.active:forms'])->group(function (): void {
        Route::get('{form:slug}', [FormController::class, 'publicShow']);
        Route::post('{form:slug}/submit', [FormController::class, 'submit']);
        Route::post('{form:slug}/track', [FormController::class, 'track']);
    });

    Route::prefix('manage/forms')->middleware(['auth:sanctum', 'extension.active:forms'])->group(function (): void {
        Route::get('', [FormController::class, 'index']);
        Route::post('', [FormController::class, 'store']);
        Route::post('bulk-action', [FormController::class, 'bulkAction']);
        Route::get('{form}', [FormController::class, 'show']);
        Route::put('{form}', [FormController::class, 'update']);
        Route::patch('{form}', [FormController::class, 'update']);
        Route::delete('{form}', [FormController::class, 'destroy']);
        Route::post('{form}/duplicate', [FormController::class, 'duplicate']);
        Route::post('{form}/restore', [FormController::class, 'restore']);
        Route::delete('{form}/force-delete', [FormController::class, 'forceDelete']);
        Route::post('{form}/fields', [FormController::class, 'addField']);
        Route::put('{form}/fields/{formField}', [FormController::class, 'updateField']);
        Route::delete('{form}/fields/{formField}', [FormController::class, 'deleteField']);
        Route::post('{form}/reorder-fields', [FormController::class, 'reorderFields']);
        Route::get('{form}/submissions', [FormSubmissionController::class, 'index']);
        Route::get('{form}/submissions/export', [FormSubmissionController::class, 'export']);
        Route::get('{form}/submissions/statistics', [FormSubmissionController::class, 'statistics']);
    });

    Route::prefix('manage/form-submissions')->middleware(['auth:sanctum', 'extension.active:forms'])->group(function (): void {
        Route::get('', [FormSubmissionController::class, 'index']);
        Route::get('{formSubmission}', [FormSubmissionController::class, 'show']);
        Route::get('{formSubmission}/export-pdf', [FormSubmissionController::class, 'exportPdf']);
        Route::put('{formSubmission}/read', [FormSubmissionController::class, 'markAsRead']);
        Route::put('{formSubmission}/archive', [FormSubmissionController::class, 'archive']);
        Route::delete('{formSubmission}', [FormSubmissionController::class, 'destroy']);
        Route::post('{formSubmission}/restore', [FormSubmissionController::class, 'restore']);
        Route::delete('{formSubmission}/force-delete', [FormSubmissionController::class, 'forceDelete']);
    });
});
