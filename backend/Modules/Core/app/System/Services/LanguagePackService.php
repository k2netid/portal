<?php

namespace Modules\Core\System\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use ZipArchive;

/**
 * Secure Language Pack Service
 * Handles import/export of language packs with comprehensive security validation:
 * - File type validation (only ZIP allowed)
 * - Content validation (only JSON and JS files)
 * - Malicious content detection (PHP, shell scripts, executable content)
 * - SQL injection and XSS pattern detection
 * - Path traversal prevention
 * - Secure temporary storage with cleanup
 * - Proper file permissions
 */
class LanguagePackService
{
    protected string $langPath;

    /** @var array<int, string> */
    protected array $protectedLocales = ['en', 'id']; // Cannot be deleted

    /** @var array<int, string> */
    // Allowed file extensions in language packs
    protected array $allowedExtensions = ['json', 'js'];

    // Maximum file size per file in bytes (100KB)
    protected int $maxFileSize = 102400;

    // Maximum total extracted size (5MB)
    protected int $maxTotalSize = 5242880;

    /** @var array<int, string> */
    // Dangerous patterns that indicate malicious content
    protected array $dangerousPatterns = [
        // PHP code
        '/<\?php/i',
        '/<\?=/i',
        '/<\?[\s\r\n]/i',
        '/\beval\s*\(/i',
        '/\bexec\s*\(/i',
        '/\bsystem\s*\(/i',
        '/\bpassthru\s*\(/i',
        '/\bshell_exec\s*\(/i',
        '/\bproc_open\s*\(/i',
        '/\bpopen\s*\(/i',
        '/\bbase64_decode\s*\(/i',
        '/\bgzinflate\s*\(/i',
        '/\bunserialize\s*\(/i',

        // Shell/script execution
        '/\b(bash|sh|zsh|csh|ksh)\s*-c/i',
        '/\bcurl\s+.*\|.*sh/i',
        '/\bwget\s+.*\|.*sh/i',
        '/\b(nc|netcat)\s+/i',
        '/\brm\s+(-rf?|--recursive)/i',

        // SQL injection patterns (shouldn't be in translations)
        '/\bDROP\s+TABLE\b/i',
        '/\bDELETE\s+FROM\b/i',
        '/\bINSERT\s+INTO\b/i',
        '/\bUPDATE\s+.*SET\b/i',
        '/\bUNION\s+SELECT\b/i',
        '/\b(\'|")\s*OR\s+\d+\s*=\s*\d+/i',
        '/\bEXEC(\s+|\s*\()xp_/i',

        // XSS patterns
        '/\b(javascript|vbscript):/i',
        '/\bonload\s*=/i',
        '/\bonerror\s*=/i',
        '/\bonclick\s*=/i',
        '/\bonmouseover\s*=/i',
        '/<script[\s>]/i',
        '/<\/script>/i',
        '/<iframe[\s>]/i',
        '/<object[\s>]/i',
        '/<embed[\s>]/i',
        '/<applet[\s>]/i',

        // Require/import malicious modules
        '/require\s*\(\s*[\'"]child_process[\'"]\s*\)/i',
        '/require\s*\(\s*[\'"]fs[\'"]\s*\)/i',
        '/import\s+.*from\s+[\'"]child_process[\'"]/i',
    ];

    protected ?string $frontendSrc = null;

    public function __construct()
    {
        $this->langPath = resource_path('lang');
        $candidate = realpath(base_path('../frontend/src'));
        $this->frontendSrc = ($candidate && is_dir($candidate.'/locales')) ? $candidate : null;
    }

    protected function frontendLocalesPath(string $locale): ?string
    {
        if (! $this->frontendSrc) {
            return null;
        }
        $path = $this->frontendSrc.'/locales/'.$locale;

        return is_dir($path) ? $path : null;
    }

