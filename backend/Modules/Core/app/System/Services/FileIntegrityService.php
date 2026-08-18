<?php

declare(strict_types=1);

namespace Modules\Core\System\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * File Integrity Monitoring Service.
 * Generates and verifies SHA-256 hashes of critical application files
 * to detect unauthorized modifications.
 */
class FileIntegrityService
{
    /** @var string Backup storage directory */
    private const BACKUP_PATH = 'security/integrity_snapshots';

    /** @var array<string> Critical files to monitor */
    private const MONITORED_FILES = [
        '.env',
        'artisan',
        'composer.json',
        'composer.lock',
        'package.json',
        'package-lock.json',
        'bootstrap/app.php',
        'config/app.php',
        'config/auth.php',
        'config/database.php',
        'config/sanctum.php',
        'config/session.php',
        'public/index.php',
        'public/.htaccess',
    ];

    /** @var array<string> Critical directory patterns to monitor (e.g. Modules/MODULE/app/Services) */
    private const MONITORED_DIR_PATTERNS = [
        // Flat structures
        'Modules/*/app/Http/Middleware',
        'Modules/*/app/Http/Controllers',
        'Modules/*/app/Services',
        'Modules/*/app/Providers',
        // DDD (Sub-domain) structures
        'Modules/*/app/*/Http/Middleware',
        'Modules/*/app/*/Http/Controllers',
        'Modules/*/app/*/Services',
        'Modules/*/app/*/Providers',
    ];

    /** @var array<string> Critical route files to monitor */
    private const MONITORED_ROUTE_FILES = [
        'routes/api.php',
        'routes/web.php',
        'routes/console.php',
    ];

