<?php

namespace Modules\Core\System\Http\Controllers\Console;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Models\Language;
use Modules\Core\System\Services\LanguagePackService;
use Symfony\Component\HttpFoundation\Response;

class LanguageController extends BaseApiController
{
    public function __construct(protected LanguagePackService $languagePackService) {}

    /**
     * Get all active languages
     */
    public function index(): JsonResponse
    {
        $languages = Language::getActive();

        // Add UI translation stats to each language
        $languages = $languages->map(function ($lang): Language {
            $stats = $this->languagePackService->getLocaleStats($lang->code);
            $lang->has_ui_translations = $stats['exists'];
            $lang->translation_keys = $stats['total_keys'] ?? 0;

            return $lang;
        });

        return $this->success($languages, 'Languages retrieved successfully');
    }

    /**
     * Get a single language
     */
    public function show(Language $language): JsonResponse
    {
        $stats = $this->languagePackService->getLocaleStats($language->code);
        $language->has_ui_translations = $stats['exists'];
        $language->translation_keys = $stats['total_keys'] ?? 0;

        return $this->success($language, 'Language retrieved successfully');
    }

    /**
     * Create a new language
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'code' => 'required|string|max:10|unique:languages,code',
                'name' => 'required|string|max:255',
                'native_name' => 'nullable|string|max:255',
                'flag' => 'nullable|string',
                'is_default' => 'boolean',
                'is_active' => 'boolean',
                'sort_order' => 'integer',
                'create_from_template' => 'boolean',
                'template_locale' => 'string|max:10',
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        // If setting as default, unset other defaults
        if ($validated['is_default'] ?? false) {
            Language::where('is_default', true)->update(['is_default' => false]);
        }

        // Create language folder from template if requested
        if ($validated['create_from_template'] ?? false) {
            $templateLocaleRaw = $validated['template_locale'] ?? 'en';
            $templateLocale = is_string($templateLocaleRaw) ? $templateLocaleRaw : 'en';
            $result = $this->languagePackService->createFromTemplate((string) $validated['code'], $templateLocale);

            if (! $result['success']) {
                $message = $result['message'];

                return $this->error($message, 400);
            }
        }

        $language = Language::create([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'native_name' => $validated['native_name'] ?? null,
            'flag' => $validated['flag'] ?? null,
            'is_default' => $validated['is_default'] ?? false,
            'is_active' => $validated['is_active'] ?? true,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return $this->success($language, 'Language created successfully', 201);
    }

    /**
     * Update a language
     */
    public function update(Request $request, Language $language): JsonResponse
    {
        try {
            $validated = $request->validate([
                'code' => 'sometimes|required|string|max:10|unique:languages,code,'.$language->id,
                'name' => 'sometimes|required|string|max:255',
                'native_name' => 'nullable|string|max:255',
                'flag' => 'nullable|string',
                'is_default' => 'boolean',
                'is_active' => 'boolean',
                'sort_order' => 'integer',
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        // If setting as default, unset other defaults
        if (isset($validated['is_default']) && $validated['is_default']) {
            Language::where('id', '!=', $language->id)->where('is_default', true)->update(['is_default' => false]);
        }

        $language->update($validated);

        return $this->success($language, 'Language updated successfully');
    }

    /**
     * Delete a language
     */
    public function destroy(Language $language): JsonResponse
    {
        if ($language->is_default) {
            return $this->validationError(['language' => ['Cannot delete default language']], 'Cannot delete default language');
        }

        $language->delete();

        return $this->success(null, 'Language deleted successfully');
    }

    /**
     * Set a language as default
     */
    public function setDefault(Language $language): JsonResponse
    {
        Language::where('is_default', true)->update(['is_default' => false]);
        $language->update(['is_default' => true]);

        return $this->success($language, 'Default language set successfully');
    }

    /**
     * Export a language pack as ZIP
     */
    public function exportPack(Language $language): Response
    {
        $zipPathRaw = $this->languagePackService->exportLanguagePack($language->code);
        $zipPath = is_string($zipPathRaw) ? $zipPathRaw : null;

        if (! $zipPath || ! file_exists($zipPath)) {
            return $this->error('Failed to create language pack export', 500);
        }

        $fileName = basename($zipPath);

        return response()->download($zipPath, $fileName)->deleteFileAfterSend(true);
    }

    /**
     * Import a language pack from ZIP
     */
    public function importPack(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:zip|max:10240', // Max 10MB
                'target_locale' => 'nullable|string|max:10',
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $file = $request->file('file');
        if (! ($file instanceof UploadedFile)) {
            return $this->error('Invalid file upload', 400);
        }

        $targetLocaleRaw = $request->input('target_locale');
        $targetLocale = is_string($targetLocaleRaw) ? $targetLocaleRaw : null;

        // Store uploaded file temporarily
        $tempPath = $file->storeAs('temp', 'import-'.now()->timestamp.'.zip');
        if (! is_string($tempPath)) {
            return $this->error('Failed to store temporary file', 500);
        }
        $fullPath = storage_path('app/'.$tempPath);

        $result = $this->languagePackService->importLanguagePack($fullPath, $targetLocale);

        // Clean up temp file
        @unlink($fullPath);

        if (! $result['success']) {
            $message = $result['message'];

            return $this->error($message, 400);
        }

        // Create or update language in DB if it doesn't exist
        $localeRaw = $result['locale'] ?? null;
        $locale = is_string($localeRaw) ? $localeRaw : 'en';
        $language = Language::firstOrCreate(
            ['code' => $locale],
            ['name' => ucfirst($locale), 'is_active' => true]
        );

        $message = $result['message'];

        return $this->success([
            'language' => $language,
            'files_imported' => $result['files'] ?? 0,
        ], $message);
    }

    /**
     * Get UI translation stats for all locales
     */
    public function uiStats(): JsonResponse
    {
        $locales = $this->languagePackService->getAvailableLocales();
        $stats = [];

        foreach ($locales as $locale) {
            $localeStats = $this->languagePackService->getLocaleStats($locale);
            $stats[$locale] = $localeStats;
        }

        return $this->success($stats, 'UI translation stats retrieved');
    }
}