    /**
     * Get list of available language folders
     *
     * @return array<int, string>
     */
    public function getAvailableLocales(): array
    {
        $locales = [];
        /** @var array<int, string> $directories */
        $directories = File::directories($this->langPath);

        foreach ($directories as $dir) {
            $code = basename($dir);
            // Skip if it's not a valid locale folder
            if ($this->isValidLocaleFolder($dir)) {
                $locales[] = $code;
            }
        }

        return $locales;
    }

    /**
     * Check if a folder is a valid locale folder
     */
    protected function isValidLocaleFolder(string $path): bool
    {
        if (! File::isDirectory($path)) {
            return false;
        }

        $hasIndex = File::exists($path.'/index.js');
        /** @var list<string>|false $jsonFiles */
        $jsonFiles = glob($path.'/*.json');
        /** @var list<string>|false $nestedJsonFiles */
        $nestedJsonFiles = glob($path.'/*/*.json');

        $hasJson = ($jsonFiles !== false && count($jsonFiles) > 0) || ($nestedJsonFiles !== false && count($nestedJsonFiles) > 0);

        return $hasIndex || $hasJson;
    }

    /**
     * Validate locale code format (2-5 lowercase letters)
     */
    protected function isValidLocaleCode(string $locale): bool
    {
        return preg_match('/^[a-z]{2,5}$/', $locale) === 1;
    }

    /**
     * Export a language pack as ZIP
     */
    public function exportLanguagePack(string $locale): ?string
    {
        // Validate locale code
        if (! $this->isValidLocaleCode($locale)) {
            Log::warning("Invalid locale code for export: {$locale}");

            return null;
        }

        $frontendPath = $this->frontendLocalesPath($locale);
        $legacyPath = $this->langPath.'/'.$locale;

        if (! $frontendPath && ! File::isDirectory($legacyPath)) {
            return null;
        }

        $zipFileName = "language-pack-{$locale}-".now()->format('Y-m-d-His').'.zip';
        $zipPath = storage_path('app/temp/'.$zipFileName);

        // Ensure temp directory exists with secure permissions
        $tempDir = storage_path('app/temp');
        if (! File::isDirectory($tempDir)) {
            File::makeDirectory($tempDir, 0750, true);
        }

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            Log::error("Could not create ZIP file: {$zipPath}");

            return null;
        }

        if ($frontendPath) {
            foreach (File::files($frontendPath) as $file) {
                if (in_array(strtolower($file->getExtension()), $this->allowedExtensions, true)) {
                    $zip->addFile($file->getRealPath(), $locale.'/'.$file->getFilename());
                }
            }
            $modulesRoot = $this->frontendSrc.'/modules';
            if (is_dir($modulesRoot)) {
                foreach (File::allFiles($modulesRoot) as $file) {
                    if ($file->getFilename() !== $locale.'.json' || ! str_contains($file->getPath(), '/locales')) {
                        continue;
                    }
                    $relative = 'modules/'.ltrim(str_replace($this->frontendSrc.'/', '', $file->getPath()), '/');
                    $zip->addFile($file->getRealPath(), $relative);
                }
            }
        }

        if (File::isDirectory($legacyPath)) {
            $this->addDirectoryToZip($zip, $legacyPath, $locale.'/legacy');
        }

        $zip->close();

        // Set secure file permissions
        chmod($zipPath, 0640);

