<?php

namespace Modules\Core\System\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Modules\Content\Library\Models\Tag;
use Modules\Content\Media\Models\File;
use Modules\Core\System\Jobs\QueueHeartbeatJob;
use Modules\Core\System\Models\Analytics;
use Modules\Core\System\Models\EmailTemplate;
use Modules\Core\System\Models\PageView;
use Modules\Core\System\Models\Setting;
use Modules\Core\System\Models\User;
use Modules\Intelligence\Newsletter\Models\NewsletterSubscriber;

class SystemService
{
    /**
     * Get system information
     *
     * @return array<string, mixed>
     */
    public function getSystemInfo(): array
    {
        $memoryInfo = $this->getMemoryUsage();
        $diskInfo = $this->getDiskUsage();

        // [SECURITY FIX H-04] In production, mask exact version strings and
        // configuration details to prevent attackers from targeting known CVEs.
        $isProduction = app()->environment('production', 'staging');

        return [
            // Version fingerprinting info — masked in production
            'php_version' => $isProduction ? PHP_MAJOR_VERSION.'.x' : PHP_VERSION,
            'laravel_version' => $isProduction ? 'latest' : app()->version(),
            'server' => $isProduction ? 'web-server' : ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'),
            'server_software' => $isProduction ? 'web-server' : ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'),
            // Database name shown in admin dashboard — safe for authenticated admins
            'database' => DB::connection()->getDatabaseName(),
            'timezone' => is_scalar($tz = config('app.timezone')) ? (string) $tz : 'UTC',
            'locale' => is_scalar($loc = config('app.locale')) ? (string) $loc : 'en',
            // Environment and debug mode — always show to authenticated admins for diagnostics,
            // but the underlying endpoint MUST be behind 'permission:manage settings' (verified).
            'environment' => app()->environment(),
            'debug_mode' => (bool) config('app.debug'),
            'cache_driver' => is_scalar($cd = config('cache.default')) ? (string) $cd : 'file',
            'queue_driver' => is_scalar($qd = config('queue.default')) ? (string) $qd : 'sync',
            'session_driver' => is_scalar($sd = config('session.driver')) ? (string) $sd : 'file',
            // Memory usage info
            'memory_usage' => $memoryInfo['used'],
            'memory_usage_percent' => $memoryInfo['percent'],
            'memory_total' => $memoryInfo['total'],
            // Disk usage info
            'disk_usage' => [
                'used' => $diskInfo['used'],
                'total' => $diskInfo['total'],
                'percent' => $diskInfo['percent'],
            ],
            'disk_usage_percent' => $diskInfo['percent'],
            // Uptime
            'uptime' => $this->getUptime(),
            'queue_health' => $this->getQueueHealth(),
        ];
    }

    /**
     * Get queue connection health and worker status
     *
     * @return array{driver: string, last_seen: mixed, is_active: bool, status: string, message: string}
     */
    public function getQueueHealth(): array
    {
        $driver = config('queue.default');
        $lastSeen = Cache::get('queue_worker_last_seen');

        // Dispatch a heartbeat job for the next check
        if ($driver !== 'sync') {
            try {
                QueueHeartbeatJob::dispatch();
            } catch (\Throwable) {
                // Ignore dispatch errors here
            }
        }

        $isActive = false;
        if ($driver === 'sync') {
            $isActive = true;
        } elseif (is_string($lastSeen)) {
            // If seen in the last 5 minutes, consider it active
            $isActive = now()->parse($lastSeen)->diffInMinutes() < 5;
        }

        return [
            'driver' => is_scalar($driver) ? (string) $driver : 'sync',
            'last_seen' => $lastSeen,
            'is_active' => $isActive,
            'status' => $isActive ? 'ok' : ($driver === 'sync' ? 'ok' : 'warning'),
            'message' => $isActive
                ? ($driver === 'sync' ? 'Sync (Direct)' : 'Worker Active')
                : 'Worker Not Detected',
        ];
    }

