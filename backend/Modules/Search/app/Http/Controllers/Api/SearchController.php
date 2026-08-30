<?php

namespace Modules\Search\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Core\System\Helpers\IpHelper;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Search\Models\SearchQuery;
use Modules\Search\Services\SearchIndexHealthService;
use Modules\Search\Services\SearchService;

class SearchController extends BaseApiController
{
    public function __construct(
        protected SearchService $searchService,
        protected SearchIndexHealthService $indexHealth,
    ) {}

    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2|max:255',
            'type' => 'nullable|in:post,page,category,tag',
            'types' => 'nullable|array',
            'types.*' => 'in:post,page,category,tag',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $query = is_string($request->input('q')) ? $request->input('q') : '';

        $filters = [];
        if ($request->filled('types') && is_array($request->input('types'))) {
            $filters['types'] = $request->input('types');
        } elseif ($request->has('type')) {
            $filters['types'] = [$request->input('type')];
        }
        if ($request->has('date_from')) {
            $filters['date_from'] = $request->input('date_from');
        }
        if ($request->has('date_to')) {
            $filters['date_to'] = $request->input('date_to');
        }

        $limitRaw = $request->input('limit', 20);
        $limit = is_numeric($limitRaw) ? (int) $limitRaw : 20;

        $results = $this->searchService->search($query, $filters, $limit);

        return $this->success($results, 'Search results retrieved successfully');
    }

    public function suggestions(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2|max:255',
            'limit' => 'nullable|integer|min:1|max:10',
            'types' => 'nullable|array',
            'types.*' => 'in:post,page,category,tag',
        ]);

        $query = is_string($request->input('q')) ? $request->input('q') : '';
        $limitRaw = $request->input('limit', 5);
        $limit = is_numeric($limitRaw) ? (int) $limitRaw : 5;
        $filters = [];
        if ($request->filled('types') && is_array($request->input('types'))) {
            $filters['types'] = $request->input('types');
        }

        $suggestions = $this->searchService->getSuggestions($query, $limit, $filters);

        return $this->success([
            'suggestions' => $suggestions,
        ], 'Search suggestions retrieved successfully');
    }

    public function getQueries(Request $request): JsonResponse
    {
        $limitRaw = $request->input('limit', 10);
        $limit = is_numeric($limitRaw) ? (int) $limitRaw : 10;
        $daysRaw = $request->input('days', 30);
        $days = is_numeric($daysRaw) ? (int) $daysRaw : 30;

        $queries = SearchQuery::where('searched_at', '>=', now()->subDays($days))
            ->orderByDesc('searched_at')
            ->paginate($limit);

        return $this->success($queries, 'Search queries retrieved successfully');
    }

    public function getIndexHealth(): JsonResponse
    {
        return $this->success($this->indexHealth->snapshot(), 'Search index health');
    }

    public function getStats(Request $request): JsonResponse
    {
        $daysRaw = $request->input('days', 30);
        $days = is_numeric($daysRaw) ? (int) $daysRaw : 30;

        $stats = [
            'total_searches' => SearchQuery::where('searched_at', '>=', now()->subDays($days))->count(),
            'unique_queries' => SearchQuery::where('searched_at', '>=', now()->subDays($days))
                ->distinct('query')
                ->count('query'),
            'avg_results' => (float) SearchQuery::where('searched_at', '>=', now()->subDays($days))
                ->avg('results_count'),
            'zero_result_searches' => SearchQuery::where('searched_at', '>=', now()->subDays($days))
                ->where('results_count', 0)
                ->count(),
            'popular_queries' => SearchQuery::getPopularQueries(10, $days),
        ];

        return $this->success($stats, 'Search statistics retrieved successfully');
    }

    public function reindex(Request $request): JsonResponse
    {
        $result = $this->searchService->reindexAll();

        return $this->success([
            'indexed' => $result,
        ], 'Search index rebuilt successfully');
    }

    public function deleteQuery(string $id): JsonResponse
    {
        $userId = Auth::id();
        $ip = IpHelper::getClientIp(request());

        $query = SearchQuery::query();

        // If the parameter is a UUID, delete that specific row. Otherwise, delete all occurrences of that query term.
        if (preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/', $id)) {
            $query->where('id', $id);
        } else {
            $query->where('query', $id);
        }

        // Scope the deletion to only the current user or active client IP
        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->where('ip_address', $ip);
        }

        $deleted = $query->delete();

        if ($deleted) {
            return $this->success(null, 'Search history item deleted successfully');
        }

        return $this->error('Search history item not found or unauthorized', 404);
    }

    public function clearQueries(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $ip = IpHelper::getClientIp(request());

        $query = SearchQuery::query();

        // Scope the bulk clear to only the current user or active client IP
        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->where('ip_address', $ip);
        }

        $query->delete();

        return $this->success(null, 'Search history cleared successfully');
    }
}
