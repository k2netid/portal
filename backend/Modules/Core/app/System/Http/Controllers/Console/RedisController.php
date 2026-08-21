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
use Modules\Core\System\Models\Setting;

class RedisController extends BaseApiController
{
    /**
     * Get all Redis settings.
     */
    public function index(): JsonResponse
    {
        // Keep cache_enabled in sys_redis_settings synchronized with global performance settings
        try {
            $currentDriver = strtolower((string) Setting::get('cache_driver', 'file'));
            $currentEnabled = filter_var(Setting::get('enable_cache', true), FILTER_VALIDATE_BOOLEAN);
            $isRedisActive = $currentEnabled && in_array($currentDriver, ['redis', 'failover', 'redis_failover']);
            RedisSetting::setValue('cache_enabled', $isRedisActive ? 'true' : 'false');
        } catch (\Throwable) {
        }

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
                        $val = $settingData['value'] ?? null;
                        RedisSetting::setValue($key, $val);

                        // Synchronize cache_enabled with Global Performance Settings
                        if ($key === 'cache_enabled') {
                            $isCacheEnabled = filter_var($val, FILTER_VALIDATE_BOOLEAN);
                            if ($isCacheEnabled) {
                                Setting::set('enable_cache', '1', 'boolean', 'performance');
                                $currentDriver = strtolower((string) Setting::get('cache_driver', 'file'));
                                if (! in_array($currentDriver, ['redis', 'failover', 'redis_failover'])) {
                                    Setting::set('cache_driver', 'failover', 'string', 'performance');
                                }
                            } else {
                                $currentDriver = strtolower((string) Setting::get('cache_driver', 'file'));
                                if (in_array($currentDriver, ['redis', 'failover', 'redis_failover'])) {
                                    Setting::set('cache_driver', 'file', 'string', 'performance');
                                }
                            }
                        }

                        // Synchronize cache_prefix with Global Performance Settings
                        if ($key === 'cache_prefix' && is_string($val)) {
                            Setting::set('cache_prefix', $val, 'string', 'performance');
                        }
                    }
                }
            }
        }

        // Clear config cache to apply new settings
        Artisan::call('config:clear');

        return $this->success(null, 'Redis settings updated successfully');
    }

    /**
     * Sync Redis settings from environment (.env).
     */
    public function syncFromEnv(): JsonResponse
    {
        $envMap = [
            'redis_host' => env('REDIS_HOST', '127.0.0.1'),
            'redis_port' => env('REDIS_PORT', 6379),
            'redis_username' => env('REDIS_USERNAME', 'core_engine'),
            'redis_password' => env('REDIS_PASSWORD', ''),
            'redis_database' => env('REDIS_DB', 6),
            'redis_cache_database' => env('REDIS_CACHE_DB', 7),
            'cache_prefix' => env('CACHE_PREFIX', 'ja_core_engine_cache:'),
        ];

        foreach ($envMap as $key => $val) {
            RedisSetting::setValue($key, $val);
        }

        Artisan::call('config:clear');

        return $this->index();
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
            $username = $request->input('username');
            $password = $request->input('password');
            $databaseRaw = $request->input('database', 0);
            $database = is_numeric($databaseRaw) ? (int) $databaseRaw : 0;
            $client = config('database.redis.client', 'phpredis');

            $config = [
                'host' => $host,
                'port' => $port,
                'username' => $username,
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
                $redis = $connector->connect($config, []);

                // Explicitly check connection by sending a PING
                $pong = $redis->ping();

                if (! $pong && $client === 'phpredis') {
                    return $this->error('Redis server reachable but did not respond to PING. Check server status.', 500, [], 'REDIS_NO_PONG');
                }
            } catch (\Exception $e) {
                $msg = $e->getMessage();

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
     * Get Redis server info and rich diagnostic metrics.
     */
    public function info(): JsonResponse
    {
        try {
            $redis = Redis::connection();

            try {
                $redis->ping();
            } catch (\Exception $e) {
                if (str_contains($e->getMessage(), 'NOAUTH') || str_contains($e->getMessage(), 'Authentication required')) {
                    return $this->error('Redis authentication required. Please configure REDIS_PASSWORD in your .env file.', 401, [], 'REDIS_AUTH_REQUIRED');
                }
                throw $e;
            }

            $info = $redis->info();

            $stats = [
                'version' => $info['redis_version'] ?? 'Unknown',
                'uptime_days' => isset($info['uptime_in_days']) ? $info['uptime_in_days'].' days' : 'Unknown',
                'uptime_seconds' => $info['uptime_in_seconds'] ?? 0,
                'connected_clients' => $info['connected_clients'] ?? 0,
                'used_memory' => $info['used_memory_human'] ?? 'Unknown',
                'used_memory_peak' => $info['used_memory_peak_human'] ?? ($info['used_memory_human'] ?? 'Unknown'),
                'mem_fragmentation_ratio' => isset($info['mem_fragmentation_ratio']) ? round((float) $info['mem_fragmentation_ratio'], 2) : 1.0,
                'redis_mode' => $info['redis_mode'] ?? 'standalone',
                'role' => $info['role'] ?? 'master',
                'connected_slaves' => $info['connected_slaves'] ?? 0,
                'rdb_last_bgsave_status' => $info['rdb_last_bgsave_status'] ?? 'ok',
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
            $type = $request->input('type', 'all');

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
     * Get cache statistics and top keys using safe non-blocking SCAN.
     */
    public function cacheStats(): JsonResponse
    {
        try {
            $redis = Redis::connection('cache');

            try {
                $redis->ping();
            } catch (\Exception $e) {
                if (str_contains($e->getMessage(), 'NOAUTH') || str_contains($e->getMessage(), 'Authentication required')) {
                    return $this->error('Redis authentication required. Please configure REDIS_PASSWORD in your .env file.', 401, [], 'REDIS_AUTH_REQUIRED');
                }
                throw $e;
            }

            $prefix = $this->getConnectionPrefix($redis);
            $totalKeys = $this->getDatabaseSize($redis);

            // Safe Non-Blocking SCAN for keys
            $keysRaw = $this->scanKeys($redis, '*', 500);

            $topKeys = [];
            if (! empty($keysRaw)) {
                $tempKeys = [];
                foreach ($keysRaw as $keyNameStr) {
                    if (empty($keyNameStr)) {
                        continue;
                    }

                    $cleanKey = $this->stripPrefix($keyNameStr, $prefix);
                    $bytes = $this->getKeyMemoryUsage($redis, $cleanKey, $keyNameStr);
                    $ttlSec = $this->getKeyTtl($redis, $cleanKey, $keyNameStr);

                    $tempKeys[] = [
                        'key' => $cleanKey,
                        'raw_key' => $keyNameStr,
                        'size_bytes' => $bytes,
                        'ttl_sec' => $ttlSec,
                    ];
                }

                // Sort keys by size descending
                usort($tempKeys, fn ($a, $b) => $b['size_bytes'] <=> $a['size_bytes']);

                // Top 10 keys
                $topKeysRaw = array_slice($tempKeys, 0, 10);

                foreach ($topKeysRaw as $item) {
                    $topKeys[] = [
                        'key' => $item['key'],
                        'raw_key' => $item['raw_key'],
                        'size' => $this->formatBytes($item['size_bytes']),
                        'ttl' => $this->formatTtl($item['ttl_sec']),
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
     * Search Redis keys with pattern matching via non-blocking SCAN.
     */
    public function searchKeys(Request $request): JsonResponse
    {
        try {
            $connName = $request->input('connection', 'cache');
            $pattern = (string) $request->input('pattern', '*');
            $limit = min((int) $request->input('limit', 80), 150);

            if (empty($pattern)) {
                $pattern = '*';
            }

            $redis = Redis::connection($connName);
            $prefix = $this->getConnectionPrefix($redis);

            $keysScanned = $this->scanKeys($redis, $pattern, 300);

            $result = [];
            foreach (array_slice($keysScanned, 0, $limit) as $keyNameStr) {
                if (empty($keyNameStr)) {
                    continue;
                }

                $cleanKey = $this->stripPrefix($keyNameStr, $prefix);
                $typeRaw = $this->getKeyType($redis, $cleanKey, $keyNameStr);
                $type = $this->formatKeyType($typeRaw);
                $bytes = $this->getKeyMemoryUsage($redis, $cleanKey, $keyNameStr);
                $ttlSec = $this->getKeyTtl($redis, $cleanKey, $keyNameStr);

                $result[] = [
                    'key' => $cleanKey,
                    'raw_key' => $keyNameStr,
                    'type' => $type,
                    'size_bytes' => $bytes,
                    'size' => $this->formatBytes($bytes),
                    'ttl_sec' => $ttlSec,
                    'ttl' => $this->formatTtl($ttlSec),
                ];
            }

            return $this->success([
                'pattern' => $pattern,
                'connection' => $connName,
                'total_found' => count($keysScanned),
                'items' => $result,
            ], 'Keys retrieved successfully');
        } catch (\Throwable $e) {
            return $this->error('Failed to search keys: '.$e->getMessage(), 500);
        }
    }

    /**
     * Get detailed key inspection and value preview.
     */
    public function getKeyDetails(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|string',
            'connection' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        try {
            $connName = (string) $request->input('connection', 'cache');
            $rawInputKey = (string) $request->input('key');
            $redis = Redis::connection($connName);
            $prefix = $this->getConnectionPrefix($redis);

            $cleanKey = $this->stripPrefix($rawInputKey, $prefix);
            $typeRaw = $this->getKeyType($redis, $cleanKey, $rawInputKey);
            $type = $this->formatKeyType($typeRaw);
            $bytes = $this->getKeyMemoryUsage($redis, $cleanKey, $rawInputKey);
            $ttlSec = $this->getKeyTtl($redis, $cleanKey, $rawInputKey);
            $value = $this->readKeyValue($redis, $cleanKey, $rawInputKey, $type);

            return $this->success([
                'key' => $cleanKey,
                'raw_key' => $rawInputKey,
                'connection' => $connName,
                'type' => $type,
                'size_bytes' => $bytes,
                'size' => $this->formatBytes($bytes),
                'ttl_sec' => $ttlSec,
                'ttl' => $this->formatTtl($ttlSec),
                'value' => $value,
                'is_json' => is_array($value),
            ], 'Key details retrieved successfully');
        } catch (\Throwable $e) {
            return $this->error('Failed to get key details: '.$e->getMessage(), 500);
        }
    }

    /**
     * Delete a single or multiple Redis keys.
     */
    public function deleteKey(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'key' => 'nullable|string',
            'keys' => 'nullable|array',
            'connection' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        try {
            $connName = (string) $request->input('connection', 'cache');
            $redis = Redis::connection($connName);
            $prefix = $this->getConnectionPrefix($redis);

            $targetKeys = [];
            if ($request->filled('key')) {
                $targetKeys[] = (string) $request->input('key');
            }
            if ($request->filled('keys') && is_array($request->input('keys'))) {
                $targetKeys = array_merge($targetKeys, $request->input('keys'));
            }

            $targetKeys = array_unique(array_filter($targetKeys));
            if (empty($targetKeys)) {
                return $this->error('No keys specified for deletion', 422);
            }

            $deletedCount = 0;
            foreach ($targetKeys as $k) {
                try {
                    $clean = $this->stripPrefix($k, $prefix);
                    $delRes = (int) $redis->del($clean);
                    if ($delRes === 0) {
                        // Fallback: delete raw key directly
                        $client = $redis->client();
                        if ($client instanceof \Redis) {
                            $delRes = (int) $client->del($k);
                        }
                    }
                    $deletedCount += $delRes;
                } catch (\Throwable) {}
            }

            return $this->success([
                'deleted' => $deletedCount,
                'keys' => $targetKeys,
            ], "Successfully deleted {$deletedCount} key(s)");
        } catch (\Throwable $e) {
            return $this->error('Failed to delete key: '.$e->getMessage(), 500);
        }
    }

    private function formatKeyType(mixed $type): string
    {
        if (is_string($type)) {
            return strtolower($type);
        }

        return match ((int) $type) {
            1 => 'string',
            2 => 'set',
            3 => 'list',
            4 => 'zset',
            5 => 'hash',
            6 => 'stream',
            default => 'none',
        };
    }

    private function getConnectionPrefix(Connection $redis): string
    {
        try {
            $client = $redis->client();
            if ($client instanceof \Redis) {
                $p = $client->getOption(\Redis::OPT_PREFIX);
                if (is_string($p)) {
                    return $p;
                }
            }
        } catch (\Throwable) {}

        $prefixRaw = config('database.redis.options.prefix');

        return is_string($prefixRaw) ? $prefixRaw : '';
    }

    private function stripPrefix(string $key, string $prefix): string
    {
        if (! empty($prefix) && str_starts_with($key, $prefix)) {
            return substr($key, strlen($prefix));
        }

        return $key;
    }

    private function getKeyType(Connection $redis, string $cleanKey, string $rawKey): mixed
    {
        try {
            $client = $redis->client();
            if ($client instanceof \Redis) {
                $type = $client->rawCommand('TYPE', $rawKey);
                if ($type !== 0 && $type !== 'none' && $type !== false && $type !== null) {
                    return $type;
                }
            }
        } catch (\Throwable) {}

        try {
            $type = $redis->type($cleanKey);
            if ($type !== 0 && $type !== 'none' && $type !== false && $type !== null) {
                return $type;
            }
        } catch (\Throwable) {}

        return 0;
    }

    private function getKeyTtl(Connection $redis, string $cleanKey, string $rawKey): int
    {
        try {
            $client = $redis->client();
            if ($client instanceof \Redis) {
                $ttl = $client->rawCommand('TTL', $rawKey);
                if (is_numeric($ttl)) {
                    return (int) $ttl;
                }
            }
        } catch (\Throwable) {}

        try {
            $ttl = $redis->ttl($cleanKey);
            if (is_numeric($ttl)) {
                return (int) $ttl;
            }
        } catch (\Throwable) {}

        return -1;
    }

    private function getKeyMemoryUsage(Connection $redis, string $cleanKey, string $rawKey): int
    {
        try {
            $client = $redis->client();
            if ($client instanceof \Redis) {
                $usage = $client->rawCommand('MEMORY', 'USAGE', $rawKey);
                if (is_numeric($usage)) {
                    return (int) $usage;
                }
            }
        } catch (\Throwable) {}

        try {
            $val = $redis->get($cleanKey);
            if (is_string($val)) {
                return strlen($val);
            }
        } catch (\Throwable) {}

        return 0;
    }

    private function readKeyValue(Connection $redis, string $cleanKey, string $rawKey, string $type): mixed
    {
        try {
            $client = $redis->client();

            switch ($type) {
                case 'string':
                    $val = null;
                    if ($client instanceof \Redis) {
                        try {
                            $val = $client->rawCommand('GET', $rawKey);
                        } catch (\Throwable) {}
                    }
                    if ($val === null || $val === false) {
                        $val = $redis->get($cleanKey);
                    }

                    if (is_string($val)) {
                        if ($this->isSerialized($val)) {
                            try {
                                $unserialized = @unserialize($val);
                                if ($unserialized !== false || $val === 'b:0;') {
                                    return $unserialized;
                                }
                            } catch (\Throwable) {}
                        }
                        $json = json_decode($val, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            return $json;
                        }
                    }

                    return $val;

                case 'hash':
                    $hash = [];
                    if ($client instanceof \Redis) {
                        try {
                            $rawHash = $client->rawCommand('HGETALL', $rawKey);
                            if (is_array($rawHash)) {
                                // PhpRedis rawCommand HGETALL returns alternating key/value flat array
                                if (! empty($rawHash) && array_is_list($rawHash)) {
                                    for ($i = 0; $i < count($rawHash); $i += 2) {
                                        if (isset($rawHash[$i + 1])) {
                                            $hash[$rawHash[$i]] = $rawHash[$i + 1];
                                        }
                                    }
                                } else {
                                    $hash = $rawHash;
                                }
                            }
                        } catch (\Throwable) {}
                    }
                    if (empty($hash)) {
                        $hash = $redis->hgetall($cleanKey);
                    }

                    return $hash;

                case 'list':
                    if ($client instanceof \Redis) {
                        try {
                            return $client->rawCommand('LRANGE', $rawKey, 0, 99);
                        } catch (\Throwable) {}
                    }

                    return $redis->lrange($cleanKey, 0, 99);

                case 'set':
                    if ($client instanceof \Redis) {
                        try {
                            return $client->rawCommand('SMEMBERS', $rawKey);
                        } catch (\Throwable) {}
                    }

                    return $redis->smembers($cleanKey);

                case 'zset':
                    if ($client instanceof \Redis) {
                        try {
                            return $client->rawCommand('ZRANGE', $rawKey, 0, 99, 'WITHSCORES');
                        } catch (\Throwable) {}
                    }

                    return $redis->zrange($cleanKey, 0, 99, ['WITHSCORES' => true]);

                default:
                    if ($client instanceof \Redis) {
                        try {
                            return $client->rawCommand('GET', $rawKey);
                        } catch (\Throwable) {}
                    }

                    return $redis->get($cleanKey);
            }
        } catch (\Throwable $e) {
            return '[Error reading value: '.$e->getMessage().']';
        }
    }

    private function isSerialized(string $val): bool
    {
        return (bool) preg_match('/^(?:i:\d+|b:[01]|s:\d+:".*"|a:\d+:\{.*\}|O:\d+:".*":\d+:\{.*\});?$/s', $val);
    }

    /**
     * Helper: Scan keys using cursor-based non-blocking SCAN.
     *
     * @return array<int, string>
     */
    private function scanKeys(Connection $redis, string $pattern = '*', int $maxLimit = 500): array
    {
        $cursor = null;
        $keys = [];
        $iterations = 0;
        $maxIterations = 50;

        do {
            $iterations++;
            $result = $redis->scan($cursor, ['match' => $pattern, 'count' => 100]);

            if ($result === false) {
                break;
            }

            if (is_array($result)) {
                if (isset($result[0]) && is_array($result[1])) {
                    $cursor = $result[0];
                    foreach ($result[1] as $k) {
                        if (is_string($k)) {
                            $keys[] = $k;
                        }
                    }
                } else {
                    foreach ($result as $k) {
                        if (is_string($k)) {
                            $keys[] = $k;
                        }
                    }
                }
            }

            $keys = array_unique($keys);
            if (count($keys) >= $maxLimit || $iterations >= $maxIterations) {
                break;
            }
        } while ($cursor && $cursor !== '0');

        return array_values($keys);
    }

    /**
     * Helper: Format bytes into human readable string.
     */
    private function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }
        $units = ['B', 'KB', 'MB', 'GB'];
        $val = (float) $bytes;
        $unit = 0;
        while ($val >= 1024 && $unit < count($units) - 1) {
            $val /= 1024;
            $unit++;
        }

        return round($val, 2).' '.$units[$unit];
    }

    /**
     * Helper: Format TTL into readable string.
     */
    private function formatTtl(int $ttlSec): string
    {
        if ($ttlSec === -2) {
            return 'Expired';
        }
        if ($ttlSec <= -1) {
            return 'Persistent';
        }

        if ($ttlSec >= 86400) {
            $days = floor($ttlSec / 86400);
            $hours = floor(($ttlSec % 86400) / 3600);

            return "{$days}d {$hours}h";
        }
        if ($ttlSec >= 3600) {
            $hours = floor($ttlSec / 3600);
            $mins = floor(($ttlSec % 3600) / 60);

            return "{$hours}h {$mins}m";
        }
        if ($ttlSec >= 60) {
            $mins = floor($ttlSec / 60);
            $secRem = $ttlSec % 60;

            return "{$mins}m {$secRem}s";
        }

        return "{$ttlSec}s";
    }

    /**
     * Helper: Get total keys count across default and cache databases.
     */
    private function getTotalKeys(): int
    {
        $count = 0;
        try {
            $count += $this->getDatabaseSize(Redis::connection('default'));
        } catch (\Throwable) {}

        try {
            $count += $this->getDatabaseSize(Redis::connection('cache'));
        } catch (\Throwable) {}

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
        } catch (\Throwable) {
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
        } catch (\Throwable) {}
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
