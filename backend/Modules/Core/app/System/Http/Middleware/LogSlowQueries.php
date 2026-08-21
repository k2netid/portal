<?php

namespace Modules\Core\System\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Core\System\Services\QueryPerformanceService;
use Symfony\Component\HttpFoundation\Response;

class LogSlowQueries
{
    protected float $threshold;

    public function __construct()
    {
        $threshold = config('database.slow_query_threshold', 100);
        $this->threshold = is_numeric($threshold) ? floatval($threshold) : 100.0;
    }

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only log in non-production or when explicitly enabled
        if (config('app.debug') || config('database.log_slow_queries', false)) {
            DB::enableQueryLog();
        }

        $startTime = microtime(true);

        $response = $next($request);

        if (config('app.debug') || config('database.log_slow_queries', false)) {
            $queries = DB::getQueryLog();
            $totalTime = (microtime(true) - $startTime) * 1000; // Convert to milliseconds

            // Log if total query time exceeds threshold
            if ($totalTime > $this->threshold) {
                $slowQueries = array_filter($queries, fn (array $query) => ($query['time'] ?? 0) > $this->threshold);

                if ($slowQueries !== []) {
                    // Log to file
                    Log::channel('slow-queries')->warning('Slow queries detected', [
                        'url' => $request->fullUrl(),
                        'method' => $request->method(),
                        'total_queries' => count($queries),
                        'total_time_ms' => round($totalTime, 2),
                        'slow_queries' => array_map(fn (array $query) => [
                            'query' => $query['query'],
                            'time' => $query['time'] ?? 0,
                            'bindings' => $query['bindings'],
                        ], $slowQueries),
                    ]);

                    // Store in database for analytics if analytics module is available
                    $slowQueryClass = 'Modules\\Intelligence\\Analytics\\Models\\SlowQuery';
                    if (config('database.store_slow_queries', false) && class_exists($slowQueryClass)) {
                        foreach ($slowQueries as $query) {
                            $slowQueryClass::create([
                                'sql' => $query['query'],
                                'time' => (float) ($query['time'] ?? 0),
                                'connection' => config('database.default'),
                                'url' => $request->path(),
                            ]);
                        }
                    }
                }
            }

            // Cache performance metrics for dashboard
            if ($request->is('api/*')) {
                $performanceService = app(QueryPerformanceService::class);
                $key = 'request_'.md5($request->path().$request->getQueryString());
                $performanceService->cacheMetrics($key, [
                    'path' => $request->path(),
                    'method' => $request->method(),
                    'total_queries' => count($queries),
                    'total_time_ms' => round($totalTime, 2),
                    'timestamp' => now()->toDateTimeString(),
                ], 5); // Cache for 5 minutes
            }
        }

        return $response;
    }
}
