<?php

namespace Modules\Analytics\Http\Controllers;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Modules\Analytics\Models\AnalyticsEvent;
use Modules\Analytics\Models\AnalyticsSession;
use Modules\Analytics\Models\AnalyticsVisit;
use Modules\Analytics\Services\AnalyticsService;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Models\Setting;
use Modules\Publishing\Contracts\PublishedContentAnalyticsPortInterface;

class AnalyticsController extends BaseApiController
{
    public function __construct(
        private readonly PublishedContentAnalyticsPortInterface $publishedContentAnalytics,
    ) {
        $this->middleware('auth:sanctum')->except(['trackVisit', 'trackEvent', 'trackBatch']);
        $this->middleware('permission:view analytics')->except(['trackVisit', 'trackEvent', 'trackBatch']);
        $this->middleware('permission:manage settings')->only(['cleanup', 'purgeAll']);
    }

    /**
     * Get properly formatted date range for queries
     * Ensures end date includes the entire day (23:59:59)
     *
     * @return array<int, string>
     */
    protected function getDateRange(Request $request): array
    {
        $dateFromRaw = $request->input('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateFrom = is_string($dateFromRaw) ? $dateFromRaw : now()->subDays(30)->format('Y-m-d');
        $dateToRaw = $request->input('date_to', now()->format('Y-m-d'));
        $dateTo = is_string($dateToRaw) ? $dateToRaw : now()->format('Y-m-d');

        return [
            $dateFrom.' 00:00:00',
            $dateTo.' 23:59:59',
        ];
    }

    /**
     * Get analytics overview.
     */
    public function overview(Request $request): JsonResponse
    {
        [$dateFrom, $dateTo] = $this->getDateRange($request);

        $stats = [
            'total_visits' => AnalyticsVisit::whereBetween('visited_at', [$dateFrom, $dateTo])->count(),
            'unique_visitors' => AnalyticsVisit::whereBetween('visited_at', [$dateFrom, $dateTo])
                ->distinct('ip_address')
                ->count('ip_address'),
            'total_sessions' => AnalyticsSession::whereBetween('started_at', [$dateFrom, $dateTo])->count(),
            'avg_session_duration' => (float) AnalyticsSession::whereBetween('started_at', [$dateFrom, $dateTo])
                ->whereNotNull('ended_at')
                ->avg('duration'),
            'bounce_rate' => $this->calculateBounceRate((string) $dateFrom, (string) $dateTo),
            'page_views' => AnalyticsVisit::whereBetween('visited_at', [$dateFrom, $dateTo])->count(),
        ];

        return $this->success($stats, 'Analytics overview retrieved successfully');
    }

    /**
     * Get analytics visits.
     */
    public function visits(Request $request): JsonResponse
    {
        [$dateFrom, $dateTo] = $this->getDateRange($request);
        $groupByRaw = $request->input('group_by', 'day'); // day, week, month
        $groupBy = is_string($groupByRaw) ? $groupByRaw : 'day';
        $driver = DB::getDriverName();

        $periodSelect = match ($groupBy) {
            'hour' => $driver === 'sqlite'
                ? "strftime('%Y-%m-%d %H:00:00', visited_at) as period, count(*) as visits"
                : "to_char(visited_at, 'YYYY-MM-DD HH24:00:00') as period, count(*) as visits",
            'week' => $driver === 'sqlite'
                ? "strftime('%Y-%W', visited_at) as period, count(*) as visits"
                : "to_char(visited_at, 'IYYYIW') as period, count(*) as visits",
            'month' => $driver === 'sqlite'
                ? "strftime('%Y-%m', visited_at) as period, count(*) as visits"
                : "to_char(visited_at, 'YYYY-MM') as period, count(*) as visits",
            default => $driver === 'sqlite'
                ? 'date(visited_at) as period, count(*) as visits'
                : 'visited_at::date as period, count(*) as visits',
        };

        $visits = AnalyticsVisit::whereBetween('visited_at', [$dateFrom, $dateTo])
            ->selectRaw($periodSelect)
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return $this->success($visits, 'Analytics visits retrieved successfully');
    }

    /**
     * Get top pages.
     */
    public function topPages(Request $request): JsonResponse
    {
        [$dateFrom, $dateTo] = $this->getDateRange($request);
        $limitRaw = $request->input('limit', 10);
        $limit = is_numeric($limitRaw) ? (int) $limitRaw : 10;

        $topPages = AnalyticsVisit::whereBetween('visited_at', [$dateFrom, $dateTo])
            ->select('url', DB::raw('count(*) as visits'))
            ->groupBy('url')
            ->orderByDesc('visits')
            ->limit($limit)
            ->get();

        return $this->success($topPages, 'Top pages retrieved successfully');
    }

    /**
     * Get top content data.
     */
    public function topContent(Request $request): JsonResponse
    {
        [$dateFrom, $dateTo] = $this->getDateRange($request);
        $limitRaw = $request->input('limit', 10);
        $limit = is_numeric($limitRaw) ? (int) $limitRaw : 10;

        // Optimized: Use content.views column instead of N+1 visits queries
        // For more accurate date-filtered stats, use subquery or pre-aggregated table
        $cacheEpochRaw = Cache::get('analytics_cache_epoch', 0);
        $cacheEpoch = is_numeric($cacheEpochRaw) ? (int) $cacheEpochRaw : 0;
        $cacheKey = 'analytics_top_content_'.$cacheEpoch.'_'.($dateFrom).'_'.($dateTo).'_'.($limit);
        $topContent = Cache::remember(
            $cacheKey,
            now()->addMinutes(30),
            function () use ($dateFrom, $dateTo, $limit) {
                // Get content URLs with most visits in date range
                $visitCounts = AnalyticsVisit::whereBetween('visited_at', [$dateFrom, $dateTo])
                    ->select('url', DB::raw('count(*) as visits_count'))
                    ->groupBy('url')
                    ->orderByDesc('visits_count')
                    ->limit($limit * 5) // Increased limit to ensure we find enough content slugs
                    ->get();

                // Extract potential slugs from URLs (basename of /path/to/slug)
                $slugs = $visitCounts->map(function ($visit): ?string {
                    $url = $visit->url;
                    $path = parse_url($url, PHP_URL_PATH);
                    if ($path) {
                        $slug = basename($path);
                        if ($slug && $slug !== 'admin' && $slug !== 'api') {
                            return $slug;
                        }
                    }

                    return null;
                })->filter()->unique();

                if ($slugs->isEmpty()) {
                    return collect();
                }

                /** @var list<string> $slugList */
                $slugList = array_values($slugs->values()->all());
                $rows = $this->publishedContentAnalytics->publishedRowsBySlugs($slugList);
                $slugToRow = collect($rows)->keyBy('slug');
                $results = [];
                foreach ($visitCounts as $visit) {
                    $url = $visit->url;
                    $path = parse_url($url, PHP_URL_PATH);
                    $slug = $path ? basename($path) : null;

                    if ($slug && $slugToRow->has($slug)) {
                        $row = $slugToRow->get($slug);
                        if ($row === null) {
                            continue;
                        }
                        $results[] = [
                            'id' => $row->id,
                            'title' => $row->title,
                            'slug' => $row->slug,
                            'type' => $row->type,
                            'author' => $row->authorName !== null ? ['name' => $row->authorName] : null,
                            'visits_count' => (int) $visit->visits_count,
                        ];
                    }

                    if (count($results) >= $limit) {
                        break;
                    }
                }

                // Sort by visits_count and take the final limit
                return collect($results)->sortByDesc('visits_count')->take($limit)->values();
            }
        );

        return $this->success($topContent, 'Top content retrieved successfully');
    }

    /**
     * Get device analytics.
     */
    public function devices(Request $request): JsonResponse
    {
        try {
            [$dateFrom, $dateTo] = $this->getDateRange($request);

            // Query from sessions table (normalized)
            $devices = AnalyticsSession::whereBetween('started_at', [$dateFrom, $dateTo])
                ->select('device_type', DB::raw('count(*) as count'))
                ->groupBy('device_type')
                ->get();

            return $this->success($devices, 'Device analytics retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Analytics devices error: '.$e->getMessage());

            return $this->success([], 'Device analytics retrieved successfully');
        }
    }

    /**
     * Get browser analytics.
     */
    public function browsers(Request $request): JsonResponse
    {
        try {
            [$dateFrom, $dateTo] = $this->getDateRange($request);

            // Query from sessions table (normalized)
            $browsers = AnalyticsSession::whereBetween('started_at', [$dateFrom, $dateTo])
                ->select('browser', DB::raw('count(*) as count'))
                ->groupBy('browser')
                ->orderByDesc('count')
                ->get();

            return $this->success($browsers, 'Browser analytics retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Analytics browsers error: '.$e->getMessage());

            return $this->success([], 'Browser analytics retrieved successfully');
        }
    }

    /**
     * Get country analytics.
     */
    public function countries(Request $request): JsonResponse
    {
        try {
            [$dateFrom, $dateTo] = $this->getDateRange($request);

            // Query from sessions table (normalized)
            $countries = AnalyticsSession::whereBetween('started_at', [$dateFrom, $dateTo])
                ->whereNotNull('country')
                ->where('country', '!=', '')
                ->select('country', DB::raw('count(*) as count'))
                ->groupBy('country')
                ->orderByDesc('count')
                ->get();

            return $this->success($countries, 'Country analytics retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Analytics countries error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            // Return empty array instead of error to prevent frontend issues
            return $this->success([], 'Country analytics retrieved successfully');
        }
    }

    /**
     * Get referrer analytics.
     */
    public function referrers(Request $request): JsonResponse
    {
        [$dateFrom, $dateTo] = $this->getDateRange($request);
        $limitRaw = $request->input('limit', 10);
        $limit = is_numeric($limitRaw) ? (int) $limitRaw : 10;

        $queryStr = 'referer';
        if (DB::getDriverName() === 'pgsql') {
            $queryStr = "regexp_replace(regexp_replace(lower(referer), '^https?://(www\.)?', ''), '/.*', '')";
        } elseif (DB::getDriverName() === 'mysql') {
            $queryStr = "regexp_replace(regexp_replace(lower(referer), '^https?://(www\.)?', ''), '/.*', '')";
        } else {
            // Fallback for SQLite or other drivers: just use referer as is or basic string ops
            $queryStr = 'referer';
        }

        $referrers = AnalyticsVisit::whereBetween('visited_at', [$dateFrom, $dateTo])
            ->whereNotNull('referer')
            ->where('referer', '!=', '')
            ->selectRaw("{$queryStr} as referer_host, count(*) as count")
            ->groupBy('referer_host')
            ->orderByDesc('count')
            ->limit($limit)
            ->get()
            ->map(function ($visit): array {
                /** @var AnalyticsVisit $visit */
                return [
                    'referer' => (string) $visit->referer_host,
                    'count' => (int) $visit->count,
                ];
            });

        return $this->success($referrers, 'Referrer analytics retrieved successfully');
    }

    /**
     * Get analytics events.
     */
    public function events(Request $request): JsonResponse
    {
        [$dateFrom, $dateTo] = $this->getDateRange($request);
        $eventTypeRaw = $request->input('event_type');
        $eventType = is_string($eventTypeRaw) ? $eventTypeRaw : null;

        $query = AnalyticsEvent::whereBetween('occurred_at', [$dateFrom, $dateTo]);

        if ($eventType) {
            $query->where('event_type', $eventType);
        }

        /** @var LengthAwarePaginator<int, AnalyticsEvent> $events */
        $events = $query->with(['user'])
            ->latest('occurred_at')
            ->paginate(50);

        return $this->paginated($events, 'Analytics events retrieved successfully');
    }

    /**
     * Get event statistics.
     */
    public function eventStats(Request $request): JsonResponse
    {
        [$dateFrom, $dateTo] = $this->getDateRange($request);

        $stats = AnalyticsEvent::whereBetween('occurred_at', [$dateFrom, $dateTo])
            ->select('event_type', 'event_name', DB::raw('count(*) as count'))
            ->groupBy('event_type', 'event_name')
            ->orderByDesc('count')
            ->get();

        return $this->success($stats, 'Event statistics retrieved successfully');
    }

    /**
     * Track a page visit
     */
    public function trackVisit(Request $request): JsonResponse
    {
        try {
            // Filter out bot/scanner traffic
            $userAgent = strtolower((string) $request->userAgent());
            $botPatterns = [
                'bot', 'crawl', 'spider', 'slurp', 'baidu', 'yandex',
                'semrush', 'ahrefs', 'mj12bot', 'dotbot', 'petalbot',
                'bytespider', 'gptbot', 'claudebot', 'ccbot', 'facebookexternalhit',
                'wget', 'curl', 'python-requests', 'go-http-client', 'scrapy',
                'headlesschrome', 'phantomjs', 'selenium',
            ];

            foreach ($botPatterns as $pattern) {
                if (str_contains($userAgent, $pattern)) {
                    return $this->success(null, 'Bot traffic ignored');
                }
            }

            // Filter out suspicious/non-page paths (scanners, probes, static assets)
            $pathInput = $request->input('path') ?? $request->input('url') ?? '';
            $path = strtolower(is_string($pathInput) ? $pathInput : '');
            $suspiciousPatterns = [
                '.php',           // PHP file probes (file.php, edit.php, wp-*.php)
                'wp-content',     // WordPress probes
                'wp-admin',       // WordPress probes
                'wp-includes',    // WordPress probes
                'wp-login',       // WordPress probes
                '.map',           // Source map requests
                'robots.txt',     // Crawler files
                'sitemap.xml',    // Crawler files
                '.env',           // Environment file probes
                '.git',           // Git probes
                'xmlrpc',         // XML-RPC probes
                'phpmyadmin',     // phpMyAdmin probes
                '/api/',          // API endpoint calls (not page visits)
            ];

            foreach ($suspiciousPatterns as $pattern) {
                if (str_contains($path, $pattern)) {
                    return $this->success(null, 'Non-page traffic ignored');
                }
            }

            $visit = AnalyticsVisit::trackVisit($request);

            return $this->success($visit, 'Visit tracked successfully');
        } catch (\Exception $e) {
            Log::error('Track visit error: '.$e->getMessage());

            return $this->error('Failed to track visit', 500);
        }
    }

    /**
     * Track a custom event
     */
    public function trackEvent(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'event_type' => 'required|string|max:50',
                'event_name' => 'required|string|max:255',
                'event_data' => 'nullable|array',
                'content_id' => 'nullable|string|exists:pub_contents,id',
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $eventDataRaw = $validated['event_data'] ?? [];
        $eventData = is_array($eventDataRaw) ? $eventDataRaw : [];
        $contentIdRaw = $validated['content_id'] ?? null;
        $contentId = is_scalar($contentIdRaw) ? (string) $contentIdRaw : null;

        $event = AnalyticsService::trackEvent(
            (string) $validated['event_type'],
            (string) $validated['event_name'],
            $eventData,
            $contentId
        );

        return $this->success($event, 'Event tracked successfully', 201);
    }

    /**
     * Track multiple events in batch
     */
    public function trackBatch(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'events' => 'required|array|min:1|max:100',
                'events.*.type' => 'required|string|max:50',
                'events.*.name' => 'required|string|max:255',
                'events.*.data' => 'nullable|array',
                'events.*.content_id' => 'nullable|string|exists:pub_contents,id',
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $eventsRaw = $validated['events'];
        /** @var array<int, mixed> $events */
        $events = is_array($eventsRaw) ? $eventsRaw : [];

        /** @var array<int, array{type?: string, name?: string, data?: array<string, mixed>, content_id?: string|null}> $formattedEvents */
        $formattedEvents = [];
        foreach ($events as $event) {
            if (! is_array($event)) {
                continue;
            }

            $formattedEvents[] = array_filter([
                'type' => isset($event['type']) && is_scalar($event['type']) ? strval($event['type']) : null,
                'name' => isset($event['name']) && is_scalar($event['name']) ? strval($event['name']) : null,
                'data' => isset($event['data']) && is_array($event['data']) ? $event['data'] : null,
                'content_id' => isset($event['content_id']) && is_scalar($event['content_id']) ? (string) $event['content_id'] : null,
            ], fn (string|array|null $v): bool => $v !== null);
        }

        $tracked = AnalyticsService::trackBatch($formattedEvents);

        return $this->success([
            'tracked_count' => count($tracked),
            'events' => $tracked,
        ], 'Events tracked successfully', 201);
    }

    /**
     * Get real-time analytics.
     */
    public function realTime(): JsonResponse
    {
        try {
            // Check if tables exist
            if (! Schema::hasTable('analytics_sessions') || ! Schema::hasTable('analytics_visits')) {
                return $this->success([
                    'active_sessions' => 0,
                    'visits_last_hour' => 0,
                    'top_pages_now' => [],
                ], 'Real-time analytics retrieved successfully');
            }

            $activeSessions = AnalyticsSession::whereNull('ended_at')
                ->where('started_at', '>=', now()->subMinutes(30))
                ->count();

            $visitsLastHour = AnalyticsVisit::where('visited_at', '>=', now()->subHour())
                ->count();

            $topPagesNow = AnalyticsVisit::where('visited_at', '>=', now()->subHour())
                ->select('url', DB::raw('count(*) as visits'))
                ->groupBy('url')
                ->orderByDesc('visits')
                ->limit(5)
                ->get();

            return $this->success([
                'active_sessions' => $activeSessions,
                'visits_last_hour' => $visitsLastHour,
                'top_pages_now' => $topPagesNow,
            ], 'Real-time analytics retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Analytics realTime error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            // Return empty data instead of error to prevent frontend issues
            return $this->success([
                'active_sessions' => 0,
                'visits_last_hour' => 0,
                'top_pages_now' => [],
            ], 'Real-time analytics retrieved successfully');
        }
    }

    /**
     * Calculate bounce rate.
     */
    protected function calculateBounceRate(string $dateFrom, string $dateTo): float
    {
        // A bounce is either:
        // 1. Single page view session, OR
        // 2. Session with duration < 10 seconds
        $bounceSessions = AnalyticsSession::whereBetween('started_at', [$dateFrom, $dateTo])
            ->where(function ($query): void {
                $query->where('page_views', 1)
                    ->orWhere('duration', '<', 10);
            })
            ->count();

        $totalSessions = AnalyticsSession::whereBetween('started_at', [$dateFrom, $dateTo])->count();

        if ($totalSessions === 0) {
            return 0.0;
        }

        return round(($bounceSessions / $totalSessions) * 100, 2);
    }

    /**
     * Export analytics data to CSV
     *
     * @return Response|JsonResponse
     */
    public function export(Request $request): ResponseFactory|Response|JsonResponse
    {
        try {
            $dateFromRaw = $request->input('date_from', now()->subDays(30)->format('Y-m-d'));
            $dateFrom = is_string($dateFromRaw) ? $dateFromRaw : now()->subDays(30)->format('Y-m-d');
            $dateToRaw = $request->input('date_to', now()->format('Y-m-d'));
            $dateTo = is_string($dateToRaw) ? $dateToRaw : now()->format('Y-m-d');
            $typeRaw = $request->input('type', 'visits'); // visits, events, sessions
            $type = is_string($typeRaw) ? $typeRaw : 'visits';

            $filename = "analytics-{$type}-{$dateFrom}-to-{$dateTo}.csv";

            $data = match ($type) {
                'events' => $this->exportEvents($dateFrom, $dateTo),
                'sessions' => $this->exportSessions($dateFrom, $dateTo),
                default => $this->exportVisits($dateFrom, $dateTo),
            };

            return response($data, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        } catch (\Exception $e) {
            Log::error('Analytics export error: '.$e->getMessage());

            return $this->error('Failed to export analytics data', 500);
        }
    }

    protected function exportVisits(string $dateFrom, string $dateTo): string
    {
        $visits = AnalyticsVisit::whereBetween('visited_at', [$dateFrom, $dateTo])
            ->orderBy('visited_at', 'desc')
            ->limit(10000)
            ->get();

        $csv = "ID,Session ID,URL,Referer,IP Address,Device Type,Browser,OS,Country,City,Visited At\n";

        foreach ($visits as $visit) {
            $csv .= sprintf(
                "%d,\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\"\n",
                $visit->id,
                $visit->session_id ?? '',
                str_replace('"', '""', $visit->url ?? ''),
                str_replace('"', '""', $visit->referer ?? ''),
                $visit->ip_address ?? '',
                $visit->device_type ?? '',
                $visit->browser ?? '',
                $visit->os ?? '',
                strval($visit->country ?? ''),
                strval($visit->city ?? ''),
                $visit->visited_at->format('Y-m-d H:i:s')
            );
        }

        return $csv;
    }

    protected function exportEvents(string $dateFrom, string $dateTo): string
    {
        $events = AnalyticsEvent::whereBetween('occurred_at', [$dateFrom, $dateTo])
            ->with('user')
            ->orderBy('occurred_at', 'desc')
            ->limit(10000)
            ->get();

        $csv = "ID,Session ID,User,Event Type,Event Name,URL,Content ID,IP Address,Occurred At\n";

        foreach ($events as $event) {
            $csv .= sprintf(
                "%d,\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",%s,\"%s\",\"%s\"\n",
                $event->id,
                $event->session_id ?? '',
                $event->user ? $event->user->name : 'Guest',
                $event->event_type ?? '',
                str_replace('"', '""', $event->event_name ?? ''),
                str_replace('"', '""', $event->url ?? ''),
                strval($event->content_id ?? ''),
                strval($event->ip_address ?? ''),
                $event->occurred_at ? $event->occurred_at->format('Y-m-d H:i:s') : ''
            );
        }

        return $csv;
    }

    protected function exportSessions(string $dateFrom, string $dateTo): string
    {
        $sessions = AnalyticsSession::whereBetween('started_at', [$dateFrom, $dateTo])
            ->with('user')
            ->orderBy('started_at', 'desc')
            ->limit(10000)
            ->get();

        $csv = "ID,Session ID,User,IP Address,Device Type,Browser,OS,Country,City,Page Views,Duration (sec),Started At,Ended At\n";

        foreach ($sessions as $session) {
            $csv .= sprintf(
                "%d,\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",%d,%d,\"%s\",\"%s\"\n",
                $session->id,
                $session->session_id ?? '',
                $session->user ? $session->user->name : 'Guest',
                $session->ip_address ?? '',
                $session->device_type ?? '',
                $session->browser ?? '',
                $session->os ?? '',
                $session->country ?? '',
                $session->city ?? '',
                $session->page_views ?? 0,
                $session->duration ?? 0,
                $session->started_at ? $session->started_at->format('Y-m-d H:i:s') : '',
                $session->ended_at ? $session->ended_at->format('Y-m-d H:i:s') : ''
            );
        }

        return $csv;
    }

    /**
     * Run analytics cleanup manually
     */
    public function cleanup(Request $request): JsonResponse
    {
        try {
            $daysVisitsRaw = Setting::get('analytics_retention_days', 90);
            $daysVisits = is_numeric($daysVisitsRaw) ? (int) $daysVisitsRaw : 90;
            $countVisits = AnalyticsVisit::where('visited_at', '<', now()->subDays($daysVisits))->delete();

            $daysEventsRaw = Setting::get('analytics_event_retention_days', 60);
            $daysEvents = is_numeric($daysEventsRaw) ? (int) $daysEventsRaw : 60;
            $countEvents = AnalyticsEvent::where('occurred_at', '<', now()->subDays($daysEvents))->delete();

            $daysSessionsRaw = Setting::get('analytics_visitor_retention_days', 30);
            $daysSessions = is_numeric($daysSessionsRaw) ? (int) $daysSessionsRaw : 30;
            $countSessions = AnalyticsSession::where('started_at', '<', now()->subDays($daysSessions))->delete();

            $totalDeleted = (is_numeric($countVisits) ? (int) $countVisits : 0) +
                            (is_numeric($countEvents) ? (int) $countEvents : 0) +
                            (is_numeric($countSessions) ? (int) $countSessions : 0);

            Log::info('Analytics manual cleanup triggered via UI', [
                'deleted' => [
                    'visits' => $countVisits,
                    'events' => $countEvents,
                    'sessions' => $countSessions,
                ],
            ]);

            Cache::forever('analytics_cache_epoch', time());

            return $this->success([
                'total_deleted' => $totalDeleted,
                'details' => [
                    'visits' => $countVisits,
                    'events' => $countEvents,
                    'sessions' => $countSessions,
                ],
            ], 'Analytics cleanup completed successfully');
        } catch (\Exception $e) {
            Log::error('Analytics cleanup error: '.$e->getMessage());

            return $this->error('Failed to run analytics cleanup', 500);
        }
    }

    /**
     * Delete all analytics visits, events, and sessions (full reset).
     * Requires JSON body: { "confirmation": "RESET_ALL_ANALYTICS" }.
     */
    public function purgeAll(Request $request): JsonResponse
    {
        $confirmation = $request->input('confirmation');
        if ($confirmation !== 'RESET_ALL_ANALYTICS') {
            return $this->error('Invalid confirmation. Send JSON: {"confirmation":"RESET_ALL_ANALYTICS"}', 422);
        }

        try {
            $deleted = DB::transaction(function (): array {
                $visits = AnalyticsVisit::query()->delete();
                $events = AnalyticsEvent::query()->delete();
                $sessions = AnalyticsSession::query()->delete();

                return [
                    'visits' => is_numeric($visits) ? (int) $visits : 0,
                    'events' => is_numeric($events) ? (int) $events : 0,
                    'sessions' => is_numeric($sessions) ? (int) $sessions : 0,
                ];
            });

            Cache::forever('analytics_cache_epoch', time());

            $total = $deleted['visits'] + $deleted['events'] + $deleted['sessions'];

            Log::warning('Analytics full purge executed', [
                'user_id' => $request->user()?->id,
                'deleted' => $deleted,
            ]);

            return $this->success([
                'total_deleted' => $total,
                'details' => $deleted,
            ], 'All analytics data has been deleted.');
        } catch (\Exception $e) {
            Log::error('Analytics purge error: '.$e->getMessage());

            return $this->error('Failed to purge analytics data', 500);
        }
    }
}
