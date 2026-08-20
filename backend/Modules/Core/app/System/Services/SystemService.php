<?php

namespace Modules\Core\System\Services;

use Illuminate\Support\Facades\Artisan;
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

        // Distro detection
        $distro = php_uname('s');
        if (file_exists('/etc/os-release')) {
            $osLines = @file('/etc/os-release') ?: [];
            foreach ($osLines as $line) {
                if (str_starts_with($line, 'PRETTY_NAME=')) {
                    $distro = trim(str_replace(['PRETTY_NAME=', '"', "'"], '', $line));
                    break;
                }
            }
        }

        // Database version
        $dbVersion = 'Unknown';
        try {
            $dbRow = DB::select('SELECT VERSION() as v');
            $dbVersion = $dbRow[0]->v ?? 'Unknown';
        } catch (\Throwable) {
        }

        // Web server detection
        $serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? (PHP_SAPI === 'cli' ? 'CLI / Background Runner' : 'Web Server');

        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server' => $serverSoftware,
            'server_software' => $serverSoftware,
            'os_distro' => $distro,
            'os_kernel' => php_uname('s').' '.php_uname('r').' ('.php_uname('m').')',
            'php_sapi' => PHP_SAPI,
            'database' => DB::connection()->getDatabaseName(),
            'database_version' => $dbVersion,
            'timezone' => is_scalar($tz = config('app.timezone')) ? (string) $tz : 'UTC',
            'locale' => is_scalar($loc = config('app.locale')) ? (string) $loc : 'en',
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

    /**
     * Get comprehensive system requirements checklist and environment diagnostics
     *
     * @return array<string, mixed>
     */
    public function getRequirements(): array
    {
        $phpVersion = PHP_VERSION;
        $phpVersionOk = version_compare($phpVersion, '8.2.0', '>=');

        // Distro detection
        $distro = php_uname('s');
        if (file_exists('/etc/os-release')) {
            $osLines = @file('/etc/os-release') ?: [];
            foreach ($osLines as $line) {
                if (str_starts_with($line, 'PRETTY_NAME=')) {
                    $distro = trim(str_replace(['PRETTY_NAME=', '"', "'"], '', $line));
                    break;
                }
            }
        }

        // Database engine & version
        $dbEngine = config('database.default', 'mysql');
        $dbVersion = 'Unknown';
        try {
            $dbVersionRow = DB::select('SELECT VERSION() as v');
            $dbVersion = $dbVersionRow[0]->v ?? 'Connected';
        } catch (\Throwable $e) {
            $dbVersion = 'Disconnected: '.$e->getMessage();
        }

        // Redis status
        $redisVersion = 'Disabled / Not Connected';
        $redisLatency = '-';
        $redisMemory = '-';
        try {
            if (class_exists('Illuminate\Support\Facades\Redis')) {
                $rStart = microtime(true);
                Redis::ping();
                $redisLatency = round((microtime(true) - $rStart) * 1000, 2).' ms';
                $rInfo = Redis::info('server');
                $redisVersion = $rInfo['redis_version'] ?? 'Active';
                $rMem = Redis::info('memory');
                $redisMemory = $this->formatBytes($rMem['used_memory'] ?? 0);
            }
        } catch (\Throwable) {
        }

        // Node & NPM versions
        $nodeVersion = 'Not installed';
        try {
            $nodeOut = @shell_exec('node -v 2>&1');
            if ($nodeOut && trim($nodeOut) && ! str_contains($nodeOut, 'not found')) {
                $nodeVersion = trim($nodeOut);
            }
        } catch (\Throwable) {
        }

        $npmVersion = 'Not installed';
        try {
            $npmOut = @shell_exec('npm -v 2>&1');
            if ($npmOut && trim($npmOut) && ! str_contains($npmOut, 'not found')) {
                $npmVersion = trim($npmOut);
            }
        } catch (\Throwable) {
        }

        // Queue worker processes
        $queueWorkersCount = 0;
        try {
            $psOut = @shell_exec('ps aux | grep "artisan queue:work" | grep -v grep 2>&1');
            if ($psOut && trim($psOut)) {
                $lines = array_filter(explode("\n", trim($psOut)));
                $queueWorkersCount = count($lines);
            }
        } catch (\Throwable) {
        }

        // Cron check
        $cronConfigured = false;
        try {
            $crontabOut = @shell_exec('crontab -l 2>&1');
            if ($crontabOut && str_contains($crontabOut, 'schedule:run')) {
                $cronConfigured = true;
            }
        } catch (\Throwable) {
        }

        // Storage paths
        $storagePaths = [
            [
                'id' => 'storage_views',
                'name' => 'storage/framework/views',
                'path' => storage_path('framework/views'),
                'desc' => 'Direktori tempat Laravel mengompilasi template Blade ke PHP.',
            ],
            [
                'id' => 'storage_cache',
                'name' => 'storage/framework/cache',
                'path' => storage_path('framework/cache'),
                'desc' => 'Direktori penyimpanan file cache aplikasi dan rate limiter.',
            ],
            [
                'id' => 'storage_sessions',
                'name' => 'storage/framework/sessions',
                'path' => storage_path('framework/sessions'),
                'desc' => 'Direktori fallback penyimpanan sesi pengguna jika menggunakan driver file.',
            ],
            [
                'id' => 'storage_public',
                'name' => 'storage/app/public',
                'path' => storage_path('app/public'),
                'desc' => 'Direktori upload berkas media, gambar, dan dokumen publik.',
            ],
            [
                'id' => 'storage_backups',
                'name' => 'storage/app/backups',
                'path' => storage_path('app/backups'),
                'desc' => 'Direktori penyimpanan arsip zip snapshot cadangan sistem.',
            ],
            [
                'id' => 'storage_logs',
                'name' => 'storage/logs',
                'path' => storage_path('logs'),
                'desc' => 'Direktori penulisan berkas log aktivitas dan diagnostic error.',
            ],
            [
                'id' => 'bootstrap_cache',
                'name' => 'bootstrap/cache',
                'path' => app()->bootstrapPath('cache'),
                'desc' => 'Direktori file manifest routes, config, dan packages cache.',
            ],
        ];

        $items = [];

        // 1. PHP Core Runtime
        $items[] = [
            'id' => 'php_version',
            'name' => 'PHP Version',
            'category' => 'php_core',
            'required' => true,
            'current_value' => $phpVersion,
            'required_value' => '>= 8.2.0',
            'status' => $phpVersionOk ? 'ok' : 'error',
            'description' => 'Versi runtime PHP minimum yang didukung JA-CMS adalah 8.2.0 (disarankan 8.3+).',
            'fix_guide' => [
                'ubuntu' => 'sudo apt-get install -y php8.3 php8.3-cli php8.3-fpm',
                'rhel' => 'sudo dnf module enable php:8.3 && sudo dnf install -y php php-cli php-fpm',
                'general' => 'Upgrade versi PHP web server Anda melalui control panel hosting ke versi minimal 8.2 atau 8.3.',
            ],
            'can_autofix' => false,
        ];

        $memLimit = (string) ini_get('memory_limit');
        $memLimitBytes = $this->parseIniSize($memLimit);
        $memLimitOk = $memLimitBytes === -1 || $memLimitBytes >= (256 * 1024 * 1024);
        $items[] = [
            'id' => 'php_memory_limit',
            'name' => 'PHP Memory Limit',
            'category' => 'php_core',
            'required' => true,
            'current_value' => $memLimit ?: 'Unknown',
            'required_value' => '>= 256M (Disarankan 512M)',
            'status' => $memLimitOk ? 'ok' : 'warning',
            'description' => 'Batas alokasi memori RAM untuk pemrosesan script PHP, rendering tema, dan pengolahan media.',
            'fix_guide' => [
                'ubuntu' => 'Edit file /etc/php/8.3/fpm/php.ini dan ubah memory_limit = 512M, lalu sudo systemctl restart php8.3-fpm',
                'rhel' => 'Edit file /etc/php.ini dan ubah memory_limit = 512M, lalu sudo systemctl restart php-fpm',
                'general' => 'Ubah nilai memory_limit menjadi minimal 256M atau 512M pada php.ini / cPanel PHP INI Editor.',
            ],
            'can_autofix' => false,
        ];

        $maxExec = (string) ini_get('max_execution_time');
        $maxExecOk = $maxExec === '0' || (int) $maxExec >= 30;
        $items[] = [
            'id' => 'php_max_execution_time',
            'name' => 'Max Execution Time',
            'category' => 'php_core',
            'required' => false,
            'current_value' => ($maxExec ? $maxExec.'s' : 'Unlimited'),
            'required_value' => '>= 60s',
            'status' => $maxExecOk ? 'ok' : 'warning',
            'description' => 'Waktu eksekusi maksimum script untuk mencegah timeout saat backup atau import konten.',
            'fix_guide' => [
                'ubuntu' => 'Edit php.ini: max_execution_time = 60 lalu restart PHP-FPM',
                'rhel' => 'Edit php.ini: max_execution_time = 60 lalu restart PHP-FPM',
                'general' => 'Ubah max_execution_time menjadi minimal 60 di konfigurasi PHP.',
            ],
            'can_autofix' => false,
        ];

        $uploadMax = (string) ini_get('upload_max_filesize');
        $uploadMaxBytes = $this->parseIniSize($uploadMax);
        $uploadMaxOk = $uploadMaxBytes >= (10 * 1024 * 1024);
        $items[] = [
            'id' => 'php_upload_max_filesize',
            'name' => 'Upload Max Filesize',
            'category' => 'php_core',
            'required' => false,
            'current_value' => $uploadMax ?: 'Unknown',
            'required_value' => '>= 32M (Disarankan 64M+)',
            'status' => $uploadMaxOk ? 'ok' : 'warning',
            'description' => 'Ukuran maksimal file tunggal yang diizinkan untuk diunggah ke Media Library.',
            'fix_guide' => [
                'ubuntu' => 'Edit php.ini: upload_max_filesize = 64M & post_max_size = 64M lalu restart PHP-FPM',
                'rhel' => 'Edit php.ini: upload_max_filesize = 64M & post_max_size = 64M lalu restart PHP-FPM',
                'general' => 'Tingkatkan upload_max_filesize dan post_max_size di php.ini.',
            ],
            'can_autofix' => false,
        ];

        $opcacheEnabled = extension_loaded('Zend OPcache') && (bool) ini_get('opcache.enable');
        $items[] = [
            'id' => 'php_opcache',
            'name' => 'Zend OPcache Accelerator',
            'category' => 'php_core',
            'required' => false,
            'current_value' => $opcacheEnabled ? 'Aktif (Enabled)' : 'Nonaktif (Disabled)',
            'required_value' => 'Aktif (Disarankan untuk Production)',
            'status' => $opcacheEnabled ? 'ok' : 'warning',
            'description' => 'OPcache mengompilasi bytecode PHP di memori sehingga performa JA-CMS meningkat hingga 3x lipat.',
            'fix_guide' => [
                'ubuntu' => 'Edit php.ini: set opcache.enable=1 dan opcache.memory_consumption=128, lalu restart PHP-FPM',
                'rhel' => 'Edit php.ini: set opcache.enable=1 dan opcache.memory_consumption=128, lalu restart PHP-FPM',
                'general' => 'Aktifkan ekstensi OPcache pada server hosting / PHP settings.',
            ],
            'can_autofix' => false,
        ];

        // 2. PHP Extensions
        $requiredExtensions = [
            'bcmath' => ['name' => 'BCMath', 'desc' => 'Operasi matematika presisi tinggi untuk perhitungan finansial dan hash.'],
            'ctype' => ['name' => 'Ctype', 'desc' => 'Validasi karakter string dan sanitasi input pengguna.'],
            'curl' => ['name' => 'cURL', 'desc' => 'Komunikasi HTTP keluar untuk integrasi API, webhook, dan update IP Cloudflare.'],
            'dom' => ['name' => 'DOM', 'desc' => 'Parsing dokumen XML, HTML sanitization, dan rendering feed RSS/Sitemap.'],
            'fileinfo' => ['name' => 'FileInfo', 'desc' => 'Deteksi tipe MIME berkas unggahan secara aman tanpa mengandalkan ekstensi nama.'],
            'gd' => ['name' => 'GD / Imagick', 'desc' => 'Pemrosesan gambar, pembuatan thumbnail responsif, dan konversi WebP/AVIF.'],
            'intl' => ['name' => 'Intl', 'desc' => 'Internasionalisasi multibahasa, penomoran format lokal, dan penanggalan.'],
            'json' => ['name' => 'JSON', 'desc' => 'Enkoding dan dekoding data JSON untuk REST API dan penyimpanan metadata.'],
            'mbstring' => ['name' => 'Mbstring', 'desc' => 'Pengolahan karakter string multibyte (UTF-8) untuk artikel dan konten global.'],
            'openssl' => ['name' => 'OpenSSL', 'desc' => 'Enkripsi password, pembuatan token API Sanctum, dan koneksi HTTPS aman.'],
            'pdo' => ['name' => 'PDO', 'desc' => 'Abstraksi koneksi database utama Laravel Eloquent ORM.'],
            'tokenizer' => ['name' => 'Tokenizer', 'desc' => 'Parsing kode sumber PHP untuk kompilasi engine template Blade.'],
            'xml' => ['name' => 'XML', 'desc' => 'Parser berkas XML untuk import/export konten dan generator RSS feed.'],
            'zip' => ['name' => 'ZipArchive', 'desc' => 'Kompresi dan ekstraksi berkas arsip zip untuk backup dan instalasi plugin/tema.'],
            'redis' => ['name' => 'PhpRedis (Opsional)', 'desc' => 'Ekstensi PHP native untuk koneksi ultra cepat ke Redis cache & worker.', 'required' => false],
            'exif' => ['name' => 'EXIF (Opsional)', 'desc' => 'Membaca metadata foto kamera dan rotasi orientasi gambar otomatis.', 'required' => false],
        ];

        foreach ($requiredExtensions as $extKey => $extMeta) {
            $isExtRequired = $extMeta['required'] ?? true;
            $isLoaded = false;
            if ($extKey === 'gd') {
                $isLoaded = extension_loaded('gd') || extension_loaded('imagick');
            } else {
                $isLoaded = extension_loaded($extKey);
            }

            $items[] = [
                'id' => 'ext_'.$extKey,
                'name' => 'PHP Ext: '.$extMeta['name'],
                'category' => 'php_extensions',
                'required' => $isExtRequired,
                'current_value' => $isLoaded ? 'Terpasang (Installed)' : 'Tidak Ditemukan (Missing)',
                'required_value' => 'Terpasang',
                'status' => $isLoaded ? 'ok' : ($isExtRequired ? 'error' : 'warning'),
                'description' => $extMeta['desc'],
                'fix_guide' => [
                    'ubuntu' => "sudo apt-get install -y php8.3-{$extKey} && sudo systemctl restart php8.3-fpm",
                    'rhel' => "sudo dnf install -y php-{$extKey} && sudo systemctl restart php-fpm",
                    'general' => "Centang ekstensi '{$extKey}' pada menu PHP Extensions / PHP Selector di cPanel/DirectAdmin.",
                ],
                'can_autofix' => false,
            ];
        }

        // 3. Database & Cache Services
        $dbConnected = false;
        try {
            DB::connection()->getPdo();
            $dbConnected = true;
        } catch (\Throwable) {
        }

        $items[] = [
            'id' => 'service_db',
            'name' => 'Database Connection',
            'category' => 'database',
            'required' => true,
            'current_value' => $dbConnected ? "Terhubung ({$dbEngine} - {$dbVersion})" : 'Koneksi Gagal',
            'required_value' => 'Terhubung (PDO Active)',
            'status' => $dbConnected ? 'ok' : 'error',
            'description' => 'Koneksi aktif ke server basis data MySQL/MariaDB/PostgreSQL.',
            'fix_guide' => [
                'ubuntu' => 'Periksa kredensial DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD di file .env',
                'rhel' => 'Periksa kredensial DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD di file .env',
                'general' => 'Pastikan database server aktif dan kredensial di file .env sudah sesuai.',
            ],
            'can_autofix' => false,
        ];

        $redisActive = false;
        try {
            if (class_exists('Illuminate\Support\Facades\Redis')) {
                Redis::ping();
                $redisActive = true;
            }
        } catch (\Throwable) {
        }

        $items[] = [
            'id' => 'service_redis',
            'name' => 'Redis In-Memory Server',
            'category' => 'caching',
            'required' => false,
            'current_value' => $redisActive ? "Aktif (v{$redisVersion}, Ping: {$redisLatency})" : 'Tidak Terhubung',
            'required_value' => 'Aktif (Disarankan untuk High-Traffic)',
            'status' => $redisActive ? 'ok' : 'warning',
            'description' => 'Server Redis untuk in-memory cache berkecepatan tinggi dan antrean queue asynchronously.',
            'fix_guide' => [
                'ubuntu' => 'sudo apt-get install -y redis-server && sudo systemctl enable --now redis-server',
                'rhel' => 'sudo dnf install -y redis && sudo systemctl enable --now redis',
                'general' => 'Pastikan Redis server terinstall dan berjalan pada port 6379.',
            ],
            'can_autofix' => false,
        ];

        // 4. Storage & Directory Permissions
        foreach ($storagePaths as $s) {
            $p = $s['path'];
            $exists = file_exists($p);
            $writable = $exists && is_writable($p);

            $items[] = [
                'id' => $s['id'],
                'name' => 'Izin: '.$s['name'],
                'category' => 'storage_permissions',
                'required' => true,
                'current_value' => $writable ? 'Writable (0775 / OK)' : ($exists ? 'Read-Only (Terkunci)' : 'Direktori Tidak Ada'),
                'required_value' => 'Writable (Dapat Ditulis)',
                'status' => $writable ? 'ok' : 'error',
                'description' => $s['desc'],
                'fix_guide' => [
                    'ubuntu' => "mkdir -p {$p} && chmod -R 775 {$p} && chown -R www-data:www-data storage bootstrap/cache",
                    'rhel' => "mkdir -p {$p} && chmod -R 775 {$p} && chown -R nginx:nginx storage bootstrap/cache",
                    'general' => "Pastikan folder {$s['name']} memiliki permission write (775 atau 777).",
                ],
                'can_autofix' => true,
            ];
        }

        // Public storage symlink check
        $symlinkPath = public_path('storage');
        $symlinkOk = file_exists($symlinkPath) && is_link($symlinkPath);
        $items[] = [
            'id' => 'storage_symlink',
            'name' => 'Public Storage Symlink (public/storage)',
            'category' => 'storage_permissions',
            'required' => true,
            'current_value' => $symlinkOk ? 'Tersambung (Linked)' : 'Terputus / Belum Dibuat',
            'required_value' => 'Tersambung',
            'status' => $symlinkOk ? 'ok' : 'warning',
            'description' => 'Tautan simbolik dari storage/app/public ke public/storage agar file media dapat diakses browser.',
            'fix_guide' => [
                'ubuntu' => 'php artisan storage:link',
                'rhel' => 'php artisan storage:link',
                'general' => 'Jalankan perintah php artisan storage:link di terminal server.',
            ],
            'can_autofix' => true,
        ];

        // 5. Background Services & Cron
        $items[] = [
            'id' => 'service_cron',
            'name' => 'Cron Scheduler Daemon',
            'category' => 'background_services',
            'required' => false,
            'current_value' => $cronConfigured ? 'Terdeteksi di Crontab' : 'Belum Dikonfigurasi di Crontab',
            'required_value' => 'Aktif (* * * * * schedule:run)',
            'status' => $cronConfigured ? 'ok' : 'warning',
            'description' => 'Cron job server yang mengeksekusi scheduled tasks otomatis (backup, pembersihan log, auto-publish) setiap menit.',
            'fix_guide' => [
                'ubuntu' => 'crontab -e lalu tambahkan: * * * * * cd '.base_path().' && php artisan schedule:run >> /dev/null 2>&1',
                'rhel' => 'crontab -e lalu tambahkan: * * * * * cd '.base_path().' && php artisan schedule:run >> /dev/null 2>&1',
                'general' => 'Tambahkan cron job 1 menit di cPanel yang menjalankan php artisan schedule:run.',
            ],
            'can_autofix' => false,
        ];

        $items[] = [
            'id' => 'service_queue_worker',
            'name' => 'Background Queue Worker',
            'category' => 'background_services',
            'required' => false,
            'current_value' => $queueWorkersCount > 0 ? "{$queueWorkersCount} Worker Berjalan" : 'Tidak Ada Worker Aktif',
            'required_value' => 'Aktif (Disarankan via Supervisor)',
            'status' => $queueWorkersCount > 0 ? 'ok' : 'warning',
            'description' => 'Worker latar belakang untuk memproses tugas berat (pengiriman email masal, kompresi thumbnail, indexing pencarian).',
            'fix_guide' => [
                'ubuntu' => 'Konfigurasikan Supervisor di /etc/supervisor/conf.d/jacms-worker.conf untuk menjalankan php artisan queue:work',
                'rhel' => 'Konfigurasikan Supervisor / Systemd service untuk menjalankan php artisan queue:work',
                'general' => 'Gunakan Supervisor daemon untuk menjaga worker antrean tetap berjalan di latar belakang.',
            ],
            'can_autofix' => false,
        ];

        // Compute summary overview
        $total = count($items);
        $passed = count(array_filter($items, fn ($i) => $i['status'] === 'ok'));
        $warnings = count(array_filter($items, fn ($i) => $i['status'] === 'warning'));
        $errors = count(array_filter($items, fn ($i) => $i['status'] === 'error'));
        $scorePercent = $total > 0 ? (int) round(($passed / $total) * 100) : 100;

        return [
            'overview' => [
                'total' => $total,
                'passed' => $passed,
                'warnings' => $warnings,
                'errors' => $errors,
                'score_percent' => $scorePercent,
                'is_ready' => $errors === 0,
            ],
            'server_spec' => [
                'distro' => $distro,
                'kernel' => php_uname('s').' '.php_uname('r').' ('.php_uname('m').')',
                'php_version' => $phpVersion,
                'php_sapi' => PHP_SAPI,
                'web_server' => $_SERVER['SERVER_SOFTWARE'] ?? (PHP_SAPI === 'cli' ? 'CLI / Background Runner' : 'Web Server'),
                'database_engine' => $dbEngine,
                'database_version' => $dbVersion,
                'redis_version' => $redisVersion,
                'redis_latency' => $redisLatency,
                'redis_memory' => $redisMemory,
                'node_version' => $nodeVersion,
                'npm_version' => $npmVersion,
                'queue_workers_count' => $queueWorkersCount,
                'cron_configured' => $cronConfigured,
            ],
            'items' => $items,
        ];
    }

    /**
     * Auto-fix file permissions and create required storage structures
     *
     * @return array{fixed: array<string>, failed: array<string>, message: string}
     */
    public function autoFixRequirements(): array
    {
        $fixed = [];
        $failed = [];

        $storageDirs = [
            storage_path('framework/views'),
            storage_path('framework/cache'),
            storage_path('framework/cache/data'),
            storage_path('framework/sessions'),
            storage_path('app/public'),
            storage_path('app/backups'),
            storage_path('logs'),
            app()->bootstrapPath('cache'),
        ];

        foreach ($storageDirs as $dir) {
            try {
                if (! file_exists($dir)) {
                    @mkdir($dir, 0775, true);
                }
                if (file_exists($dir)) {
                    @chmod($dir, 0775);
                    if (is_writable($dir)) {
                        $fixed[] = basename($dir).' (Permissions OK)';
                    } else {
                        $failed[] = basename($dir).' (Still not writable)';
                    }
                } else {
                    $failed[] = basename($dir).' (Could not create directory)';
                }
            } catch (\Throwable $e) {
                $failed[] = basename($dir).': '.$e->getMessage();
            }
        }

        // Connect storage symlink
        try {
            Artisan::call('storage:link');
            $fixed[] = 'public/storage symlink connected';
        } catch (\Throwable $e) {
            $failed[] = 'storage:link: '.$e->getMessage();
        }

        return [
            'fixed' => $fixed,
            'failed' => $failed,
            'message' => 'Auto-fix procedure completed. '.count($fixed).' actions succeeded, '.count($failed).' actions require manual intervention.',
        ];
    }

    /**
     * Parse php.ini size format (e.g. 512M, 2G, -1) to bytes
     */
    private function parseIniSize(string $size): int
    {
        $size = trim($size);
        if ($size === '-1') {
            return -1;
        }
        if ($size === '' || ! is_numeric(substr($size, 0, 1))) {
            return 0;
        }

        $unit = strtoupper(substr($size, -1));
        $val = (int) substr($size, 0, -1);

        switch ($unit) {
            case 'G':
                return $val * 1024 * 1024 * 1024;
            case 'M':
                return $val * 1024 * 1024;
            case 'K':
                return $val * 1024;
            default:
                return (int) $size;
        }
    }
}