        return $zipPath;
    }

    /**
     * Recursively add directory contents to ZIP (export only safe files)
     */
    protected function addDirectoryToZip(ZipArchive $zip, string $directory, string $basePath): void
    {
        $files = File::allFiles($directory);

        foreach ($files as $file) {
            $extension = strtolower($file->getExtension());

            // Only include allowed file types
            if (! in_array($extension, $this->allowedExtensions)) {
                continue;
            }

            $relativePath = $basePath.'/'.$file->getRelativePathname();
            $zip->addFile($file->getRealPath(), $relativePath);
        }
    }

    /**
     * Import a language pack from uploaded ZIP with security validation
     *
     * @return array{success: bool, message: string, locale?: string, files?: int}
     */
    public function importLanguagePack(string $zipPath, ?string $targetLocale = null): array
    {
        // Validate ZIP file exists and is readable
        if (! file_exists($zipPath) || ! is_readable($zipPath)) {
            return ['success' => false, 'message' => 'ZIP file not found or not readable'];
        }

        // Validate ZIP file size
        $zipSize = filesize($zipPath);
        if ($zipSize > $this->maxTotalSize) {
            return ['success' => false, 'message' => 'ZIP file exceeds maximum allowed size (5MB)'];
        }

        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== true) {
            return ['success' => false, 'message' => 'Could not open ZIP file - file may be corrupted'];
        }

        // Security validation before extraction
        $validation = $this->validateZipContents($zip);
        if (! $validation['valid']) {
            $zip->close();
            Log::warning('Language pack import blocked', [
                'reason' => $validation['message'] ?? 'Unknown security violation',
                'file' => $zipPath,
            ]);

            return ['success' => false, 'message' => $validation['message'] ?? 'Import blocked for security reasons'];
        }

        $locale = $targetLocale ?? $validation['locale'] ?? 'unknown';

        // Validate locale code
        if (! $this->isValidLocaleCode($locale)) {
            $zip->close();

            return ['success' => false, 'message' => 'Invalid locale code format. Must be 2-5 lowercase letters.'];
        }

        // Create temporary extraction directory
        $tempExtractPath = storage_path('app/temp/lang_import_'.uniqid());
        File::makeDirectory($tempExtractPath, 0750, true);

        try {
            // Extract to temp directory first
            $zip->extractTo($tempExtractPath);
            $zip->close();

            // Validate extracted content
            $contentValidation = $this->validateExtractedContent($tempExtractPath, $locale);
            if (! $contentValidation['valid']) {
                $this->cleanupTempDirectory($tempExtractPath);

                return ['success' => false, 'message' => $contentValidation['message'] ?? 'Invalid extracted content'];
            }

            $filesImported = 0;

            if ($this->frontendSrc) {
                $sourceLocalePath = $tempExtractPath.'/'.$locale;
                $targetLocalePath = $this->frontendSrc.'/locales/'.$locale;
                if (File::isDirectory($sourceLocalePath)) {
                    if (File::isDirectory($targetLocalePath)) {
                        File::deleteDirectory($targetLocalePath);
                    }
                    File::copyDirectory($sourceLocalePath, $targetLocalePath);
                    $filesImported += count(File::allFiles($targetLocalePath));
                    $this->setSecurePermissions($targetLocalePath);
                }

                $modulesSource = $tempExtractPath.'/modules';
                if (File::isDirectory($modulesSource)) {
                    $filesImported += $this->importFrontendModuleLocales($modulesSource, $locale);
                }
            }

            $sourcePath = $tempExtractPath.'/'.$locale;
            $targetPath = $this->langPath.'/'.$locale;
            if (File::isDirectory($sourcePath) && ! $this->frontendSrc) {
                if (File::isDirectory($targetPath)) {
                    $backupPath = $targetPath.'_backup_'.now()->format('YmdHis');
                    File::moveDirectory($targetPath, $backupPath);
                }
                File::moveDirectory($sourcePath, $targetPath);
                $filesImported += count(File::allFiles($targetPath));
                $this->setSecurePermissions($targetPath);
            }

            // Cleanup temp directory
            $this->cleanupTempDirectory($tempExtractPath);

            Log::info('Language pack imported successfully', ['locale' => $locale]);

            return [
                'success' => true,
                'message' => "Language pack '{$locale}' imported successfully",
                'locale' => $locale,
                'files' => $filesImported,
            ];

        } catch (\Exception $e) {
            $this->cleanupTempDirectory($tempExtractPath);
            Log::error('Language pack import failed', [
                'error' => $e->getMessage(),
                'file' => $zipPath,
            ]);

            return ['success' => false, 'message' => 'Import failed: '.$e->getMessage()];
        }
    }

    /**
     * Validate ZIP contents before extraction
     *
     * @return array{valid: bool, message?: string, locale?: string}
     */
    protected function validateZipContents(ZipArchive $zip): array
    {
        $detectedLocale = null;
        $totalSize = 0;
        $fileCount = 0;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            if ($stat === false) {
                continue;
            }

            $name = $stat['name'];

            // Detect locale from first folder
            if ($detectedLocale === null) {
                $parts = explode('/', $name);
                if ($this->isValidLocaleCode($parts[0])) {
                    $detectedLocale = $parts[0];
                }
            }

            // Skip directories
            if (str_ends_with($name, '/')) {
                continue;
            }

            $fileCount++;

            // Check for path traversal attempts
            if (str_contains($name, '..') || str_contains($name, '\\')) {
                return ['valid' => false, 'message' => 'Security violation: Path traversal detected'];
            }

            // Validate file extension
            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (! in_array($extension, $this->allowedExtensions)) {
                return [
                    'valid' => false,
                    'message' => "Invalid file type: .{$extension}. Only .json and .js files are allowed.",
                ];
            }

            // Check file size
            if ($stat['size'] > $this->maxFileSize) {
                return [
                    'valid' => false,
                    'message' => "File too large: {$name} exceeds 100KB limit",
                ];
            }

            $totalSize += $stat['size'];

            // Check total size
            if ($totalSize > $this->maxTotalSize) {
                return ['valid' => false, 'message' => 'Total extracted size exceeds 5MB limit'];
            }

            // Validate file content for malicious patterns
            $content = $zip->getFromIndex($i);
            if ($content !== false) {
                $contentCheck = $this->validateFileContent($content, $name);
                if (! $contentCheck['valid']) {
                    return ['valid' => false, 'message' => $contentCheck['message'] ?? 'Unknown error'];
                }
            }
        }

        if ($fileCount === 0) {
            return ['valid' => false, 'message' => 'ZIP file is empty'];
        }

        if ($detectedLocale === null) {
            return ['valid' => false, 'message' => 'Could not detect locale from ZIP structure'];
        }

        return ['valid' => true, 'locale' => $detectedLocale];
    }

    /**
     * Validate file content for malicious patterns
     *
     * @return array{valid: bool, message?: string}
     */
    protected function validateFileContent(string $content, string $filename): array
    {
        // Check for dangerous patterns
        foreach ($this->dangerousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return [
                    'valid' => false,
                    'message' => "Security violation: Potentially malicious content detected in {$filename}",
                ];
            }
        }

        // If JSON file, validate JSON structure
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if ($extension === 'json') {
            /** @var mixed $json */
            $json = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'valid' => false,
                    'message' => "Invalid JSON in {$filename}: ".json_last_error_msg(),
                ];
            }

            // Validate JSON structure (should only contain strings)
            if (! is_array($json) || ! $this->validateTranslationStructure($json, $filename)) {
                return [
                    'valid' => false,
                    'message' => "Invalid translation structure in {$filename}: Values must be strings only",
                ];
            }
        }

        return ['valid' => true];
    }

    /**
     * Validate translation JSON structure (only strings allowed as leaf values)
     *
     * @param  array<string|int, mixed>  $data
     */
    protected function validateTranslationStructure(array $data, string $path = ''): bool
    {
        foreach ($data as $key => $value) {
            // Key should be alphanumeric with underscores
            if (! preg_match('/^\w+$/', (string) $key)) {
                Log::warning("Invalid translation key: {$path}.{$key}");

                return false;
            }

            if (is_array($value)) {
                if (! $this->validateTranslationStructure($value, "{$path}.{$key}")) {
                    return false;
                }
            } elseif (! is_string($value)) {
                Log::warning("Non-string value in translation: {$path}.{$key}");

                return false;
            }
        }

        return true;
    }

    /**
     * Validate extracted content in temp directory
     *
     * @return array{valid: bool, message?: string}
     */
    protected function validateExtractedContent(string $extractPath, string $locale): array
    {
        $localePath = $extractPath.'/'.$locale;
        $modulesPath = $extractPath.'/modules';

        if (! File::isDirectory($localePath) && ! File::isDirectory($modulesPath)) {
            return [
                'valid' => false,
                'message' => "Locale folder '{$locale}' or modules/ not found in extracted content",
            ];
        }

        $paths = array_filter([
            File::isDirectory($localePath) ? $localePath : null,
            File::isDirectory($modulesPath) ? $modulesPath : null,
        ]);

        foreach ($paths as $scanPath) {
            $files = File::allFiles($scanPath);
            foreach ($files as $file) {
                $extension = strtolower($file->getExtension());

                if (! in_array($extension, $this->allowedExtensions)) {
                    return [
                        'valid' => false,
                        'message' => 'Unexpected file type found: '.$file->getFilename(),
                    ];
                }

                /** @var string $content */
                $content = File::get($file->getRealPath());
                $check = $this->validateFileContent($content, $file->getFilename());
                if (! $check['valid']) {
                    return ['valid' => false, 'message' => $check['message'] ?? 'Unknown error'];
                }
            }
        }

        return ['valid' => true];
    }

    /**
     * Copy module locale JSON files from an extracted modules/ tree into the Vue frontend.
     */
    protected function importFrontendModuleLocales(string $modulesSource, string $locale): int
    {
        if (! $this->frontendSrc) {
            return 0;
        }

        $count = 0;
        foreach (File::allFiles($modulesSource) as $file) {
            if ($file->getFilename() !== $locale.'.json' || ! str_ends_with($file->getPath(), '/locales')) {
                continue;
            }
            $relative = ltrim(str_replace($modulesSource.'/', '', $file->getPath()), '/');
            $target = $this->frontendSrc.'/modules/'.$relative;
            File::ensureDirectoryExists(dirname($target));
            File::copy($file->getRealPath(), $target);
            $count++;
        }

        return $count;
    }

    /**
     * Set secure file/folder permissions
     */
    protected function setSecurePermissions(string $path): void
    {
        // Directory: 0755 (rwxr-xr-x)
        // Files: 0644 (rw-r--r--)

        chmod($path, 0755);

        $files = File::allFiles($path);
        foreach ($files as $file) {
            chmod($file->getRealPath(), 0644);
        }

        $directories = File::directories($path);
        foreach ($directories as $dir) {
            $this->setSecurePermissions($dir);
        }
    }

    /**
     * Cleanup temporary directory
     */
    protected function cleanupTempDirectory(string $path): void
    {
        if (File::isDirectory($path)) {
            File::deleteDirectory($path);
        }
    }

    /**
     * Create a new language from template (copy from default language)
     *
     * @return array{success: bool, message: string, locale?: string, files?: int}
     */
    public function createFromTemplate(string $newLocale, string $templateLocale = 'en'): array
    {
        // Validate locale codes
        if (! $this->isValidLocaleCode($newLocale)) {
            return ['success' => false, 'message' => 'Invalid locale code format'];
        }

        $filesCopied = 0;

        if ($this->frontendSrc) {
            $templateFe = $this->frontendLocalesPath($templateLocale);
            $newFe = $this->frontendSrc.'/locales/'.$newLocale;
            if (! $templateFe) {
                return ['success' => false, 'message' => "Template locale '{$templateLocale}' not found in frontend"];
            }
            if (File::isDirectory($newFe)) {
                return ['success' => false, 'message' => "Locale '{$newLocale}' already exists in frontend"];
            }
            File::copyDirectory($templateFe, $newFe);
            $filesCopied += count(File::allFiles($newFe));

            $modulesRoot = $this->frontendSrc.'/modules';
            foreach (File::allFiles($modulesRoot) as $file) {
                if ($file->getFilename() !== $templateLocale.'.json' || ! str_ends_with($file->getPath(), '/locales')) {
                    continue;
                }
                $target = str_replace($templateLocale.'.json', $newLocale.'.json', $file->getPath());
                if (File::exists($target)) {
                    continue;
                }
                File::copy($file->getRealPath(), $target);
                $filesCopied++;
            }
            $this->setSecurePermissions($newFe);

            return [
                'success' => true,
                'message' => "Language '{$newLocale}' created from template '{$templateLocale}'",
                'locale' => $newLocale,
                'files' => $filesCopied,
            ];
        }

        $templatePath = $this->langPath.'/'.$templateLocale;
        $newPath = $this->langPath.'/'.$newLocale;

        if (! File::isDirectory($templatePath)) {
            return ['success' => false, 'message' => "Template locale '{$templateLocale}' not found"];
        }

        if (File::isDirectory($newPath)) {
            return ['success' => false, 'message' => "Locale '{$newLocale}' already exists"];
        }

        File::copyDirectory($templatePath, $newPath);
        $this->setSecurePermissions($newPath);

        Log::info('Language created from template', [
            'new_locale' => $newLocale,
            'template' => $templateLocale,
        ]);

        return [
            'success' => true,
            'message' => "Language '{$newLocale}' created from template '{$templateLocale}'",
            'locale' => $newLocale,
            'files' => count(File::allFiles($newPath)),
        ];
    }

    /**
     * Delete a language folder
     *
     * @return array{success: bool, message: string}
     */
    public function deleteLanguagePack(string $locale): array
    {
        // Validate locale code
        if (! $this->isValidLocaleCode($locale)) {
            return ['success' => false, 'message' => 'Invalid locale code'];
        }

        // Prevent deleting protected locales
        if (in_array($locale, $this->protectedLocales)) {
            return ['success' => false, 'message' => "Cannot delete protected locale: {$locale}"];
        }

        $localePath = $this->langPath.'/'.$locale;

        if (! File::isDirectory($localePath)) {
            return ['success' => false, 'message' => "Locale '{$locale}' not found"];
        }

        File::deleteDirectory($localePath);

        Log::info('Language pack deleted', ['locale' => $locale]);

        return [
            'success' => true,
            'message' => "Language pack '{$locale}' deleted successfully",
        ];
    }

    /**
     * Get translation statistics for a locale
     *
     * @return array{exists: bool, files?: int, total_keys?: int}
     */
    public function getLocaleStats(string $locale): array
    {
        if (! $this->isValidLocaleCode($locale)) {
            return ['exists' => false];
        }

        $jsonFiles = [];
        $frontendPath = $this->frontendLocalesPath($locale);
        if ($frontendPath) {
            $allFiles = array_merge(File::files($frontendPath), File::allFiles($this->frontendSrc.'/modules'));
            foreach ($allFiles as $file) {
                if ($file->getExtension() === 'json' && ($file->getFilename() === $locale.'.json' || dirname($file->getPath()) === $frontendPath)) {
                    $jsonFiles[] = $file;
                }
            }
        }

        $legacyPath = $this->langPath.'/'.$locale;
        if (File::isDirectory($legacyPath)) {
            $allFiles = File::allFiles($legacyPath);
            foreach ($allFiles as $file) {
                if ($file->getExtension() === 'json') {
                    $jsonFiles[] = $file;
                }
            }
        }

        if ($jsonFiles === []) {
            return ['exists' => false];
        }

        $totalKeys = 0;

        foreach ($jsonFiles as $file) {
            /** @var mixed $content */
            $content = json_decode(File::get($file->getRealPath()), true);
            if (is_array($content)) {
                $totalKeys += $this->countKeys($content);
            }
        }

        return [
            'exists' => true,
            'files' => count($jsonFiles),
            'total_keys' => $totalKeys,
        ];
    }

    /**
     * Recursively count keys in array
     *
     * @param  array<string|int, mixed>  $array
     */
    protected function countKeys(array $array): int
    {
        $count = 0;
        foreach ($array as $value) {
            if (is_array($value)) {
                $count += $this->countKeys($value);
            } else {
                $count++;
            }
        }

        return $count;
    }
}