    /**
     * Get application statistics
     *
     * @return array<string, mixed>
     */
    public function getStatistics(): array
    {
        $cached = Cache::get('system_statistics');
        if (is_array($cached)) {
            /** @var array<string, mixed> $cached */
            return $cached;
        }

        try {
            // Get page views/visits count (if analytics exists)
            $totalVisits = 0;
            try {
                if (class_exists(PageView::class)) {
                    $totalVisits = PageView::count();
                } elseif (class_exists(Analytics::class)) {
                    $totalVisits = Analytics::count();
                }
            } catch (\Exception $e) {
                // Visits tracking not available
            }

            $registry = app(DashboardRegistry::class);

            $stats = [
                // Base Core Stats
                'total_users' => User::count(),
                'total_media' => File::count(),
                'total_visits' => $totalVisits,
                'users' => [
                    'total' => User::count(),
                    'verified' => User::whereNotNull('email_verified_at')->count(),
                ],
                'media' => [
                    'total' => File::count(),
                    'total_size' => File::sum('size'),
                ],
                'tags' => Tag::count(),
                'email' => [
                    'templates' => class_exists(EmailTemplate::class) ? EmailTemplate::count() : 0,
                    'subscribers' => class_exists(NewsletterSubscriber::class) ? NewsletterSubscriber::count() : 0,
                    'smtp_status' => strtoupper(is_string($statusVal = Cache::get('email_smtp_status', 'active')) ? $statusVal : 'active'),
                ],
            ];

            // Merge module stats (Flattened)
            foreach ($registry->getAllStats() as $moduleStat) {
                if (is_array($moduleStat)) {
                    $stats = array_replace_recursive($stats, $moduleStat);
                }
            }

            // Backward compatibility for common flat keys if not provided by modules
            if (! isset($stats['total_contents']) && isset($stats['contents']['total'])) {
                $stats['total_contents'] = $stats['contents']['total'];
            }

            Cache::put('system_statistics', $stats, 300);

            return $stats;
        } catch (\Exception $e) {
            Log::error('System statistics error: '.$e->getMessage());

            return $this->getDefaultStatistics();
        }
    }

    /**
     * Get default statistics on error
     *
     * @return array<string, mixed>
     */
    protected function getDefaultStatistics(): array
    {
        return [
            'total_contents' => 0,
            'total_users' => 0,
            'total_media' => 0,
            'total_visits' => 0,
            'contents' => ['total' => 0, 'published' => 0, 'draft' => 0],
            'users' => ['total' => 0, 'verified' => 0],
            'media' => ['total' => 0, 'total_size' => 0],
            'categories' => 0,
            'tags' => 0,
            'comments' => 0,
            'forms' => 0,
            'form_submissions' => 0,
        ];
    }

    /**
     * Get system uptime
     */
    public function getUptime(): ?int
    {
        try {
            // Read from /proc/uptime (Linux standard)
            if (file_exists('/proc/uptime')) {
                $uptimeRaw = file_get_contents('/proc/uptime');
                if ($uptimeRaw !== false) {
                    $uptimeArray = explode(' ', $uptimeRaw);

                    return (int) $uptimeArray[0];
                }
            }
            // [SECURITY FIX L-03] Removed shell_exec('uptime -s') fallback.
            // shell_exec increases attack surface for command injection chaining.
            // /proc/uptime is available on all Linux deployments and is sufficient.
        } catch (\Exception $e) {
            Log::debug('Uptime error: '.$e->getMessage());
        }

        return null;
    }

    /**
     * Get comprehensive system health
     *
     * @return array<string, mixed>
     */
    public function getSystemHealth(): array
    {
        return Cache::remember('system_health', 60, function (): array {
            $health = [
                'cpu' => $this->getCpuUsage(),
                'memory' => $this->getMemoryUsage(),
                'disk' => $this->getDiskUsage(),
                'database' => $this->checkDatabase(),
                'redis' => $this->checkRedis(),
                'overall' => 'healthy',
            ];

            // Determine overall status
            $critical = ($health['cpu']['percent'] > 90 || $health['memory']['percent'] > 90 || $health['disk']['percent'] > 90);
            $warning = ($health['cpu']['percent'] > 75 || $health['memory']['percent'] > 75 || $health['disk']['percent'] > 75);

            if ($health['database']['status'] !== 'ok' || $health['redis']['status'] !== 'ok') {
                $critical = true;
            }

            $health['overall'] = $critical ? 'critical' : ($warning ? 'warning' : 'healthy');

            return $health;
        });
    }

