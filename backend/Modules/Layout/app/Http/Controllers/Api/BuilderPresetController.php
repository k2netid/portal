<?php

namespace Modules\Layout\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Layout\Models\BuilderPreset;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Models\User;

class BuilderPresetController extends BaseApiController
{
    /**
     * Get presets for a module type.
     * Returns both system presets and user's own presets.
     */
    public function index(Request $request): JsonResponse
    {
        $typeRaw = $request->query('type');
        $type = is_string($typeRaw) ? $typeRaw : null;

        $user = $request->user();
        /** @var User|null $user */
        $userId = $user ? (string) $user->id : '0';

        $query = BuilderPreset::query();

        if ($type) {
            $query->ofType($type);
        }

        // Get system presets + user's presets
        $presets = $query->where(function ($q) use ($userId) {
            $q->where('is_system', true)
                ->orWhere('user_id', $userId);
        })
            ->orderBy('is_system', 'desc') // System first
            ->orderBy('name')
            ->get();

        return $this->success($presets);
    }

    /**
     * Store a new preset.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        $validated = $request->validate([
            'type' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'settings' => 'required|array',
        ]);

        $preset = BuilderPreset::create([
            'user_id' => $user->id,
            'type' => $validated['type'],
            'name' => $validated['name'],
            'settings' => $validated['settings'],
            'is_system' => false,
        ]);

        return $this->success($preset, 'Preset saved successfully', 201);
    }

    /**
     * Update an existing preset.
     */
    public function update(Request $request, BuilderPreset $builderPreset): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        // Only allow updating own presets (not system presets unless admin)
        if ($builderPreset->is_system && ! $user->hasRole('admin')) {
            return $this->forbidden('Cannot modify system presets');
        }

        if (! $builderPreset->is_system && $builderPreset->user_id !== $user->id) {
            return $this->unauthorized();
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'settings' => 'sometimes|array',
        ]);

        $builderPreset->update($validated);

        return $this->success($builderPreset, 'Preset updated successfully');
    }

    /**
     * Delete a preset.
     */
    public function destroy(Request $request, BuilderPreset $builderPreset): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        // Only allow deleting own presets
        if ($builderPreset->is_system && ! $user->hasRole('admin')) {
            return $this->forbidden('Cannot delete system presets');
        }

        if (! $builderPreset->is_system && $builderPreset->user_id !== $user->id) {
            return $this->unauthorized();
        }

        $builderPreset->delete();

        return $this->success(null, 'Preset deleted successfully');
    }
}
