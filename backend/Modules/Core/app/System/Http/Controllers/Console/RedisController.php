<?php

namespace Modules\Core\System\Http\Controllers\Console;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Redis\Connectors\PhpRedisConnector;
use Illuminate\Redis\Connectors\PredisConnector;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Models\RedisSetting;

class RedisController extends BaseApiController
{
    /**
     * Get all Redis settings.
     */
    public function index(): JsonResponse
    {
        $settings = RedisSetting::orderBy('group')->orderBy('key')->get();

        $grouped = $settings->groupBy('group')->map(function (Collection $items) {
            /** @var Collection<int, RedisSetting> $items */
            return $items->map(fn (RedisSetting $item) => [
                'id' => $item->id,
                'key' => $item->key,
                'value' => $this->presentSettingValue($item),
                'type' => $item->type,
                'description' => $item->description,
                'is_encrypted' => $item->is_encrypted,
            ])->values()->all();
        })->toArray();

        return $this->success($grouped, 'Redis settings retrieved successfully');
    }

    private function presentSettingValue(RedisSetting $item): mixed
    {
        if ($item->is_encrypted || $this->isSensitiveKey($item->key)) {
            return $item->value;
        }

        return $item->value;
    }

    private function isSensitiveKey(string $key): bool
    {
        return (bool) preg_match('/(password|secret|token|api[_-]?key)/i', $key);
    }

    /**
     * Update Redis settings.
     */
    public function update(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'nullable',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $settings = $request->input('settings');
        if (is_array($settings)) {
            foreach ($settings as $settingData) {
                if (is_array($settingData) && isset($settingData['key'])) {
                    $key = is_string($settingData['key']) ? $settingData['key'] : '';
                    if ($key !== '' && $key !== '0') {
                        RedisSetting::setValue($key, $settingData['value'] ?? null);
                    }
                }
            }
        }

        // Clear config cache to apply new settings
        Artisan::call('config:clear');

        return $this->success(null, 'Redis settings updated successfully');
    }

    /**
     * Test Redis connection.
     */
    public function testConnection(Request $request): JsonResponse
    {
        try {
            $start = microtime(true);

            $host = $request->input('host', '127.0.0.1');
            $portRaw = $request->input('port', 6379);
            $port = is_numeric($portRaw) ? (int) $portRaw : 6379;
            $password = $request->input('password');
            $databaseRaw = $request->input('database', 0);
            $database = is_numeric($databaseRaw) ? (int) $databaseRaw : 0;
            $client = config('database.redis.client', 'phpredis');

            $config = [
                'host' => $host,
                'port' => $port,
                'password' => $password,
                'database' => $database,
                'timeout' => 2.0,
                'persistent' => false,
            ];

            // Use direct connector to bypass Laravel's connection manager constraints for testing
            $connector = ($client === 'phpredis')
                ? new PhpRedisConnector
                : new PredisConnector;

            try {
                // For phpredis, we might need to handle options differently, but empty array is usually fine
                $redis = $connector->connect($config, []);

                // Explicitly check connection by sending a PING
                $pong = $redis->ping();

                // If ping() doesn't throw but returns something else
                if (! $pong && $client === 'phpredis') {
                    return $this->error('Redis server reachable but did not respond to PING. Check server status.', 500, [], 'REDIS_NO_PONG');
                }
            } catch (\Exception $e) {
                $msg = $e->getMessage();

                // Specific error mapping for better UX
                if (str_contains($msg, 'NOAUTH') || str_contains($msg, 'Authentication required') || str_contains($msg, 'invalid password')) {
                    return $this->error('Redis Authentication Failed: The password provided is incorrect.', 401, [
                        'field' => 'password',
                        'hint' => 'Check your Redis password setting.',
                    ], 'REDIS_AUTH_FAILED');
                }

                if (str_contains($msg, 'Connection refused') || str_contains($msg, 'getaddrinfo failed')) {
                    $sHost = is_scalar($host) ? (string) $host : '127.0.0.1';
                    $sPort = (string) $port;

                    return $this->error('Redis Connection Refused: Could not reach the server at '.$sHost.':'.$sPort.'.', 500, [
                        'field' => 'host',
                        'hint' => 'Verify the host address and port. Ensure Redis is running and firewall allows connections.',
                    ], 'REDIS_CONN_REFUSED');
                }

                if (str_contains($msg, 'timed out')) {
                    return $this->error('Redis Connection Timeout: The server took too long to respond.', 500, [
                        'hint' => 'Check network latency or if the Redis server is overloaded.',
                    ], 'REDIS_TIMEOUT');
                }

                return $this->error('Redis connection failed: '.$msg, 500, [], 'REDIS_CONN_FAILED');
            }

            $duration = round((microtime(true) - $start) * 1000, 2);

            return $this->success([
                'connected' => true,
                'response_time' => $duration.'ms',
                'message' => 'Redis connection successful',
            ], 'Connection test passed');

        } catch (\Exception $e) {
            return $this->error('Redis test utility error: '.$e->getMessage(), 500, [], 'REDIS_TEST_UTILITY_ERROR');
        }
    }

