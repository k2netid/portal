<?php

namespace Modules\Core\System\Http\Controllers\Console;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Models\ActivityLog;
use Modules\Core\System\Models\User;
use Modules\Core\System\Services\DashboardRegistry;

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
                'users' => $this->getUserStats(),
                'system' => $this->getSystemStats(),
            ], $registry->getAllStats()),
            'charts' => array_merge([
                'userActivity' => $this->getUserActivity($days),
                'systemActivity' => $this->getSystemActivitySeries($days),
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

        $userId = $user->id;
        $daysRaw = $request->input('days', 30);
        $days = is_numeric($daysRaw) ? (int) $daysRaw : 30;

        $cacheKey = "dashboard_creator_data_{$userId}_{$days}";

        $data = Cache::remember($cacheKey, 300, fn () => [
            'stats' => array_merge([
                'users' => $this->getUserStats(),
            ], $registry->getAllStats()),
            'charts' => array_merge([
                'userActivity' => $this->getUserActivity($days),
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
        return $this->success($registry->getAllStats()['viewer'] ?? []);
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
     * @return array{php_version: string, laravel_version: string, memory_usage: string}
     */
    private function getSystemStats(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2).' MB',
        ];
    }

    /**
     * Daily system activity logs from sys_activity_logs.
     *
     * @return array<int, array{period: string, visits: int}>
     */
    private function getSystemActivitySeries(int $days): array
    {
        $days = max(1, min(366, $days));
        $from = now()->copy()->subDays($days - 1)->startOfDay();

        $rows = [];
        if (Schema::hasTable('sys_activity_logs')) {
            $rows = ActivityLog::query()
                ->selectRaw('DATE(created_at) as period, COUNT(*) as count')
                ->where('created_at', '>=', $from)
                ->groupByRaw('DATE(created_at)')
                ->orderBy('period')
                ->pluck('count', 'period');
        }

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
}
