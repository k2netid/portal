<?php

namespace Modules\Core\System\Http\Controllers\Console;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Content\Media\Models\File;
use Modules\Content\Media\Models\File as Media;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Models\User;
use Modules\Core\System\Services\DashboardRegistry;
use Modules\Intelligence\Analytics\Models\AnalyticsVisit;

/**
 * @OA\Tag(name="Dashboard")
 */
class DashboardController extends BaseApiController
{
    /**
     * @OA\Get(
     *     path="/api/admin/ja/dashboard/admin",
     *     summary="Get admin dashboard data",
     *     tags={"Dashboard"},
     *
     *     @OA\Response(response=200, description="Dashboard data retrieved successfully"),
     *     security={{"sanctum":{}}}
     * )
     */
    public function admin(Request $request, DashboardRegistry $registry): JsonResponse
    {
        $daysRaw = $request->input('days', 30);
        $days = is_numeric($daysRaw) ? max(1, min(366, (int) $daysRaw)) : 30;

        $cacheKey = 'dashboard_admin_data_'.$days;

        $data = Cache::remember($cacheKey, 300, fn () => [
            'stats' => array_merge([
                'media' => $this->getMediaStats(),
                'users' => $this->getUserStats(),
            ], $registry->getAllStats()),
            'charts' => array_merge([
                'mediaByType' => $this->getMediaByType(),
                'contentTraffic' => $this->getSiteTrafficSeries($days),
                'userActivity' => $this->getUserActivity($days),
            ], $registry->getAllCharts()),
        ]);

        return $this->success($data);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/ja/dashboard/creator",
     *     summary="Get creator dashboard data",
     *     tags={"Dashboard"},
     *
     *     @OA\Parameter(name="days", in="query", @OA\Schema(type="integer", default=30)),
     *
     *     @OA\Response(response=200, description="Dashboard data retrieved successfully"),
     *     security={{"sanctum":{}}}
     * )
     */
    public function creator(Request $request, DashboardRegistry $registry): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        $userId = (int) $user->id;
        $daysRaw = $request->input('days', 30);
        $days = is_numeric($daysRaw) ? (int) $daysRaw : 30;

        $cacheKey = "dashboard_creator_data_{$userId}_{$days}";

        $data = Cache::remember($cacheKey, 300, fn () => [
            'stats' => array_merge([
                'myMedia' => $this->getMyMediaStats($userId),
            ], $registry->getAllStats()), // Individual modules should handle userId filtering in their providers if needed
            'charts' => array_merge([
                'mediaTraffic' => [], // Placeholder for media specific traffic if any
            ], $registry->getAllCharts()),
        ]);

        return $this->success($data);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/ja/dashboard/viewer",
     *     summary="Get viewer dashboard data",
     *     tags={"Dashboard"},
     *
     *     @OA\Response(response=200, description="Dashboard data retrieved successfully"),
     *     security={{"sanctum":{}}}
     * )
     */
    public function viewer(Request $request, DashboardRegistry $registry): JsonResponse
    {
        // Viewer dashboard data is primarily module-specific (e.g. Latest Content)
        return $this->success($registry->getAllStats()['viewer'] ?? []);
    }

    // Helper methods (Core Only)

    /**
     * @return array{total: int, images: int, videos: int, documents: int}
     */
    private function getMediaStats(): array
    {
        return [
            'total' => Media::count(),
            'images' => Media::where('mime_type', 'like', 'image/%')->count(),
            'videos' => Media::where('mime_type', 'like', 'video/%')->count(),
            'documents' => Media::where('mime_type', 'not like', 'image/%')
                ->where('mime_type', 'not like', 'video/%')->count(),
        ];
    }

    /**
     * @return array{total: int, active: int}
     */
    private function getUserStats(): array
    {
        return [
            'total' => User::count(),
            'active' => User::where('created_at', '>=', now()->subDays(30))->count(),
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, File>
     */
    private function getMediaByType(): Collection
    {
        $driver = DB::connection()->getDriverName();
        $typeExpr = match ($driver) {
            'pgsql' => "split_part(mime_type, '/', 1)",
            'sqlite' => "CASE WHEN mime_type LIKE '%/%' THEN SUBSTR(mime_type, 1, INSTR(mime_type, '/') - 1) ELSE mime_type END",
            default => 'SUBSTRING_INDEX(mime_type, \'/\', 1)',
        };

        return Media::select(
            DB::raw("{$typeExpr} as type"),
            DB::raw('count(*) as count')
        )
            ->groupBy(DB::raw($typeExpr))
            ->get();
    }

    /**
     * Daily site page views from analytics_visits (aligned with dashboard time filter).
     *
     * @return array<int, array{period: string, visits: int}>
     */
    private function getSiteTrafficSeries(int $days): array
    {
        $days = max(1, min(366, $days));
        $from = now()->copy()->subDays($days - 1)->startOfDay();

        $rows = AnalyticsVisit::query()
            ->selectRaw('DATE(visited_at) as period, COUNT(*) as visits')
            ->where('visited_at', '>=', $from)
            ->groupByRaw('DATE(visited_at)')
            ->orderBy('period')
            ->pluck('visits', 'period');

        $series = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $period = now()->copy()->subDays($i)->toDateString();
            $series[] = [
                'period' => $period,
                'visits' => is_numeric($v = $rows[$period] ?? 0) ? (int) $v : 0,
            ];
        }

        return $series;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    private function getUserActivity(int $days = 30): Collection
    {
        $days = max(1, min(366, $days));
        $driver = DB::connection()->getDriverName();
        $dateExpr = match ($driver) {
            'pgsql' => 'DATE(created_at)',
            'sqlite' => 'DATE(created_at)',
            default => 'DATE(created_at)',
        };

        return User::query()
            ->select(DB::raw("{$dateExpr} as date"), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->copy()->subDays($days)->startOfDay())
            ->groupBy(DB::raw($dateExpr))
            ->orderBy('date')
            ->get();
    }

    /**
     * @return array{total: int, size: float|int}
     */
    private function getMyMediaStats(int $userId): array
    {
        return [
            'total' => Media::where('author_id', $userId)->count(),
            'size' => (float) Media::where('author_id', $userId)->sum('size'),
        ];
    }
}
