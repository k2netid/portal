<?php

namespace Modules\Core\System\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QueryPerformanceService
{
    /**
     * Enable query logging
     */
    public function enableQueryLog(): void
    {
        DB::enableQueryLog();
    }

    /**
     * Get query log
     *
     * @return array<int, array{query: string, bindings: array<mixed>, time: float}>
     */
    public function getQueryLog(): array
    {
        /** @var array<int, array{query: string, bindings: array<mixed>, time: float}> $log */
        $log = DB::getQueryLog();

        return $log;
    }

    /**
     * Analyze query performance
     *
     * @param  array<int, array{query: string, bindings: array<mixed>, time: float}>|null  $queries
     * @return array<string, mixed>
     */
    public function analyzeQueries(?array $queries = null): array
    {
        $queries ??= $this->getQueryLog();

        $analysis = [
            'total_queries' => count($queries),
            'total_time' => 0,
            'slow_queries' => [],
            'duplicate_queries' => [],
            'n_plus_one_candidates' => [],
        ];

        $queryHashes = [];
        $selectQueries = [];

        foreach ($queries as $query) {
            $time = $query['time'];
            $analysis['total_time'] += $time;

            // Detect slow queries (> 100ms)
            if ($time > 100) {
                $analysis['slow_queries'][] = [
                    'query' => $query['query'],
                    'bindings' => $query['bindings'],
                    'time' => $time,
                ];
            }

            // Detect duplicate queries
            $hash = md5($query['query']);
            if (isset($queryHashes[$hash])) {
                $queryHashes[$hash]['count']++;
                $queryHashes[$hash]['total_time'] += $time;
            } else {
                $queryHashes[$hash] = [
                    'query' => $query['query'],
                    'count' => 1,
                    'total_time' => $time,
                ];
            }

            // Track SELECT queries for N+1 detection
            if (str_starts_with(strtolower(trim($query['query'])), 'select')) {
                $selectQueries[] = $query;
            }
        }

        // Find duplicate queries
        foreach ($queryHashes as $data) {
            if ($data['count'] > 1) {
                $analysis['duplicate_queries'][] = $data;
            }
        }

        // Detect potential N+1 queries (same query pattern repeated)
        $patternCounts = [];
        foreach ($selectQueries as $query) {
            // Extract table name and basic pattern
            $pattern = $this->extractQueryPattern($query['query']);
            if (! isset($patternCounts[$pattern])) {
                $patternCounts[$pattern] = 0;
            }
            $patternCounts[$pattern]++;
        }

        foreach ($patternCounts as $pattern => $count) {
            if ($count > 5) {
                $analysis['n_plus_one_candidates'][] = [
                    'pattern' => $pattern,
                    'count' => $count,
                ];
            }
        }

        return $analysis;
    }

    /**
     * Extract query pattern for N+1 detection
     */
    protected function extractQueryPattern(string $query): string
    {
        // Remove bindings placeholders
        $patternRaw = preg_replace('/\?/', '*', $query);
        $pattern = is_string($patternRaw) ? $patternRaw : $query;

        // Extract table name
        if (preg_match('/from\s+`?(\w+)`?/i', $pattern, $matches)) {
            return $matches[1];
        }

        return substr($pattern, 0, 50);
    }

    /**
     * Log slow queries
     */
    public function logSlowQueries(float $threshold = 100): void
    {
        /** @var array<int, array{query: string, bindings: array<mixed>, time: float}> $queries */
        $queries = $this->getQueryLog();
        $slowQueries = [];

        foreach ($queries as $query) {
            $time = $query['time'];
            if ($time > $threshold) {
                $slowQueries[] = [
                    'query' => $query['query'],
                    'bindings' => $query['bindings'],
                    'time' => $time,
                    'timestamp' => now()->toDateTimeString(),
                ];
            }
        }

        if ($slowQueries !== []) {
            Log::warning('Slow queries detected', [
                'count' => count($slowQueries),
                'queries' => $slowQueries,
            ]);
        }
    }

    /**
     * Get performance statistics
     *
     * @return array<string, mixed>
     */
    public function getPerformanceStats(): array
    {
        /** @var array<int, array{query: string, bindings: array<mixed>, time: float}> $queries */
        $queries = $this->getQueryLog();

        if (empty($queries)) {
            return [
                'total_queries' => 0,
                'total_time' => 0.0,
                'average_time' => 0.0,
                'slow_queries_count' => 0,
            ];
        }

        /** @var non-empty-array<int, float> $times */
        $times = array_column($queries, 'time');
        $totalTime = array_sum($times);
        $slowQueries = array_filter($queries, fn (array $q): bool => $q['time'] > 100);

        return [
            'total_queries' => count($queries),
            'total_time' => round($totalTime, 2),
            'average_time' => round($totalTime / count($queries), 2),
            'slow_queries_count' => count($slowQueries),
            'max_time' => round(max($times), 2),
            'min_time' => round(min($times), 2),
        ];
    }

    /**
     * Cache performance metrics
     *
     * @param  array<string, mixed>  $metrics
     */
    public function cacheMetrics(string $key, array $metrics, int $ttl = 60): void
    {
        Cache::put("query_performance:{$key}", $metrics, now()->addMinutes($ttl));
    }

    /**
     * Get cached metrics
     *
     * @return array<string, mixed>|null
     */
    public function getCachedMetrics(string $key): ?array
    {
        /** @var array<string, mixed>|null $metrics */
        $metrics = Cache::get("query_performance:{$key}");

        return $metrics;
    }
}
