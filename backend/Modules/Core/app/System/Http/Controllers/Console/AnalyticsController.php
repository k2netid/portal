<?php

declare(strict_types=1);

namespace Modules\Core\System\Http\Controllers\Console;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Models\Setting;

class AnalyticsController extends BaseApiController
{
    /**
     * POST /api/v1/manage/analytics/cleanup
     * Clean up old analytics data based on retention setting.
     */
    public function cleanup(): JsonResponse
    {
        $retentionDays = (int) Setting::get('analytics_retention_days', 90);
        $cutoffDate = now()->subDays($retentionDays);
        $totalDeleted = 0;

        $tables = ['sys_analytics_events', 'sys_analytics_visitors', 'sys_page_views'];
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)->where('created_at', '<', $cutoffDate)->count();
                if ($count > 0) {
                    DB::table($table)->where('created_at', '<', $cutoffDate)->delete();
                    $totalDeleted += $count;
                }
            }
        }

        return $this->success(
            ['total_deleted' => $totalDeleted],
            "Pembersihan data analitik selesai ({$totalDeleted} data dibersihkan)."
        );
    }

    /**
     * POST /api/v1/manage/analytics/purge-all
     * Permanently purge all analytics data upon confirmation.
     */
    public function purgeAll(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'confirmation' => 'required|string|in:RESET_ALL_ANALYTICS',
        ]);

        $totalDeleted = 0;
        $tables = ['sys_analytics_events', 'sys_analytics_visitors', 'sys_page_views'];
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)->count();
                if ($count > 0) {
                    DB::table($table)->truncate();
                    $totalDeleted += $count;
                }
            }
        }

        return $this->success(
            ['total_deleted' => $totalDeleted],
            'Seluruh data analitik berhasil dikosongkan.'
        );
    }

    /**
     * GET /api/v1/manage/analytics/overview
     */
    public function overview(): JsonResponse
    {
        return $this->success([
            'total_visits' => 0,
            'unique_visitors' => 0,
            'page_views' => 0,
            'avg_session_duration' => 0,
            'bounce_rate' => 0,
            'chart_data' => [],
        ], 'Analytics overview retrieved');
    }

    /**
     * GET /api/v1/manage/analytics/visits
     */
    public function visits(): JsonResponse
    {
        return $this->success([], 'Analytics visits retrieved');
    }

    /**
     * GET /api/v1/manage/analytics/top-pages
     */
    public function topPages(): JsonResponse
    {
        return $this->success([], 'Top pages retrieved');
    }

    /**
     * GET /api/v1/manage/analytics/devices
     */
    public function devices(): JsonResponse
    {
        return $this->success([], 'Device analytics retrieved');
    }

    /**
     * GET /api/v1/manage/analytics/browsers
     */
    public function browsers(): JsonResponse
    {
        return $this->success([], 'Browser analytics retrieved');
    }

    /**
     * GET /api/v1/manage/analytics/countries
     */
    public function countries(): JsonResponse
    {
        return $this->success([], 'Country analytics retrieved');
    }

    /**
     * GET /api/v1/manage/analytics/referrers
     */
    public function referrers(): JsonResponse
    {
        return $this->success([], 'Referrer analytics retrieved');
    }

    /**
     * GET /api/v1/manage/analytics/realtime
     */
    public function realtime(): JsonResponse
    {
        return $this->success([
            'active_visitors' => 0,
            'recent_pages' => [],
        ], 'Realtime analytics retrieved');
    }

    /**
     * GET /api/v1/manage/analytics/export
     */
    public function export(): JsonResponse
    {
        return $this->success([], 'Analytics export generated');
    }
}
