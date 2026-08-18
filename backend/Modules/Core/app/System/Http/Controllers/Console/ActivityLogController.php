<?php

namespace Modules\Core\System\Http\Controllers\Console;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Models\ActivityLog;
use Modules\Core\System\Models\User;
use Modules\Core\System\Support\SqlLikeEscape;

class ActivityLogController extends BaseApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = ActivityLog::with('user');

            // Filters
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->input('user_id'));
            }

            if ($request->filled('action')) {
                $query->where('action', $request->input('action'));
            }

            if ($request->filled('model_type')) {
                $modelTypeRaw = $request->input('model_type');
                $modelType = is_string($modelTypeRaw) ? $modelTypeRaw : '';
                $mtPat = SqlLikeEscape::contains($modelType);
                $mtEsc = SqlLikeEscape::LIKE_ESCAPE_SQL;
                $query->whereRaw('model_type LIKE ? '.$mtEsc, [$mtPat]);
            }

            if ($request->filled('ip_address')) {
                $query->where('ip_address', $request->input('ip_address'));
            }

            if ($request->filled('date_from')) {
                $dateFromRaw = $request->input('date_from');
                $dateFrom = is_string($dateFromRaw) ? $dateFromRaw : null;
                if ($dateFrom) {
                    $query->whereDate('created_at', '>=', $dateFrom);
                }
            }

            if ($request->filled('date_to')) {
                $dateToRaw = $request->input('date_to');
                $dateTo = is_string($dateToRaw) ? $dateToRaw : null;
                if ($dateTo) {
                    $query->whereDate('created_at', '<=', $dateTo);
                }
            }

            if ($request->filled('search')) {
                $searchRaw = $request->input('search');
                $search = is_string($searchRaw) ? $searchRaw : '';
                $pat = SqlLikeEscape::contains($search);
                $esc = SqlLikeEscape::LIKE_ESCAPE_SQL;
                $query->where(function ($q) use ($pat, $esc): void {
                    $q->whereRaw('description LIKE ? '.$esc, [$pat])
                        ->orWhereRaw('model_type LIKE ? '.$esc, [$pat])
                        ->orWhereHas('user', function ($userQuery) use ($pat, $esc): void {
                            $userQuery->whereRaw('name LIKE ? '.$esc, [$pat]);
                        });
                });
            }

            // Sorting
            $sortByRaw = $request->input('sort_by', 'created_at');
            $sortBy = is_string($sortByRaw) ? $sortByRaw : 'created_at';
            $sortDirRaw = $request->input('sort_dir', 'desc');
            $sortDir = is_string($sortDirRaw) ? $sortDirRaw : 'desc';
            $allowedSorts = ['created_at', 'action', 'model_type', 'user_id'];
            if (in_array($sortBy, $allowedSorts)) {
                $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
            } else {
                $query->latest();
            }

            // Pagination with customizable per_page
            $perPageRaw = $request->input('per_page', 50);
            $perPage = min(max(is_numeric($perPageRaw) ? (int) $perPageRaw : 50, 1), 500);
            $logs = $query->paginate($perPage);

            return $this->paginated($logs, 'Activity logs retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Activity logs index error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->success([], 'Activity logs retrieved successfully');
        }
    }

    /**
     * Export activity logs to CSV
     *
     * @return Response|JsonResponse
     */
    public function export(Request $request): ResponseFactory|Response|JsonResponse
    {
        try {
            $query = ActivityLog::with('user');

            // Apply same filters as index
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->input('user_id'));
            }

            if ($request->filled('action')) {
                $query->where('action', $request->input('action'));
            }

            if ($request->filled('model_type')) {
                $modelTypeRaw = $request->input('model_type');
                $modelType = is_string($modelTypeRaw) ? $modelTypeRaw : '';
                $mtPat = SqlLikeEscape::contains($modelType);
                $mtEsc = SqlLikeEscape::LIKE_ESCAPE_SQL;
                $query->whereRaw('model_type LIKE ? '.$mtEsc, [$mtPat]);
            }

            if ($request->filled('date_from')) {
                $dateFromRaw = $request->input('date_from');
                $dateFrom = is_string($dateFromRaw) ? $dateFromRaw : null;
                if ($dateFrom) {
                    $query->whereDate('created_at', '>=', $dateFrom);
                }
            }

            if ($request->filled('date_to')) {
                $dateToRaw = $request->input('date_to');
                $dateTo = is_string($dateToRaw) ? $dateToRaw : null;
                if ($dateTo) {
                    $query->whereDate('created_at', '<=', $dateTo);
                }
            }

            $logs = $query->latest()->limit(10000)->get();

            // Generate CSV content
            $csv = "ID,User,Action,Model Type,Model ID,Description,IP Address,Created At\n";
            foreach ($logs as $log) {
                $csv .= sprintf(
                    "%d,\"%s\",\"%s\",\"%s\",%s,\"%s\",\"%s\",\"%s\"\n",
                    $log->id,
                    $log->user->name ?? 'System',
                    $log->action,
                    $log->model_type ?? '',
                    $log->model_id ?? '',
                    str_replace('"', '""', $log->description ?? ''),
                    $log->ip_address ?? '',
                    $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : ''
                );
            }

            return response($csv, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="activity-logs-'.now()->format('Y-m-d').'.csv"',
            ]);
        } catch (\Exception $e) {
            Log::error('Activity logs export error: '.$e->getMessage());

            return $this->error('Failed to export activity logs', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ActivityLog $activityLog): JsonResponse
    {
        return $this->success($activityLog->load('user'), 'Activity log retrieved successfully');
    }

    /**
     * Display a listing of the resource for a specific user.
     *
     * @param  int|string  $userId
     */
    public function userActivity(Request $request, $userId): JsonResponse
    {
        $logs = ActivityLog::where('user_id', $userId)
            ->with('user')
            ->latest()
            ->paginate(50);

        return $this->paginated($logs, 'User activity logs retrieved successfully');
    }

    /**
     * Display a listing of the most recent resources.
     */
    public function recent(Request $request): JsonResponse
    {
        $limitRaw = $request->input('limit', 20);
        $limit = is_numeric($limitRaw) ? (int) $limitRaw : 20;

        $logs = ActivityLog::with('user')
            ->latest()
            ->limit($limit)
            ->get();

        return $this->success($logs, 'Recent activity logs retrieved successfully');
    }

    /**
     * Display statistics about activity logs.
     */
    public function statistics(Request $request): JsonResponse
    {
        $query = ActivityLog::query();

        if ($request->has('date_from')) {
            $dateFromRaw = $request->input('date_from');
            $dateFrom = is_string($dateFromRaw) ? $dateFromRaw : null;
            if ($dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            }
        }

        if ($request->has('date_to')) {
            $dateToRaw = $request->input('date_to');
            $dateTo = is_string($dateToRaw) ? $dateToRaw : null;
            if ($dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            }
        }

        $baseQuery = clone $query;

        $stats = [
            'total' => $baseQuery->count(),
            'today' => $baseQuery->whereDate('created_at', today())->count(),
            'this_week' => $baseQuery->whereDate('created_at', '>=', now()->subWeek())->count(),
            'active_users' => $baseQuery->whereNotNull('user_id')->distinct('user_id')->count('user_id'),
            'actions_by_type' => $query->selectRaw('action, count(*) as count')
                ->groupBy('action')
                ->pluck('count', 'action'),
            'actions_by_user' => $query->selectRaw('user_id, count(*) as count')
                ->whereNotNull('user_id')
                ->groupBy('user_id')
                ->get()
                ->map(function ($item): array {
                    $userId = $item->getAttribute('user_id');
                    $user = (is_string($userId) && $userId !== '') ? User::find($userId) : null;

                    $count = $item->getAttribute('count');

                    return [
                        'user' => $user ? ['id' => $user->id, 'name' => $user->name, 'email' => $user->email] : null,
                        'count' => is_scalar($count) ? intval($count) : 0,
                    ];
                }),
            'actions_by_model' => $query->selectRaw('model_type, count(*) as count')
                ->whereNotNull('model_type')
                ->groupBy('model_type')
                ->pluck('count', 'model_type'),
        ];

        return $this->success($stats, 'Activity log statistics retrieved successfully');
    }

    /**
     * Clear activity logs.
     */
    public function clear(Request $request): JsonResponse
    {
        try {
            // Optional: retain last X days
            $retainDaysRaw = $request->input('retain_days');
            $retainDays = is_numeric($retainDaysRaw) ? (int) $retainDaysRaw : null;

            if ($retainDays) {
                $count = ActivityLog::where('created_at', '<', now()->subDays($retainDays))->delete();
                $countStr = is_numeric($count) ? (string) $count : '0';
                $retainDaysStr = (string) $retainDays;

                return $this->success(null, "Cleared $countStr activity logs older than $retainDaysStr days");
            }

            ActivityLog::truncate();

            return $this->success(null, 'All activity logs cleared successfully');
        } catch (\Exception $e) {
            Log::error('Activity logs clear error: '.$e->getMessage());

            return $this->error('Failed to clear activity logs', 500);
        }
    }
}