    /**
     * Get Redis server info.
     */
    public function info(): JsonResponse
    {
        try {
            $redis = Redis::connection();

            // Check if Redis requires authentication
            try {
                $redis->ping();
            } catch (\Exception $e) {
                if (str_contains($e->getMessage(), 'NOAUTH') || str_contains($e->getMessage(), 'Authentication required')) {
                    return $this->error('Redis authentication required. Please configure REDIS_PASSWORD in your .env file.', 401, [], 'REDIS_AUTH_REQUIRED');
                }
                throw $e;
            }

            $info = $redis->info();

            // Get some key metrics (Redis returns flat array)
            $stats = [
                'version' => $info['redis_version'] ?? 'Unknown',
                'uptime_days' => isset($info['uptime_in_days']) ? $info['uptime_in_days'].' days' : 'Unknown',
                'connected_clients' => $info['connected_clients'] ?? 0,
                'used_memory' => $info['used_memory_human'] ?? 'Unknown',
                'total_keys' => $this->getTotalKeys(),
                'hits' => $info['keyspace_hits'] ?? 0,
                'misses' => $info['keyspace_misses'] ?? 0,
                'hit_rate' => $this->calculateHitRate($info),
                'total_commands' => $info['total_commands_processed'] ?? 0,
                'operations_per_sec' => $info['instantaneous_ops_per_sec'] ?? 0,
            ];

            return $this->success($stats, 'Redis info retrieved successfully');
        } catch (\Exception $e) {
            $message = $e->getMessage();
            if (str_contains($message, 'NOAUTH') || str_contains($message, 'Authentication required')) {
                return $this->error('Redis authentication required. Please configure REDIS_PASSWORD in your .env file.', 401, [], 'REDIS_AUTH_REQUIRED');
            }

            return $this->error('Failed to retrieve Redis info: '.$message, 500);
        }
    }

    /**
     * Flush Redis cache.
     */
    public function flushCache(Request $request): JsonResponse
    {
        try {
            $type = $request->input('type', 'all'); // all, cache, config, route, view

            switch ($type) {
                case 'cache':
                    Artisan::call('cache:clear');
                    $this->flushRedisDatabase('cache');
                    break;
                case 'config':
                    Artisan::call('config:clear');
                    break;
                case 'route':
                    Artisan::call('route:clear');
                    break;
                case 'view':
                    Artisan::call('view:clear');
                    break;
                case 'all':
                default:
                    Artisan::call('cache:clear');
                    Artisan::call('config:clear');
                    Artisan::call('route:clear');
                    Artisan::call('view:clear');
                    $this->flushRedisDatabase('default');
                    $this->flushRedisDatabase('cache');
                    break;
            }

            $defaultKeys = $this->getConnectionKeys('default');
            $cacheKeys = $this->getConnectionKeys('cache');

            return $this->success([
                'default_keys' => $defaultKeys,
                'cache_keys' => $cacheKeys,
                'total_keys' => $defaultKeys + $cacheKeys,
            ], 'Cache cleared successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to flush cache: '.$e->getMessage(), 500);
        }
    }

