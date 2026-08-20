<?php

namespace Modules\Core\System\Http\Controllers\Console;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Modules\Core\System\Helpers\IpHelper;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Services\CacheWarmingService;
use Modules\Core\System\Services\SystemService;
use Modules\Core\System\Support\SqlLikeEscape;

class SystemController extends BaseApiController
{
    protected SystemService $systemService;

    public function __construct()
    {
        $this->systemService = new SystemService;
    }

    public function info(): JsonResponse
    {
        return $this->success($this->systemService->getSystemInfo(), 'System information retrieved successfully');
    }

    public function requirements(): JsonResponse
    {
        return $this->success($this->systemService->getRequirements(), 'System requirements retrieved successfully');
    }

    public function autoFixRequirements(): JsonResponse
    {
        $res = $this->systemService->autoFixRequirements();

        return $this->success($res, $res['message']);
    }

    public function health(): JsonResponse
    {
        $health = $this->systemService->getSystemHealth();

        $health['php'] = ['status' => 'ok', 'message' => 'PHP '.PHP_VERSION];
        $health['laravel'] = ['status' => 'ok', 'message' => 'Laravel '.app()->version()];

        try {
            Cache::put('health_check_queue', 'test', 10);
            $health['queue'] = ['status' => 'ok', 'message' => 'Queue connection working'];
        } catch (\Exception) {
            $health['queue'] = ['status' => 'error', 'message' => 'Queue connection failed'];
        }

        return $this->success($health, 'System health check completed');
    }

    public function statistics(): JsonResponse
    {
        return $this->success($this->systemService->getStatistics(), 'System statistics retrieved successfully');
    }

    public function cache(): JsonResponse
    {
        return $this->success([
            'driver' => config('cache.default'),
            'size' => $this->systemService->getCacheSize(),
        ], 'Cache information retrieved successfully');
    }

    public function cacheStatus(): JsonResponse
    {
        $status = $this->systemService->getCacheStatus();

        return $this->success($status, 'Cache status retrieved successfully');
    }

    public function clearCache(): JsonResponse
    {
        \Artisan::call('cache:clear');
        \Artisan::call('config:clear');
        \Artisan::call('route:clear');
        \Artisan::call('view:clear');

        return $this->success(null, 'All caches cleared successfully');
    }

    /**
     * Warm up application cache
     */
    public function warmCache(Request $request): JsonResponse
    {
        try {
            $warmingService = new CacheWarmingService;
            $typeInput = $request->input('type');
            $type = is_string($typeInput) ? $typeInput : null;
            $limitInput = $request->input('limit', 50);
            $limit = is_numeric($limitInput) ? (int) $limitInput : 50;

            if ($type) {
                $count = $warmingService->warmByType($type, $limit);

                return $this->success([
                    'type' => $type,
                    'items_cached' => $count,
                ], "Cache warmed for {$type}: {$count} items");
            } else {
                $results = $warmingService->warmAll();
                $total = array_sum($results);

                return $this->success([
                    'results' => $results,
                    'total_items' => $total,
                ], "Cache warming completed: {$total} total items");
            }
        } catch (\Exception $e) {
            Log::error('Cache warming failed: '.$e->getMessage());

            return $this->error('Failed to warm cache: '.$e->getMessage(), 500, [], 'CACHE_WARMING_FAILED');
        }
    }

    /**
     * Get cache warming statistics
     */
    public function cacheWarmingStats(): JsonResponse
    {
        try {
            $warmingService = new CacheWarmingService;
            $stats = $warmingService->getStatistics();

            return $this->success($stats, 'Cache warming statistics retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to get cache warming stats: '.$e->getMessage());

            return $this->error('Failed to get cache warming statistics', 500, [], 'STATS_ERROR');
        }
    }

    public function systemHealth(): JsonResponse
    {
        $health = $this->systemService->getSystemHealth();

        return $this->success($health, 'System health retrieved successfully');
    }

    // Protected methods removed as they are now handled by SystemService
    // formatBytes moved to private helper if widely used, or just kept in Service

