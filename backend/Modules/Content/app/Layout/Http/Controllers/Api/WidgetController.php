<?php

namespace Modules\Content\Layout\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Content\Layout\Models\Widget;
use Modules\Core\System\Contracts\LayoutRegistryInterface;
use Modules\Core\System\Http\Controllers\BaseApiController;

class WidgetController extends BaseApiController
{
    public function __construct(protected LayoutRegistryInterface $registry) {}

    public function index(Request $request): JsonResponse
    {
        $query = Widget::query();

        if ($request->has('module_scope')) {
            $query->where('module_scope', $request->input('module_scope'));
        }

        if ($request->has('location')) {
            $query->where('location', $request->input('location'));
        }

        $widgets = $query->orderBy('sort_order')->get();

        return $this->success($widgets, 'Widgets retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'location' => 'nullable|string',
            'settings' => 'nullable|array',
            'module_scope' => 'nullable|string',
            'sort_order' => 'integer',
        ]);

        $widget = Widget::create($validated);

        return $this->success($widget, 'Widget created successfully', 201);
    }

    public function show(Widget $widget): JsonResponse
    {
        return $this->success($widget, 'Widget retrieved successfully');
    }

    public function update(Request $request, Widget $widget): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|string',
            'location' => 'nullable|string',
            'settings' => 'nullable|array',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ]);

        $widget->update($validated);

        return $this->success($widget, 'Widget updated successfully');
    }

    public function destroy(Widget $widget): JsonResponse
    {
        $widget->delete();

        return $this->success(null, 'Widget deleted successfully');
    }

    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'widgets' => 'required|array',
            'widgets.*.id' => 'required|exists:lay_widgets,id',
            'widgets.*.sort_order' => 'required|integer',
        ]);

        /** @var list<array{id: string, sort_order: int}> $widgets */
        $widgets = $request->input('widgets');

        foreach ($widgets as $widgetData) {
            Widget::where('id', $widgetData['id'])
                ->update(['sort_order' => $widgetData['sort_order']]);
        }

        return $this->success(null, 'Widgets reordered successfully');
    }

    public function locations(Request $request): JsonResponse
    {
        $scopeRaw = $request->input('module_scope', 'publishing');
        $scope = is_string($scopeRaw) ? $scopeRaw : 'publishing';
        $locations = $this->registry->getWidgetLocations($scope);

        $formatted = array_map(fn ($loc) => ['id' => $loc, 'name' => ucwords(str_replace(['-', '_'], ' ', $loc))], $locations);

        return $this->success($formatted, 'Widget locations retrieved successfully');
    }

    public function getByLocation(string $location, Request $request): JsonResponse
    {
        $scopeRaw = $request->input('module_scope', 'publishing');
        $scope = is_string($scopeRaw) ? $scopeRaw : 'publishing';

        $widgets = Widget::where('location', $location)
            ->where('module_scope', $scope)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return $this->success($widgets, 'Widgets retrieved successfully');
    }
}