    /**
     * Get cache statistics.
     */
    public function cacheStats(): JsonResponse
    {
        try {
            $redis = Redis::connection('cache');

            // Check if Redis requires authentication
            try {
                $redis->ping();
            } catch (\Exception $e) {
                if (str_contains($e->getMessage(), 'NOAUTH') || str_contains($e->getMessage(), 'Authentication required')) {
                    return $this->error('Redis authentication required. Please configure REDIS_PASSWORD in your .env file.', 401, [], 'REDIS_AUTH_REQUIRED');
                }
                throw $e;
            }

            $prefixRaw = config('database.redis.options.prefix');
            $prefix = is_string($prefixRaw) ? $prefixRaw : null;
            $totalKeys = $this->getDatabaseSize($redis);

            // Scan and build live Top Keys by Size
            $keysRaw = [];
            try {
                $keysRaw = $redis->keys('*');
            } catch (\Exception) {
                // Keep keysRaw empty if failed
            }

            $topKeys = [];
            if (is_array($keysRaw) && count($keysRaw) > 0) {
                $tempKeys = [];
                foreach ($keysRaw as $keyName) {
                    $keyNameStr = is_string($keyName) ? $keyName : '';
                    if (empty($keyNameStr)) {
                        continue;
                    }

                    // Query exact MEMORY USAGE in bytes
                    $bytes = 0;
                    try {
                        $client = $redis->client();
                        $usage = null;
                        if ($client instanceof \Redis) {
                            $usage = $client->rawCommand('MEMORY', 'USAGE', $keyNameStr);
                        }
                        $bytes = is_numeric($usage) ? (int) $usage : 0;
                    } catch (\Exception) {
                        try {
                            $val = $redis->get($keyNameStr);
                            $bytes = is_string($val) ? strlen($val) : 0;
                        } catch (\Exception) {
                            $bytes = 0;
                        }
                    }

                    // Query TTL
                    $ttlSec = -1;
                    try {
                        $ttlSec = $redis->ttl($keyNameStr);
                        $ttlSec = is_numeric($ttlSec) ? (int) $ttlSec : -1;
                    } catch (\Exception) {
                        $ttlSec = -1;
                    }

                    // Remove prefix if present in keyNameStr
                    $cleanKey = $keyNameStr;
                    if (! empty($prefix)) {
                        while (str_starts_with($cleanKey, $prefix)) {
                            $cleanKey = substr($cleanKey, strlen($prefix));
                        }
                    }

                    $tempKeys[] = [
                        'key' => $cleanKey,
                        'size_bytes' => $bytes,
                        'ttl_sec' => $ttlSec,
                    ];
                }

                // Sort keys by size descending
                usort($tempKeys, function ($a, $b) {
                    return $b['size_bytes'] <=> $a['size_bytes'];
                });

                // Take top 10 keys
                $topKeysRaw = array_slice($tempKeys, 0, 10);

                // Format sizes and TTLs
                foreach ($topKeysRaw as $item) {
                    $formattedSize = '0 B';
                    $units = ['B', 'KB', 'MB', 'GB'];
                    $sizeVal = $item['size_bytes'];
                    $unit = 0;
                    while ($sizeVal >= 1024 && $unit < count($units) - 1) {
                        $sizeVal /= 1024;
                        $unit++;
                    }
                    $formattedSize = round($sizeVal, 2).' '.$units[$unit];

                    $formattedTtl = 'Persistent';
                    if ($item['ttl_sec'] === -2) {
                        $formattedTtl = 'Expired';
                    } elseif ($item['ttl_sec'] > 0) {
                        $secs = $item['ttl_sec'];
                        if ($secs >= 86400) {
                            $days = floor($secs / 86400);
                            $hours = floor(($secs % 86400) / 3600);
                            $formattedTtl = "{$days}d {$hours}h";
                        } elseif ($secs >= 3600) {
                            $hours = floor($secs / 3600);
                            $mins = floor(($secs % 3600) / 60);
                            $formattedTtl = "{$hours}h {$mins}m";
                        } elseif ($secs >= 60) {
                            $mins = floor($secs / 60);
                            $secRem = $secs % 60;
                            $formattedTtl = "{$mins}m {$secRem}s";
                        } else {
                            $formattedTtl = "{$secs}s";
                        }
                    }

                    $topKeys[] = [
                        'key' => $item['key'],
                        'size' => $formattedSize,
                        'ttl' => $formattedTtl,
                    ];
                }
            }

            $stats = [
                'total_keys' => $totalKeys,
                'cache_size' => 'Estimated via Redis INFO only',
                'expired_keys' => $this->getExpiredKeysCount($redis),
                'top_keys' => $topKeys,
                'key_prefix' => $prefix,
            ];

            return $this->success($stats, 'Cache statistics retrieved successfully');
        } catch (\Exception $e) {
            $message = $e->getMessage();
            if (str_contains($message, 'NOAUTH') || str_contains($message, 'Authentication required')) {
                return $this->error('Redis authentication required. Please configure REDIS_PASSWORD in your .env file.', 401, [], 'REDIS_AUTH_REQUIRED');
            }

            return $this->error('Failed to retrieve cache stats: '.$message, 500);
        }
    }

