<?php

declare(strict_types=1);

namespace Modules\Core\Security\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Models\AbacPolicy;

class AbacPolicyController extends BaseApiController
{
    public function __construct()
    {
        $this->middleware('kyc:level_1')->only(['store', 'update', 'destroy']);
    }

    public function index(Request $request): JsonResponse
    {
        $policies = AbacPolicy::latest()->get();

        return $this->success($policies);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_resource' => 'required|string|max:255',
            'action' => 'required|string|max:255',
            'conditions' => 'required|array',
            'is_active' => 'boolean',
        ]);

        $policy = AbacPolicy::create($validated);

        return $this->success($policy, 'ABAC Policy created successfully', 201);
    }

    public function show(string $id): JsonResponse
    {
        $policy = AbacPolicy::findOrFail($id);

        return $this->success($policy);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $policy = AbacPolicy::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_resource' => 'required|string|max:255',
            'action' => 'required|string|max:255',
            'conditions' => 'required|array',
            'is_active' => 'boolean',
        ]);

        $policy->update($validated);

        return $this->success($policy, 'ABAC Policy updated successfully');
    }

    public function destroy(string $id): JsonResponse
    {
        $policy = AbacPolicy::findOrFail($id);
        $policy->delete();

        return $this->success(null, 'ABAC Policy deleted successfully');
    }
}
