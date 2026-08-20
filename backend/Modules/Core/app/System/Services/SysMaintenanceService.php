<?php

declare(strict_types=1);

namespace Modules\Core\System\Services;

use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\Core\System\Support\ExtensionPaths;

class SysMaintenanceService
{
    /**
     * Clean all temporary and orphan files across sandboxes and upload directories.
     *
     * @return array{cleaned_bytes: int, files_removed: int}
     */
    public function cleanJunk(): array
    {
        $cleanedBytes = 0;
        $filesRemoved = 0;

        // 1. Clean temporary ZIP uploads, scaffold outputs, and temp media/cache
        $tempPaths = [
            storage_path('app/uploads'),
            storage_path('app/scaffolds'),
            storage_path('app/temp'),
            storage_path('app/private/temp'),
            storage_path('framework/views'),
            storage_path('framework/testing'),
        ];

        foreach ($tempPaths as $path) {
            if (File::isDirectory($path)) {
                $files = File::allFiles($path);
                foreach ($files as $file) {
                    // Do not delete gitignore files
                    if ($file->getFilename() === '.gitignore') {
                        continue;
                    }

                    $size = $file->getSize();
                    try {
                        File::delete($file->getRealPath());
                        $cleanedBytes += $size;
                        $filesRemoved++;
                    } catch (Exception) {
                        // Suppress failures on locked files
                    }
                }
            }
        }

        // 2. Clean temporary/expired cache files inside extension sandboxes
        $sandboxBase = storage_path('app/extensions');
        if (File::isDirectory($sandboxBase)) {
            $extensionDirs = File::directories($sandboxBase);
            foreach ($extensionDirs as $extDir) {
                $cachePath = $extDir.'/sandbox/cache';
                if (File::isDirectory($cachePath)) {
                    $files = File::allFiles($cachePath);
                    foreach ($files as $file) {
                        $size = $file->getSize();
                        try {
                            File::delete($file->getRealPath());
                            $cleanedBytes += $size;
                            $filesRemoved++;
                        } catch (Exception) {
                        }
                    }
                }
            }
        }

        Log::info('[SysMaintenance] Junk cleaner routine completed.', [
            'files_removed' => $filesRemoved,
            'bytes_freed' => $cleanedBytes,
        ]);

        return [
            'cleaned_bytes' => $cleanedBytes,
            'freed_bytes' => $cleanedBytes,
            'files_removed' => $filesRemoved,
            'deleted_files' => $filesRemoved,
        ];
    }

    /**
     * Optimize database tables and clear orphan dynamic records.
     *
     * @return array{success: bool, optimized_tables: int, purged_orphans: int}
     */
    public function optimizeDatabase(): array
    {
        $purgedOrphans = 0;
        $optimizedTablesCount = 0;

        // 1. Purge orphan dynamic CCK records (records whose ContentType no longer exists)
        if (Schema::hasTable('sys_content_types') && Schema::hasTable('sys_dynamic_records')) {
            $activeTypeIds = DB::table('sys_content_types')->pluck('id')->toArray();
            if (! empty($activeTypeIds)) {
                $purgedOrphans = DB::table('sys_dynamic_records')
                    ->whereNotIn('content_type_id', $activeTypeIds)
                    ->delete();
            } else {
                $purgedOrphans = DB::table('sys_dynamic_records')->delete();
            }
        }

        // 2. Optimize DB tables (Vacuum for SQLite, Optimize for MySQL, Vacuum Analyze for PostgreSQL)
        try {
            $driver = DB::connection()->getDriverName();
            if ($driver === 'sqlite') {
                DB::statement('VACUUM');
                $optimizedTablesCount = 1;
            } else {
                $targetTables = [
                    'pub_contents',
                    'pub_content_revisions',
                    'pub_comments',
                    'srv_media_files',
                    'srv_media_usages',
                    'srv_auth_users',
                    'sys_settings',
                    'sys_content_types',
                    'sys_dynamic_records',
                    'srv_analytics_visits',
                    'srv_analytics_events',
                    'srch_indexes',
                    'srch_queries',
                    'infra_backups',
                    'sec_logs',
                    'system_activity_logs',
                ];

                foreach ($targetTables as $table) {
                    if (Schema::hasTable($table)) {
                        if ($driver === 'pgsql') {
                            DB::statement("VACUUM ANALYZE {$table}");
                        } elseif ($driver === 'mysql') {
                            DB::statement("OPTIMIZE TABLE {$table}");
                        }
                        $optimizedTablesCount++;
                    }
                }
            }
        } catch (Exception $e) {
            Log::warning('[SysMaintenance] Database table optimization threw an exception: '.$e->getMessage());
        }

        Log::info('[SysMaintenance] Database optimizer routine completed.', [
            'purged_orphans' => $purgedOrphans,
            'optimized_tables' => $optimizedTablesCount,
        ]);

        return [
            'success' => true,
            'optimized_tables' => $optimizedTablesCount,
            'purged_orphans' => $purgedOrphans,
        ];
    }