    /**
     * Helper: Get total keys count.
     * Helper: Get total keys count.
     */
    private function getTotalKeys(): int
    {
        $count = 0;
        // Try to count keys from both default and cache connections
        try {
            $count += $this->getDatabaseSize(Redis::connection('default'));
        } catch (\Exception $e) {
        }

        try {
            $count += $this->getDatabaseSize(Redis::connection('cache'));
        } catch (\Exception) {
        }

        return $count;
    }

    /**
     * Helper: Calculate hit rate.
     *
     * @param  array<string, mixed>  $info
     */
    private function calculateHitRate(array $info): string
    {
        $hitsRaw = $info['keyspace_hits'] ?? 0;
        $hits = is_numeric($hitsRaw) ? (int) $hitsRaw : 0;
        $missesRaw = $info['keyspace_misses'] ?? 0;
        $misses = is_numeric($missesRaw) ? (int) $missesRaw : 0;
        $total = $hits + $misses;

        if ($total === 0) {
            return '0%';
        }

        return round(($hits / $total) * 100, 2).'%';
    }

    /**
     * Helper: Get expired keys count.
     *
     * @param  Connection  $redis
     */
    private function getExpiredKeysCount($redis): int
    {
        try {
            $info = $redis->info();

            return isset($info['expired_keys']) ? (int) $info['expired_keys'] : 0;
        } catch (\Exception) {
            return 0;
        }
    }

    private function getDatabaseSize(Connection $redis): int
    {
        $size = $redis->dbsize();

        return is_numeric($size) ? (int) $size : 0;
    }

    private function flushRedisDatabase(string $connection): void
    {
        try {
            Redis::connection($connection)->flushdb();
        } catch (\Throwable) {
            // Continue without failing request; some deployments may not define both connections.
        }
    }

    private function getConnectionKeys(string $connection): int
    {
        try {
            return $this->getDatabaseSize(Redis::connection($connection));
        } catch (\Throwable) {
            return 0;
        }
    }

    /**
     * Warm up cache (optimize).
     */
    public function warmCache(): JsonResponse
    {
        try {
            $commands = [
                'config:cache',
                'route:cache',
                'view:cache',
            ];

            foreach ($commands as $cmd) {
                Artisan::call($cmd);
            }

            return $this->success(null, 'Cache warmed successfully');
        } catch (\Throwable $e) {
            return $this->error('Failed to warm cache: '.$e->getMessage(), 500);
        }
    }
}