    /**
     * Get CPU usage
     *
     * @return array{percent: float, load: float, cores: int, status: string}
     */
    public function getCpuUsage(): array
    {
        try {
            // 1. Get Core Count
            $cores = 1;
            if (function_exists('shell_exec')) {
                // Try nproc
                if ($nproc = @shell_exec('nproc')) {
                    $cores = (int) trim($nproc);
                }
                // Fallback to reading cpuinfo lines
                elseif (file_exists('/proc/cpuinfo')) {
                    $cpuinfo = file_get_contents('/proc/cpuinfo');
                    if ($cpuinfo !== false) {
                        $cores = substr_count($cpuinfo, 'processor');
                    }
                }
            } elseif (file_exists('/proc/cpuinfo')) {
                $cpuinfo = file_get_contents('/proc/cpuinfo');
                if ($cpuinfo !== false) {
                    $cores = substr_count($cpuinfo, 'processor');
                }
            }

            if ($cores < 1) {
                $cores = 1;
            }

            // 2. Get Real-Time CPU Usage from /proc/stat
            // We sample for 200ms to get an accurate instantaneous reading
            if (file_exists('/proc/stat')) {
                $stat1 = file_get_contents('/proc/stat');
                usleep(200000); // Sleep 200ms
                $stat2 = file_get_contents('/proc/stat');

                if ($stat1 !== false && $stat2 !== false) {
                    $info1 = $this->parseProcStat($stat1);
                    $info2 = $this->parseProcStat($stat2);

                    if ($info1 && $info2) {
                        // Calculate deltas
                        $diffTotal = $info2['total'] - $info1['total'];
                        $diffIdle = $info2['idle'] - $info1['idle'];

                        // Prevent division by zero
                        if ($diffTotal > 0) {
                            $cpuPercent = (($diffTotal - $diffIdle) / $diffTotal) * 100;

                            // Get load average just for display
                            $load = sys_getloadavg();
                            $loadAvg = is_array($load) ? $load[0] : 0.0;

                            return [
                                'percent' => round($cpuPercent, 2),
                                'load' => $loadAvg,
                                'cores' => $cores,
                                'status' => $cpuPercent > 90 ? 'critical' : ($cpuPercent > 75 ? 'warning' : 'ok'),
                            ];
                        }
                    }
                }
            }

            // Fallback to Load Average if /proc/stat fails
            if (file_exists('/proc/loadavg')) {
                $load = sys_getloadavg();
                if (is_array($load)) {
                    // Load is number of runnable processes.
                    // Load 1.0 on 1 core = 100%. Load 8.0 on 8 cores = 100%.
                    $cpuPercent = min(100, ($load[0] * 100) / $cores);

                    return [
                        'percent' => round($cpuPercent, 2),
                        'load' => $load[0],
                        'cores' => $cores,
                        'status' => $cpuPercent > 90 ? 'critical' : ($cpuPercent > 75 ? 'warning' : 'ok'),
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::debug('CPU usage error: '.$e->getMessage());
        }

        return ['percent' => 0, 'load' => 0.0, 'cores' => 1, 'status' => 'unknown'];
    }

    /**
     * Parse /proc/stat content
     *
     * @return array{total: float|int, idle: float|int}|null
     */
    private function parseProcStat(string $content): ?array
    {
        // Get the first line which starts with "cpu "
        $lines = explode("\n", $content);
        foreach ($lines as $line) {
            if (str_starts_with($line, 'cpu ')) {
                // Format: cpu  user nice system idle iowait irq softirq steal guest guest_nice
                $partsRaw = preg_split('/\s+/', trim($line));
                if ($partsRaw === false) {
                    continue;
                }
                $parts = $partsRaw;
                // Remove 'cpu'
                array_shift($parts);

                // Sum all columns for total time
                $numParts = array_map(floatval(...), $parts);
                $total = array_sum($numParts);
                // Idle is the 4th column (index 3) + iowait (index 4) usually considered idle regarding CPU utilization?
                // Standard calculation: Idle = idle + iowait
                // Linux 2.6+:
                // user, nice, system, idle, iowait, irq, softirq, steal, guest, guest_nice
                // indexes: 0, 1, 2, 3, 4, 5, 6, 7, 8, 9

                $idle = ($numParts[3] ?? 0) + ($numParts[4] ?? 0);

                return ['total' => $total, 'idle' => $idle];
            }
        }

        return null;
    }

    /**
     * Get memory usage
     *
     * @return array{percent: float, used: string, total: string, available: string, status: string}
     */
    public function getMemoryUsage(): array
    {
        try {
            $memInfo = @file_get_contents('/proc/meminfo');
            if ($memInfo) {
                preg_match('/MemTotal:\s+(\d+)\s+kB/', $memInfo, $total);
                preg_match('/MemAvailable:\s+(\d+)\s+kB/', $memInfo, $available);

                if (isset($total[1]) && isset($available[1])) {
                    $totalMem = (int) $total[1] * 1024;
                    $availableMem = (int) $available[1] * 1024;
                    $usedMem = $totalMem - $availableMem;
                    $percent = ($usedMem / $totalMem) * 100;

                    return [
                        'percent' => round($percent, 2),
                        'used' => $this->formatBytes($usedMem),
                        'total' => $this->formatBytes($totalMem),
                        'available' => $this->formatBytes($availableMem),
                        'status' => $percent > 90 ? 'critical' : ($percent > 75 ? 'warning' : 'ok'),
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::debug('Memory usage error: '.$e->getMessage());
        }

        return ['percent' => 0.0, 'used' => '0 B', 'total' => '0 B', 'available' => '0 B', 'status' => 'unknown'];
    }

    /**
     * Get disk usage
     *
     * @return array{percent: float, used: string, total: string, free: string, status: string}
     */
    public function getDiskUsage(): array
    {
        try {
            $path = base_path();
            $totalRaw = @disk_total_space($path);
            $freeRaw = @disk_free_space($path);

            if ($totalRaw === false || $freeRaw === false) {
                return ['percent' => 0.0, 'used' => '0 B', 'total' => '0 B', 'free' => '0 B', 'status' => 'unknown'];
            }

            $total = $totalRaw;
            $free = $freeRaw;
            $used = $total - $free;
            $percent = $total > 0 ? ($used / $total) * 100 : 0;

            return [
                'percent' => round($percent, 2),
                'used' => $this->formatBytes($used),
                'total' => $this->formatBytes($total),
                'free' => $this->formatBytes($free),
                'status' => $percent > 90 ? 'critical' : ($percent > 75 ? 'warning' : 'ok'),
            ];
        } catch (\Exception) {
            return ['percent' => 0.0, 'used' => '0 B', 'total' => '0 B', 'free' => '0 B', 'status' => 'unknown'];
        }
    }

    /**
     * Check database connection
     *
     * @return array{status: string, message: string}
     */
    public function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();

            return ['status' => 'ok', 'message' => 'Connected'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Check Redis connection
     *
     * @return array{status: string, message: string}
     */
    public function checkRedis(): array
    {
        try {
            // Check if Redis class exists and connection is configured
            if (class_exists(Redis::class)) {
                try {
                    $redis = Redis::connection();
                    $redis->ping();

                    return ['status' => 'ok', 'message' => 'Connected'];
                } catch (\Exception $e) {
                    // Only return error if it's actually configured but failing
                    // If not configured (e.g. standard file cache), return disabled
                    if (config('database.redis.default.host')) {
                        return ['status' => 'error', 'message' => 'Connection Failed: '.$e->getMessage()];
                    }
                }
            }

            return ['status' => 'disabled', 'message' => 'Redis not configured'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Get cache size and count
     *
     * @return array{size: string, count: int}
     */
    public function getCacheStats(string $driver = 'file'): array
    {
        if ($driver === 'file') {
            $cachePath = storage_path('framework/cache');
            if (is_dir($cachePath)) {
                $size = 0;
                $count = 0;
                /** @var \SplFileInfo $file */
                foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($cachePath)) as $file) {
                    if ($file->isFile()) {
                        $size += $file->getSize();
                        $count++;
                    }
                }

                return ['size' => $this->formatBytes($size), 'count' => $count];
            }

            return ['size' => '0 B', 'count' => 0];
        }

        return ['size' => '0 B', 'count' => 0];
    }

    /**
     * Get cache size (backward compatibility)
     */
    public function getCacheSize(): string
    {
        return $this->getCacheStats('file')['size'];
    }

    /**
     * Get cache status with stats
     *
     * @return array<string, mixed>
     */
    public function getCacheStatus(): array
    {
        $cacheEnabledRaw = Setting::get('enable_cache', true);
        $isEnabled = filter_var($cacheEnabledRaw, FILTER_VALIDATE_BOOLEAN);

        $configuredDriverRaw = Setting::get('cache_driver', config('cache.default', 'file'));
        $driver = is_scalar($configuredDriverRaw) ? (string) $configuredDriverRaw : 'file';

        $hits = 0;
        $misses = 0;
        $keys = 0;
        $size = '0 B';

        if ($isEnabled) {
            $driverStr = strtolower($driver);
            if (str_starts_with($driverStr, 'redis') || $driverStr === 'failover') {
                try {
                    $redis = Redis::connection();
                    $redis->ping();

                    $info = $redis->info('stats');
                    $hits = $info['keyspace_hits'] ?? 0;
                    $misses = $info['keyspace_misses'] ?? 0;
                    $keys = $redis->dbsize();

                    $memory = $redis->info('memory');
                    $size = $this->formatBytes($memory['used_memory'] ?? 0);
                } catch (\Exception $e) {
                    Log::debug('Redis stats not available: '.$e->getMessage());
                    if ($driverStr === 'failover') {
                        $stats = $this->getCacheStats('file');
                        $size = $stats['size'];
                        $keys = $stats['count'];
                    }
                }
            } elseif ($driverStr === 'file') {
                $stats = $this->getCacheStats('file');
                $size = $stats['size'];
                $keys = $stats['count'];
            } elseif ($driverStr === 'database') {
                try {
                    $tableNameRaw = config('cache.stores.database.table', 'cache');
                    $tableName = is_scalar($tableNameRaw) ? (string) $tableNameRaw : 'cache';
                    $keys = DB::table($tableName)->count();
                } catch (\Exception) {
                }
            }
        }

        return [
            'status' => $isEnabled ? 'Enabled' : 'Disabled',
            'enabled' => $isEnabled,
            'driver' => $driver,
            'hits' => $hits,
            'misses' => $misses,
            'keys' => $isEnabled ? $keys : 0,
            'size' => $isEnabled ? $size : '0 B',
        ];
    }

    /**
     * Get database size information
     *
     * @return array{total_mb: float|int, formatted: string}
     */
    public function getDatabaseSize(): array
    {
        try {
            $database = DB::connection()->getDatabaseName();
            /** @var array<int, \stdClass> $size */
            $size = DB::select('SELECT
                ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
                FROM information_schema.TABLES
                WHERE table_schema = ?', [$database]);

            $sizeMb = $size[0]->size_mb ?? 0;

            return [
                'total_mb' => $sizeMb,
                'formatted' => $this->formatBytes($sizeMb * 1024 * 1024),
            ];
        } catch (\Exception) {
            return ['total_mb' => 0, 'formatted' => '0 B'];
        }
    }

    /**
     * Get table statistics
     *
     * @return array<int, array{name: string, size_mb: float|int, rows: int, formatted_size: string}>
     */
    public function getTableStatistics(): array
    {
        try {
            $database = DB::connection()->getDatabaseName();
            /** @var array<int, \stdClass> $tables */
            $tables = DB::select('SELECT
                table_name,
                ROUND((data_length + index_length) / 1024 / 1024, 2) AS size_mb,
                table_rows
                FROM information_schema.TABLES
                WHERE table_schema = ?
                ORDER BY (data_length + index_length) DESC
                LIMIT 10', [$database]);

            return array_map(fn (\stdClass $table) => [
                'name' => $table->table_name,
                'size_mb' => $table->size_mb,
                'rows' => $table->table_rows,
                'formatted_size' => $this->formatBytes($table->size_mb * 1024 * 1024),
            ], $tables);
        } catch (\Exception) {
            return [];
        }
    }

    /**
     * Format bytes to human readable
     */
    public function formatBytes(int|float $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $divisor = 1024 ** $pow;
        $bytes /= $divisor;

        return round($bytes, $precision).' '.$units[(int) $pow];
    }
}