    /**
     * @return array{created: int, updated: int, errors: int}
     */
    public function generateBaseline(): array
    {
        $stats = ['created' => 0, 'updated' => 0, 'errors' => 0];
        $files = $this->getMonitoredFiles();

        foreach ($files as $relativePath) {
            $absolutePath = base_path($relativePath);
            if (! file_exists($absolutePath)) {
                continue;
            }

            try {
                $hash = hash_file('sha256', $absolutePath);
                if ($hash === false) {
                    $stats['errors']++;

                    continue;
                }

                $exists = DB::table('sec_file_integrity_baselines')->where('file_path', $relativePath)->exists();

                if ($exists) {
                    DB::table('sec_file_integrity_baselines')->where('file_path', $relativePath)->update([
                        'hash' => $hash,
                        'file_size' => filesize($absolutePath) ?: 0,
                        'status' => 'ok',
                        'checked_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $stats['updated']++;
                } else {
                    DB::table('sec_file_integrity_baselines')->insert([
                        'id' => Str::uuid()->toString(),
                        'file_path' => $relativePath,
                        'hash' => $hash,
                        'file_size' => filesize($absolutePath) ?: 0,
                        'status' => 'ok',
                        'checked_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $stats['created']++;
                }
            } catch (\Exception $e) {
                Log::error("File integrity baseline error for {$relativePath}", ['error' => $e->getMessage()]);
                $stats['errors']++;
            }
        }

        return $stats;
    }

    /**
     * @return array{backed_up: int, errors: int}
     */
    public function createBackup(): array
    {
        $stats = ['backed_up' => 0, 'errors' => 0];
        $files = $this->getMonitoredFiles();
        $backupDir = storage_path(self::BACKUP_PATH);
        if (! is_dir($backupDir)) {
            mkdir($backupDir, 0750, true);
        }

        foreach ($files as $relativePath) {
            $src = base_path($relativePath);
            if (! file_exists($src)) {
                continue;
            }
            $dest = $backupDir.'/'.md5((string) $relativePath).'.bak';
            try {
                if (copy($src, $dest)) {
                    $stats['backed_up']++;
                } else {
                    $stats['errors']++;
                }
            } catch (\Exception $e) {
                Log::error("Integrity backup failed for {$relativePath}", ['error' => $e->getMessage()]);
                $stats['errors']++;
            }
        }

        return $stats;
    }

    public function restoreFile(string $relativePath): bool
    {
        $src = storage_path(self::BACKUP_PATH.'/'.md5($relativePath).'.bak');
        $dest = base_path($relativePath);
        if (! file_exists($src)) {
            return false;
        }
        try {
            return copy($src, $dest);
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * Verify the integrity of monitored files.
     *
     * @param  bool  $force  Force a fresh check bypassing cache
     * @return array{ok: int, modified: array<int, array<string, string>>, missing: array<int, array<string, string>>, new: array<int, array<string, string>>, violations: array<int, array<string, string>>}
     */
    public function verify(bool $force = false): array
    {
        if ($force) {
            Cache::forget('file_integrity_verification_result');
        }

        /** @var array{ok: int, modified: array<int, array<string, string>>, missing: array<int, array<string, string>>, new: array<int, array<string, string>>, violations: array<int, array<string, string>>} $result */
        $result = Cache::remember('file_integrity_verification_result', 60, function (): array {
            $stats = ['ok' => 0, 'modified' => [], 'missing' => [], 'new' => [], 'violations' => []];
            $baselines = DB::table('sec_file_integrity_baselines')->get()->keyBy('file_path');
            $currentFiles = $this->getMonitoredFiles();

            foreach ($baselines as $path => $baseline) {
                $absolutePath = base_path((string) $path);
                if (! file_exists($absolutePath)) {
                    $stats['missing'][] = ['path' => (string) $path, 'detail' => 'File has been deleted'];
                    $stats['violations'][] = ['path' => (string) $path, 'status' => 'missing', 'detail' => 'File has been deleted'];
                    if ($baseline->status !== 'missing') {
                        DB::table('sec_file_integrity_baselines')->where('file_path', $path)->update(['status' => 'missing', 'checked_at' => now()]);
                    }

                    continue;
                }

                $currentHash = hash_file('sha256', $absolutePath);
                if ($currentHash !== $baseline->hash) {
                    $stats['modified'][] = ['path' => (string) $path, 'detail' => 'Modified', 'expected' => $baseline->hash, 'actual' => (string) $currentHash];
                    $stats['violations'][] = ['path' => (string) $path, 'status' => 'modified', 'detail' => 'Hash mismatch'];
                    if ($baseline->status !== 'modified') {
                        DB::table('sec_file_integrity_baselines')->where('file_path', $path)->update(['status' => 'modified', 'checked_at' => now()]);
                    }
                } else {
                    $stats['ok']++;
                    if ($baseline->status !== 'ok') {
                        DB::table('sec_file_integrity_baselines')->where('file_path', $path)->update(['status' => 'ok', 'checked_at' => now()]);
                    }
                }
            }

            foreach ($currentFiles as $path) {
                if (! isset($baselines[$path]) && file_exists(base_path((string) $path))) {
                    $stats['new'][] = ['path' => (string) $path, 'detail' => 'New file'];
                    $stats['violations'][] = ['path' => (string) $path, 'status' => 'new', 'detail' => 'New file'];
                }
            }

            return $stats;
        });

        return $result;
    }

    /**
     * @return list<string>
     */
    private function getMonitoredFiles(): array
    {
        $files = self::MONITORED_FILES;

        // [SECURITY FIX M-07] Include critical route files
        foreach (self::MONITORED_ROUTE_FILES as $routeFile) {
            if (file_exists(base_path($routeFile))) {
                $files[] = $routeFile;
            }
        }

        foreach (self::MONITORED_DIR_PATTERNS as $pattern) {
            $dirs = glob(base_path($pattern), GLOB_ONLYDIR);
            if ($dirs === false) {
                continue;
            }
            foreach ($dirs as $dirPath) {
                $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dirPath, \FilesystemIterator::SKIP_DOTS));
                foreach ($iterator as $file) {
                    if ($file instanceof \SplFileInfo && $file->getExtension() === 'php') {
                        $files[] = str_replace(base_path().'/', '', $file->getPathname());
                    }
                }
            }
        }

        return array_values(array_unique($files));
    }
}