    /**
     * Run in-process framework optimization warm-ups.
     *
     * @return array{success: bool}
     */
    public function boostPerformance(): array
    {
        try {
            // Clear framework compilation caches to resolve segmentations
            Artisan::call('optimize:clear');

            // Warm-up configuration caches
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');
            try {
                Artisan::call('event:cache');
            } catch (Exception) {
                // event:cache optional if no event discovery
            }
        } catch (Exception $e) {
            Log::warning('[SysMaintenance] Framework boost compilation failed: '.$e->getMessage());
        }

        Log::info('[SysMaintenance] Performance boost caches generated.');

        return [
            'success' => true,
        ];
    }

    /**
     * Reset the entire system back to pristine clean factory default state.
     *
     * @return array{success: bool, message: string}
     */
    public function factoryResetStep1(): array
    {
        // 1. Wipe all local custom extension packages (backend/extensions/*)
        $extensionsDir = ExtensionPaths::root();
        if (File::isDirectory($extensionsDir)) {
            $subDirs = File::directories($extensionsDir);
            foreach ($subDirs as $subDir) {
                File::deleteDirectory($subDir);
            }
        }

        // 2. Wipe all VFS Sandboxes files
        $sandboxBase = storage_path('app/extensions');
        if (File::isDirectory($sandboxBase)) {
            File::deleteDirectory($sandboxBase);
            File::makeDirectory($sandboxBase, 0755, true);
        }

        // 3. Clear temporary files
        $this->cleanJunk();

        return ['success' => true, 'message' => 'Junk and Sandboxes cleared'];
    }

    /**
     * @return array{success: bool, message: string}
     */
    public function factoryResetStep2(): array
    {
        // 1. Wipe all Media / Uploaded files
        $publicStorage = storage_path('app/public');
        if (File::isDirectory($publicStorage)) {
            try {
                File::deleteDirectory($publicStorage);
                File::makeDirectory($publicStorage, 0755, true);
            } catch (Exception $e) {
                // Ignore permission errors
            }
        }

        // 2. Clear all logs
        $logPath = storage_path('logs');
        if (File::isDirectory($logPath)) {
            $logFiles = File::allFiles($logPath);
            foreach ($logFiles as $file) {
                if ($file->getFilename() !== '.gitignore') {
                    try {
                        File::delete($file->getRealPath());
                    } catch (Exception $e) {
                        // Ignore permission errors when deleting log files
                    }
                }
            }
        }

        return ['success' => true, 'message' => 'Media and Logs wiped'];
    }

    /**
     * @return array{success: bool, message: string}
     */
    public function factoryResetStep3(): array
    {
        // 1. Flush Cache
        try {
            Cache::flush();
        } catch (Exception) {
            // Ignore cache flush errors
        }

        // 2. Wipe DB migrations fresh
        Artisan::call('migrate:fresh', ['--force' => true]);

        // 3. Create a post-reset flag to trigger a wizard on next login
        $setupToken = Str::random(64);
        File::put(storage_path('app/.post_reset'), $setupToken);

        Log::critical('[SysMaintenance] Platform has been restored to factory default pristine state.');

        return ['success' => true, 'message' => 'Factory reset completed', 'setup_token' => $setupToken];
    }

    /**
     * Legacy single-step reset.
     *
     * @return array{success: bool, message: string}
     */
    public function factoryReset(): array
    {
        $this->factoryResetStep1();
        $this->factoryResetStep2();
        $this->factoryResetStep3();

        return [
            'success' => true,
            'message' => 'System successfully restored to default factory clean state.',
        ];
    }
}
