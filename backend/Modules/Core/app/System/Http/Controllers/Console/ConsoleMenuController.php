<?php

declare(strict_types=1);

namespace Modules\Core\System\Http\Controllers\Console;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Models\ConsoleMenu;

class ConsoleMenuController extends BaseApiController
{
    /**
     * List all console menus structured by root parent & children.
     */
    public function index(Request $request): JsonResponse
    {
        // Auto-seed defaults if table is empty
        if (ConsoleMenu::count() === 0) {
            ConsoleMenu::seedDefaults();
        }

        $query = ConsoleMenu::with(['children' => function ($q) {
            $q->orderBy('order', 'asc');
        }])->whereNull('parent_id')->orderBy('order', 'asc');

        if ($request->has('group')) {
            $groupRaw = $request->input('group');
            if (is_string($groupRaw) && $groupRaw !== '') {
                $query->where('group_slug', $groupRaw);
            }
        }

        $menus = $query->get();

        return $this->success($menus, 'Console menus retrieved successfully');
    }

    /**
     * Store a newly created console menu item.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|uuid|exists:sys_console_menus,id',
            'group_slug' => 'required|string|max:64',
            'name' => 'required|string|max:128',
            'label_key' => 'nullable|string|max:128',
            'route_name' => 'nullable|string|max:128',
            'url' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:64',
            'permission' => 'nullable|string|max:128',
            'role' => 'nullable|string|max:64',
            'extension_slug' => 'nullable|string|max:64',
            'badge_text' => 'nullable|string|max:32',
            'badge_variant' => 'nullable|string|in:default,primary,amber,emerald,rose',
            'order' => 'nullable|integer',
            'is_visible' => 'nullable|boolean',
            'meta' => 'nullable|array',
        ]);

        if (! isset($validated['order'])) {
            $maxOrderRaw = ConsoleMenu::where('parent_id', $validated['parent_id'] ?? null)->max('order') ?? 0;
            $maxOrder = is_numeric($maxOrderRaw) ? (int) $maxOrderRaw : 0;
            $validated['order'] = $maxOrder + 1;
        }

        $menu = ConsoleMenu::create($validated);

        return $this->success($menu->load('children'), 'Menu item created successfully', 201);
    }

    /**
     * Show a single console menu item.
     */
    public function show(string $id): JsonResponse
    {
        $menu = ConsoleMenu::with('children')->findOrFail($id);

        return $this->success($menu, 'Menu item retrieved successfully');
    }

    /**
     * Update an existing console menu item.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $menu = ConsoleMenu::findOrFail($id);

        $validated = $request->validate([
            'parent_id' => 'nullable|uuid|exists:sys_console_menus,id',
            'group_slug' => 'sometimes|required|string|max:64',
            'name' => 'sometimes|required|string|max:128',
            'label_key' => 'nullable|string|max:128',
            'route_name' => 'nullable|string|max:128',
            'url' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:64',
            'permission' => 'nullable|string|max:128',
            'role' => 'nullable|string|max:64',
            'extension_slug' => 'nullable|string|max:64',
            'badge_text' => 'nullable|string|max:32',
            'badge_variant' => 'nullable|string|in:default,primary,amber,emerald,rose',
            'order' => 'nullable|integer',
            'is_visible' => 'nullable|boolean',
            'meta' => 'nullable|array',
        ]);

        $menu->update($validated);

        return $this->success($menu->load('children'), 'Menu item updated successfully');
    }

    /**
     * Delete a console menu item (and cascade its children).
     */
    public function destroy(string $id): JsonResponse
    {
        $menu = ConsoleMenu::findOrFail($id);
        $menu->delete();

        return $this->success(null, 'Menu item deleted successfully');
    }

    /**
     * Batch reorder and re-parent menu items (Drag & Drop Tree synchronization).
     */
    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|uuid|exists:sys_console_menus,id',
            'items.*.parent_id' => 'nullable|uuid|exists:sys_console_menus,id',
            'items.*.order' => 'required|integer',
            'items.*.group_slug' => 'nullable|string|max:64',
        ]);

        DB::transaction(function () use ($validated): void {
            foreach ($validated['items'] as $item) {
                $payload = [
                    'parent_id' => $item['parent_id'] ?? null,
                    'order' => $item['order'],
                ];
                if (isset($item['group_slug'])) {
                    $payload['group_slug'] = $item['group_slug'];
                }
                ConsoleMenu::where('id', $item['id'])->update($payload);
            }
        });

        $menus = ConsoleMenu::with(['children' => function ($q) {
            $q->orderBy('order', 'asc');
        }])->whereNull('parent_id')->orderBy('order', 'asc')->get();

        return $this->success($menus, 'Menu structure reordered successfully');
    }

    /**
     * Reset all console menus to system factory defaults.
     */
    public function resetDefaults(): JsonResponse
    {
        ConsoleMenu::seedDefaults(true);

        $menus = ConsoleMenu::with(['children' => function ($q) {
            $q->orderBy('order', 'asc');
        }])->whereNull('parent_id')->orderBy('order', 'asc')->get();

        return $this->success($menus, 'Console navigation reset to system defaults successfully');
    }
}
