<?php

namespace Modules\Core\Security\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Core\Security\Models\CspReport;
use Modules\Core\System\Helpers\IpHelper;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Support\SqlLikeEscape;

class CspReportController extends BaseApiController
{
    /**
     * Receive CSP violation reports from browsers
     * Public endpoint, no authentication required
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $reportRaw = $request->input('csp-report');
            $report = is_array($reportRaw) ? $reportRaw : null;

            // Fallback for direct JSON or slightly different formats
            if (! $report) {
                $reportAll = $request->json()->all();
                $report = isset($reportAll['csp-report']) && is_array($reportAll['csp-report']) ? $reportAll['csp-report'] : $reportAll;
            }

            if (empty($report)) {
                return response()->json(['status' => 'ignored'], 200);
            }

            // Log to security channel for immediate visibility
            $clientIp = IpHelper::getClientIp($request);
            Log::channel('security')->warning('CSP Violation Reported', [
                'ip' => $clientIp,
                'blocked_uri' => $report['blocked-uri'] ?? 'unknown',
                'violated_directive' => $report['violated-directive'] ?? 'unknown',
                'document_uri' => $report['document-uri'] ?? 'unknown',
                'user_agent' => $request->userAgent(),
            ]);

            CspReport::create([
                'document_uri' => is_string($report['document-uri'] ?? null) ? $report['document-uri'] : '',
                'violated_directive' => is_string($report['violated-directive'] ?? null) ? $report['violated-directive'] : '',
                'blocked_uri' => is_string($report['blocked-uri'] ?? null) ? $report['blocked-uri'] : '',
                'source_file' => is_string($report['source-file'] ?? null) ? $report['source-file'] : null,
                'line_number' => (is_numeric($report['line-number'] ?? null)) ? (int) $report['line-number'] : null,
                'user_agent' => $request->userAgent(),
                'ip_address' => $clientIp,
                'raw_report' => $report,
                'status' => 'new',
            ]);

            return response()->json(['status' => 'received'], 200);

        } catch (\Exception $e) {
            Log::error('CSP report storage failed', ['error' => $e->getMessage()]);

            return response()->json(['status' => 'error'], 500);
        }
    }

    /**
     * Get CSP reports for admin dashboard
     */
    public function index(Request $request): JsonResponse
    {
        $query = CspReport::query();

        if ($request->filled('status')) {
            $statusRaw = $request->input('status');
            $status = is_string($statusRaw) ? $statusRaw : '';
            $query->where('status', $status);
        }

        if ($request->filled('directive')) {
            $directiveRaw = $request->input('directive');
            $directive = is_string($directiveRaw) ? $directiveRaw : '';
            $pat = SqlLikeEscape::contains($directive);
            $esc = SqlLikeEscape::LIKE_ESCAPE_SQL;
            $query->whereRaw('violated_directive LIKE ? '.$esc, [$pat]);
        }

        if ($request->filled('date_from')) {
            $dateFromRaw = $request->input('date_from');
            $dateFrom = is_string($dateFromRaw) ? $dateFromRaw : null;
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($request->filled('date_to')) {
            $dateToRaw = $request->input('date_to');
            $dateTo = is_string($dateToRaw) ? $dateToRaw : null;
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $perPageRaw = $request->input('per_page', 50);
        $perPage = is_numeric($perPageRaw) ? (int) $perPageRaw : 50;
        $reports = $query->latest()->paginate($perPage);

        return $this->paginated($reports, 'CSP reports retrieved successfully');
    }

    /**
     * Bulk action on CSP reports
     */
    public function bulkAction(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'action' => 'required|in:mark_reviewed,mark_false_positive,delete',
        ]);

        $idsRaw = $validated['ids'];
        $ids = is_array($idsRaw) ? $idsRaw : [];
        $query = CspReport::whereIn('id', $ids);

        match ($validated['action']) {
            'mark_reviewed' => $query->update(['status' => 'reviewed']),
            'mark_false_positive' => $query->update(['status' => 'false_positive']),
            'delete' => $query->delete(),
            default => $this->success(null, 'Bulk action completed successfully'),
        };

        return $this->success(null, 'Bulk action completed successfully');
    }

    /**
     * Get CSP report statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total' => CspReport::count(),
            'new' => CspReport::where('status', 'new')->count(),
            'by_directive' => CspReport::select('violated_directive', DB::raw('count(*) as count'))
                ->groupBy('violated_directive')
                ->orderByDesc('count')
                ->limit(10)
                ->get(),
            'recent_trend' => CspReport::selectRaw('DATE(created_at) as date, count(*) as count')
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
        ];

        return $this->success($stats, 'CSP statistics retrieved successfully');
    }
}
