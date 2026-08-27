<?php

namespace Modules\Layout\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Core\System\Contracts\LayoutRegistryInterface;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Models\Extension;
use Modules\Core\System\Support\SqlLikeEscape;
use Modules\Layout\Models\Menu;
use Modules\Layout\Models\MenuItem;
use Modules\Layout\Services\MenuUsageService;
use Modules\Layout\Support\MenuItemUrlValidator;

class MenuController extends BaseApiController
{
    public function __construct(
        protected LayoutRegistryInterface $registry,
        protected MenuUsageService $menuUsageService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Menu::withCount('items');

        if ($request->has('module_scope')) {
            $query->where('module_scope', $request->input('module_scope'));
        }

        if ($request->has('trashed')) {
            $trashed = $request->input('trashed');
            if ($trashed === 'only') {
                $query->onlyTrashed();
            } elseif ($trashed === 'with') {
                $query->withTrashed();
            }
        }

        if ($request->filled('search')) {
            $searchRaw = $request->input('search');
            $search = is_string($searchRaw) ? trim($searchRaw) : '';
            if ($search !== '') {
                SqlLikeEscape::whereContainsAny(
                    $query,
                    ['name', 'slug'],
                    mb_strtolower($search, 'UTF-8')
                );
            }
        }

        $perPage = max(1, min(200, $request->integer('per_page', 15)));
        $menus = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Menus retrieved successfully',
            'data' => $menus,
            'meta' => [
                'trashed_count' => Menu::onlyTrashed()->count(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $scopeRaw = $request->input('module_scope', 'publishing');
        $scope = is_string($scopeRaw) ? $scopeRaw : 'publishing';
        $this->registry->getMenuLocations($scope);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:lay_menus,slug',
            'location' => 'nullable|string',
            'module_scope' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $menu = Menu::create($validated);

        return $this->success($menu, 'Menu created successfully', 201);
    }

    public function show(Menu $menu): JsonResponse
    {
        return $this->success($menu->load('parentItems.children'), 'Menu retrieved successfully');
    }

    public function update(Request $request, Menu $menu): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|unique:lay_menus,slug,'.$menu->id,
            'location' => 'nullable|string',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $menu->update($validated);

        $this->forgetMenuLocationCache($menu);

        return $this->success($menu, 'Menu updated successfully');
    }

    public function usage(Menu $menu): JsonResponse
    {
        return $this->success(
            $this->menuUsageService->analyze($menu),
            'Menu usage retrieved successfully'
        );
    }

    public function destroy(Menu $menu): JsonResponse
    {
        $force = request()->boolean('force');
        $analysis = $this->menuUsageService->analyze($menu);

        if ($force && $analysis['is_in_use']) {
            return $this->error(
                'Menu cannot be permanently deleted while it is assigned to a theme or served on the public site. Remove assignments in Theme Customizer first.',
                422,
                ['usage' => $analysis]
            );
        }

        $this->forgetMenuLocationCache($menu);
        $menu->delete();

        return $this->success(
            ['usage' => $analysis],
            $analysis['is_in_use']
                ? 'Menu moved to trash. It is no longer served on the public site.'
                : 'Menu deleted successfully'
        );
    }

    public function forceDestroy(Menu $menu): JsonResponse
    {
        $analysis = $this->menuUsageService->analyze($menu);

        if ($analysis['is_in_use']) {
            return $this->error(
                'Menu cannot be permanently deleted while it is assigned to a theme or served on the public site. Remove assignments in Theme Customizer first.',
                422,
                ['usage' => $analysis]
            );
        }

        $this->forgetMenuLocationCache($menu);
        $menu->forceDelete();

        return $this->success(null, 'Menu permanently deleted');
    }

    public function restore(Menu $menu): JsonResponse
    {
        if (! $menu->trashed()) {
            return $this->error('Menu is not in trash.', 422);
        }

        $menu->restore();
        $this->forgetMenuLocationCache($menu);

        return $this->success($menu->fresh(), 'Menu restored successfully');
    }

    public function addItem(Request $request, Menu $menu): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'url' => MenuItemUrlValidator::validationRules(),
            'type' => 'required|string',
            'target_id' => 'nullable',
            'target_type' => 'nullable|string',
            'parent_id' => 'nullable|exists:lay_menu_items,id',
            'icon' => 'nullable|string',
            'css_class' => 'nullable|string',
            'sort_order' => 'integer',
            'open_in_new_tab' => 'boolean',
            'metadata' => 'nullable|array',
        ]);

        $item = $menu->items()->create($validated);

        $this->forgetMenuLocationCache($menu);

        return $this->success($item, 'Menu item added successfully', 201);
    }

    public function listItems(Menu $menu): JsonResponse
    {
        $items = $menu->items()->orderBy('sort_order')->get();

        return $this->success($items, 'Menu items retrieved successfully');
    }

    public function updateItem(Request $request, Menu $menu, MenuItem $item): JsonResponse
    {
        if ($item->menu_id !== $menu->id) {
            return $this->notFound('Menu item not found');
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'url' => MenuItemUrlValidator::validationRules(),
            'type' => 'sometimes|required|string|max:64',
            'target_id' => 'nullable',
            'target_type' => 'nullable|string|max:128',
            'parent_id' => 'nullable|exists:lay_menu_items,id',
            'icon' => 'nullable|string|max:128',
            'css_class' => 'nullable|string|max:128',
            'sort_order' => 'sometimes|integer',
            'open_in_new_tab' => 'boolean',
            'metadata' => 'nullable|array',
        ]);

        $parentId = $validated['parent_id'] ?? null;
        if (is_string($parentId) && $parentId !== '') {
            $parent = MenuItem::query()
                ->where('id', $parentId)
                ->where('menu_id', $menu->id)
                ->first();
            if (! $parent) {
                return $this->error('Parent item must belong to the same menu.', 422);
            }
        }

        $item->update($validated);
        $this->forgetMenuLocationCache($menu);

        return $this->success($item->fresh(), 'Menu item updated successfully');
    }

    public function deleteItem(Menu $menu, MenuItem $item): JsonResponse
    {
        if ($item->menu_id !== $menu->id) {
            return $this->notFound('Menu item not found');
        }

        $item->delete();
        $this->forgetMenuLocationCache($menu);

        return $this->success(null, 'Menu item deleted successfully');
    }

    public function reorderItems(Request $request, Menu $menu): JsonResponse
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:lay_menu_items,id',
            'items.*.sort_order' => 'required|integer',
            'items.*.parent_id' => 'nullable|exists:lay_menu_items,id',
        ]);

        /** @var list<array{id: string, sort_order: int, parent_id?: string|null}> $items */
        $items = $request->input('items');

        $ids = collect($items)->pluck('id')->filter()->values();
        $owned = MenuItem::query()->where('menu_id', $menu->id)->whereIn('id', $ids)->count();
        if ($owned !== $ids->count()) {
            return $this->error('One or more items do not belong to this menu.', 422);
        }

        $keptIds = [];

        DB::transaction(function () use ($items, $menu, &$keptIds): void {
            foreach ($items as $itemData) {
                $updated = MenuItem::where('id', $itemData['id'])
                    ->where('menu_id', $menu->id)
                    ->update([
                        'sort_order' => $itemData['sort_order'],
                        'parent_id' => $itemData['parent_id'] ?? null,
                    ]);

                $keptIds[] = $itemData['id'];
            }

            if ($keptIds !== []) {
                MenuItem::where('menu_id', $menu->id)
                    ->whereNotIn('id', $keptIds)
                    ->delete();
            }
        });

        $this->forgetMenuLocationCache($menu);

        return $this->success(null, 'Menu items reordered successfully');
    }

    public function getByLocation(string $location): JsonResponse
    {
        if (! Extension::isProductActive('layout')) {
            return $this->success(null, 'No active menu found for this location');
        }

        $cacheKey = "menu_location_{$location}";

        $menu = Cache::remember($cacheKey, 3600, fn () => Menu::query()
            ->where('location', $location)
            ->where('is_active', true)
            ->orderByDesc('updated_at')
            ->with(['parentItems.children'])
            ->first());

        if (! $menu) {
            return $this->success(null, 'No active menu found for this location');
        }

        return $this->success($menu, 'Menu retrieved successfully');
    }

    /**
     * Menu location options from LayoutRegistry (P3-3a stand-in for theme locations).
     */
    public function locations(Request $request): JsonResponse
    {
        $scopeRaw = $request->input('module', 'publishing');
        $scope = is_string($scopeRaw) ? $scopeRaw : 'publishing';
        $keys = $this->registry->getMenuLocations($scope);

        $labels = [];
        foreach ($keys as $key) {
            $labels[$key] = ucfirst(str_replace('_', ' ', $key));
        }

        return $this->success($labels, 'Menu locations retrieved successfully');
    }

    private function forgetMenuLocationCache(Menu $menu): void
    {
        if ($menu->location) {
            Cache::forget("menu_location_{$menu->location}");
        }
    }
}
