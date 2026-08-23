<?php

namespace Modules\Library\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Library\Models\FieldGroup;

class FieldGroupController extends BaseApiController
{
    public function index(): JsonResponse
    {
        $groups = FieldGroup::with('fields')->get();

        return $this->success($groups, 'Field groups retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assignments' => 'sometimes|array',
            'assignments.*' => 'array',
            'assignments.*.assignable_type' => 'required_with:assignments|string',
            'assignments.*.module_scope' => 'nullable|string',
        ]);

        $assignments = $validated['assignments'] ?? null;
        unset($validated['assignments']);

        $group = FieldGroup::create($validated);

        if (is_array($assignments)) {
            foreach ($assignments as $assignment) {
                if (! is_array($assignment)) {
                    continue;
                }
                /** @var array<string, mixed> $assignment */
                $group->assignments()->create($assignment);
            }
        }

        return $this->success($group->load('assignments'), 'Field group created successfully', 201);
    }

    public function show(FieldGroup $fieldGroup): JsonResponse
    {
        return $this->success($fieldGroup->load(['fields', 'assignments']), 'Field group retrieved successfully');
    }

    public function update(Request $request, FieldGroup $fieldGroup): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'assignments' => 'sometimes|array',
            'assignments.*' => 'array',
            'assignments.*.assignable_type' => 'required_with:assignments|string',
            'assignments.*.module_scope' => 'nullable|string',
        ]);

        $assignments = $validated['assignments'] ?? null;
        unset($validated['assignments']);

        $fieldGroup->update($validated);

        if (is_array($assignments)) {
            $fieldGroup->assignments()->delete();
            foreach ($assignments as $assignment) {
                if (! is_array($assignment)) {
                    continue;
                }
                /** @var array<string, mixed> $assignment */
                $fieldGroup->assignments()->create($assignment);
            }
        }

        return $this->success($fieldGroup->load('assignments'), 'Field group updated successfully');
    }

    public function destroy(FieldGroup $fieldGroup): JsonResponse
    {
        $fieldGroup->delete();

        return $this->success(null, 'Field group deleted successfully');
    }
}
