<?php

namespace Modules\Layout\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Support\SqlLikeEscape;
use Modules\Layout\Models\Redirect;

class RedirectController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Redirect::query();

        if ($request->has('module_scope')) {
            $query->where('module_scope', $request->input('module_scope'));
        }

        if ($request->filled('search')) {
            $searchRaw = $request->input('search');
            $search = is_string($searchRaw) ? trim($searchRaw) : '';
            if ($search !== '') {
                SqlLikeEscape::whereContainsAny($query, ['source_path', 'target_path'], mb_strtolower($search, 'UTF-8'));
            }
        }

        $redirects = $query->latest()->paginate(20);

        return $this->success($redirects, 'Redirects retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'source_path' => 'required|string|unique:lay_redirects,source_path',
            'target_path' => 'required|string',
            'status_code' => 'required|integer',
            'module_scope' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $redirect = Redirect::create($validated);

        return $this->success($redirect, 'Redirect created successfully', 201);
    }

    public function show(Redirect $redirect): JsonResponse
    {
        return $this->success($redirect, 'Redirect retrieved successfully');
    }

    public function update(Request $request, Redirect $redirect): JsonResponse
    {
        $validated = $request->validate([
            'source_path' => 'sometimes|required|string|unique:lay_redirects,source_path,'.$redirect->id,
            'target_path' => 'sometimes|required|string',
            'status_code' => 'sometimes|required|integer',
            'is_active' => 'boolean',
        ]);

        $redirect->update($validated);

        return $this->success($redirect, 'Redirect updated successfully');
    }

    public function destroy(Redirect $redirect): JsonResponse
    {
        $redirect->delete();

        return $this->success(null, 'Redirect deleted successfully');
    }

    public function statistics(): JsonResponse
    {
        $stats = [
            'total' => Redirect::count(),
            'active' => Redirect::where('is_active', true)->count(),
            'total_hits' => Redirect::sum('hits'),
            'top_redirects' => Redirect::where('is_active', true)
                ->orderBy('hits', 'desc')
                ->limit(10)
                ->get(),
        ];

        return $this->success($stats, 'Redirect statistics retrieved successfully');
    }
}
