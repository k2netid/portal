<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Content\Forms\Http\Controllers\Api\FormController;
use Modules\Content\Forms\Http\Controllers\Api\FormSubmissionController;

Route::prefix('v1')->group(function (): void {
    // Public Forms API
    Route::prefix('public/forms')->group(function (): void {
        Route::get('{form:slug}', [FormController::class, 'publicShow']);
        Route::post('{form:slug}/submit', [FormController::class, 'submit']);
        Route::post('{form:slug}/track', [FormController::class, 'track']);
    });

    // Console Management
    Route::prefix('manage/forms')->middleware(['auth:sanctum'])->group(function (): void {
        Route::post('bulk-action', [FormController::class, 'bulkAction'])->middleware('permission:manage forms');
        Route::post('{form}/duplicate', [FormController::class, 'duplicate'])->middleware('permission:manage forms');
        Route::post('{form}/restore', [FormController::class, 'restore'])->middleware('permission:manage forms');
        Route::delete('{form}/force-delete', [FormController::class, 'forceDelete'])->middleware('permission:manage forms');
        Route::apiResource('', FormController::class)->middleware('permission:manage forms')->parameters(['' => 'form']);
        Route::post('{form}/fields', [FormController::class, 'addField'])->middleware('permission:manage forms');
        Route::put('{form}/fields/{formField}', [FormController::class, 'updateField'])->middleware('permission:manage forms');
        Route::delete('{form}/fields/{formField}', [FormController::class, 'deleteField'])->middleware('permission:manage forms');
        Route::post('{form}/reorder-fields', [FormController::class, 'reorderFields'])->middleware('permission:manage forms');

        // Submissions
        Route::get('{form}/submissions', [FormSubmissionController::class, 'index'])->middleware('permission:manage forms');
        Route::get('{form}/submissions/export', [FormSubmissionController::class, 'export'])->middleware('permission:manage forms');
        Route::get('{form}/submissions/statistics', [FormSubmissionController::class, 'statistics'])->middleware('permission:manage forms');
    });

    // Submissions (General)
    Route::prefix('manage/form-submissions')->middleware(['auth:sanctum'])->group(function (): void {
        Route::get('', [FormSubmissionController::class, 'index'])->middleware('permission:manage forms');
        Route::get('{formSubmission}', [FormSubmissionController::class, 'show'])->middleware('permission:manage forms');
        Route::get('{formSubmission}/export-pdf', [FormSubmissionController::class, 'exportPdf'])->middleware('permission:manage forms');
        Route::put('{formSubmission}/read', [FormSubmissionController::class, 'markAsRead'])->middleware('permission:manage forms');
        Route::put('{formSubmission}/archive', [FormSubmissionController::class, 'archive'])->middleware('permission:manage forms');
        Route::delete('{formSubmission}', [FormSubmissionController::class, 'destroy'])->middleware('permission:manage forms');
        Route::post('{formSubmission}/restore', [FormSubmissionController::class, 'restore'])->middleware('permission:manage forms');
        Route::delete('{formSubmission}/force-delete', [FormSubmissionController::class, 'forceDelete'])->middleware('permission:manage forms');
    });
});