    /**
     * Clear rate limit for login attempts
     */
    public function clearRateLimit(Request $request): JsonResponse
    {
        try {
            $ipInput = $request->input('ip', IpHelper::getClientIp($request));
            $ip = is_string($ipInput) ? $ipInput : '';
            $emailInput = $request->input('email');
            $email = is_string($emailInput) ? $emailInput : null;

            $cleared = [];

            // Clear throttle rate limit for IP (try multiple key formats)
            $throttleKeys = [
                "throttle:5,1:{$ip}",
                "throttle:10,1:{$ip}",
                "throttle:20,1:{$ip}",
                "throttle:60,1:{$ip}",
                "throttle:120,1:{$ip}",
                "throttle:300,1:{$ip}",
                "throttle:30,1:{$ip}",
                "throttle:10,1:{$ip}",
            ];

            foreach ($throttleKeys as $throttleKey) {
                RateLimiter::clear($throttleKey);
                Cache::forget($throttleKey);
            }

            // Named limiters from AppServiceProvider (ThrottleRequests hashes keys as md5(name . limitKey))
            $namedLimiterKeys = [
                md5('loginlogin-ip|'.$ip),
                md5('two-factor-verify2fa-verify|'.$ip),
            ];
            if (is_string($email) && $email !== '') {
                $namedLimiterKeys[] = md5('loginlogin-email|'.sha1(strtolower(trim($email))).'|'.$ip);
            }

            $userIdRaw = $request->input('user_id');
            if (is_numeric($userIdRaw)) {
                $uid = (string) $userIdRaw;
                $namedLimiterKeys[] = md5('manage-publishingmanage-publishing|u:'.$uid);
                $namedLimiterKeys[] = md5('media-uploadmedia-upload|u:'.$uid);
                $namedLimiterKeys[] = md5('media-upload-multiplemedia-upload-multi|u:'.$uid);
            }

            foreach ($namedLimiterKeys as $namedKey) {
                RateLimiter::clear($namedKey);
                Cache::forget($namedKey);
            }

            $cleared[] = "Rate limit for IP: {$ip}";

            // Clear security service related caches
            Cache::forget("failed_login_attempts_{$ip}");
            Cache::forget("blocked_ip_{$ip}");

            // Clear account lockout if email provided
            if ($email) {
                Cache::forget("account_locked_{$email}");
                Cache::forget("failed_login_attempts_email_{$email}");
                $cleared[] = "Account lockout for email: {$email}";
            }

            // If using database cache, also clear from cache table
            if (config('cache.default') === 'database') {
                try {
                    $keysToDelete = [];
                    $keysToDelete[] = "throttle:5,1:{$ip}";
                    $keysToDelete[] = "throttle:10,1:{$ip}";
                    $keysToDelete[] = "throttle:60,1:{$ip}";
                    $keysToDelete[] = "throttle:120,1:{$ip}";
                    $keysToDelete[] = "failed_login_attempts_{$ip}";
                    $keysToDelete[] = "blocked_ip_{$ip}";

                    if ($email) {
                        $keysToDelete[] = "account_locked_{$email}";
                        $keysToDelete[] = "failed_login_attempts_email_{$email}";
                    }

                    foreach ($keysToDelete as $key) {
                        $pat = SqlLikeEscape::contains($key);
                        $driver = DB::getDriverName();
                        if (in_array($driver, ['mysql', 'mariadb'], true)) {
                            DB::table('cache')->whereRaw("`key` LIKE ? ESCAPE '\\'", [$pat])->delete();
                        } else {
                            DB::table('cache')->whereRaw("\"key\" LIKE ? ESCAPE '\\\"", [$pat])->delete();
                        }
                    }

                    $cleared[] = 'Database cache entries cleared';
                } catch (\Exception $e) {
                    Log::warning('Failed to clear database cache: '.$e->getMessage());
                }
            }

            return $this->success([
                'cleared' => $cleared,
                'ip' => $ip,
                'email' => $email,
            ], 'Rate limit cleared successfully');
        } catch (\Exception $e) {
            Log::error('Failed to clear rate limit: '.$e->getMessage());

            return $this->error('Failed to clear rate limit: '.$e->getMessage(), 500);
        }
    }
}
