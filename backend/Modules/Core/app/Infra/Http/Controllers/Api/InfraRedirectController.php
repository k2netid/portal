<?php

namespace Modules\Core\Infra\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Infra\Models\InfraRedirect;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Support\SqlLikeEscape;

class InfraRedirectController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('view analytics'); // Using analytics permission for now or manage settings

        $redirects = InfraRedirect::query()
            ->when($request->search, function ($query, $search): void {
                $searchStr = strtolower(is_string($search) ? $search : '');
                $pat = SqlLikeEscape::contains($searchStr);
                $esc = SqlLikeEscape::LIKE_ESCAPE_SQL;
                $query->where(function ($q) use ($pat, $esc): void {
                    $q->whereRaw("lower(from_domain) LIKE ? {$esc}", [$pat])
                        ->orWhereRaw("lower(to_domain) LIKE ? {$esc}", [$pat]);
                });
            })
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return $this->success($redirects);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('manage settings');

        $validated = $request->validate([
            'from_domain' => 'required|string|unique:infra_redirects,from_domain',
            'to_domain' => 'required|string',
            'target_path' => 'nullable|string',
            'status_code' => 'required|integer|in:301,302',
            'keep_path' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $redirect = InfraRedirect::create($validated);

        return $this->success($redirect, 'Redirect created successfully', 201);
    }

    public function show(InfraRedirect $redirect): JsonResponse
    {
        $this->authorize('manage settings');

        return $this->success($redirect);
    }

    public function update(Request $request, InfraRedirect $redirect): JsonResponse
    {
        $this->authorize('manage settings');

        $validated = $request->validate([
            'from_domain' => 'required|string|unique:infra_redirects,from_domain,'.$redirect->id,
            'to_domain' => 'required|string',
            'target_path' => 'nullable|string',
            'status_code' => 'required|integer|in:301,302',
            'keep_path' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $redirect->update($validated);

        return $this->success($redirect, 'Redirect updated successfully');
    }

    public function destroy(InfraRedirect $redirect): JsonResponse
    {
        $this->authorize('manage settings');
        $redirect->delete();

        return $this->success(null, 'Redirect deleted successfully');
    }

    public function toggle(InfraRedirect $redirect): JsonResponse
    {
        $this->authorize('manage settings');
        $redirect->update(['is_active' => ! $redirect->is_active]);

        return $this->success($redirect, 'Redirect status updated');
    }
}
