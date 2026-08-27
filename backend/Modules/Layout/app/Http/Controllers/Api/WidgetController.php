<?php

namespace Modules\Layout\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\System\Contracts\LayoutRegistryInterface;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Layout\Models\Widget;
use Modules\Layout\Services\PublicWidgetPresenter;

class WidgetController extends BaseApiController
{
    public function __construct(
        protected LayoutRegistryInterface $registry,
        protected PublicWidgetPresenter $publicWidgets,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Widget::query();

        if ($request->has('module_scope')) {
            $query->where('module_scope', $request->input('module_scope'));
        }

        if ($request->has('location')) {
            $query->where('location', $request->input('location'));
        }

        $widgets = $query->orderBy('sort_order')->get()
            ->map(fn (Widget $widget) => $this->presentWidget($widget));

        return $this->success($widgets, 'Widgets retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required_without:title|string|max:255',
            'title' => 'required_without:name|string|max:255',
            'type' => 'required|string',
            'location' => 'nullable|string',
            'settings' => 'nullable|array',
            'content' => 'nullable|string',
            'module_scope' => 'nullable|string',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ]);

        $name = $validated['name'] ?? $validated['title'] ?? '';
        $settings = is_array($validated['settings'] ?? null) ? $validated['settings'] : [];
        if (array_key_exists('content', $validated)) {
            $settings['content'] = $validated['content'];
        }

        $widget = Widget::create([
            'name' => $name,
            'type' => $validated['type'],
            'location' => $validated['location'] ?? null,
            'settings' => $settings,
            'module_scope' => $validated['module_scope'] ?? 'publishing',
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return $this->success($this->presentWidget($widget), 'Widget created successfully', 201);
    }

    public function show(Widget $widget): JsonResponse
    {
        return $this->success($this->presentWidget($widget), 'Widget retrieved successfully');
    }

    public function update(Request $request, Widget $widget): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'title' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|string',
            'location' => 'nullable|string',
            'settings' => 'nullable|array',
            'content' => 'nullable|string',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ]);

        $payload = [];
        if (isset($validated['name']) || isset($validated['title'])) {
            $payload['name'] = $validated['name'] ?? $validated['title'];
        }
        if (isset($validated['type'])) {
            $payload['type'] = $validated['type'];
        }
        if (array_key_exists('location', $validated)) {
            $payload['location'] = $validated['location'];
        }
        if (isset($validated['sort_order'])) {
            $payload['sort_order'] = $validated['sort_order'];
        }
        if (array_key_exists('is_active', $validated)) {
            $payload['is_active'] = $validated['is_active'];
        }

        $settings = is_array($widget->settings) ? $widget->settings : [];
        if (isset($validated['settings']) && is_array($validated['settings'])) {
            $settings = $validated['settings'];
        }
        if (array_key_exists('content', $validated)) {
            $settings['content'] = $validated['content'];
        }
        $payload['settings'] = $settings;

        $widget->update($payload);

        $fresh = $widget->fresh();
        if ($fresh === null) {
            return $this->error('Widget not found after update', 404);
        }

        return $this->success($this->presentWidget($fresh), 'Widget updated successfully');
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
        return $this->success(
            $this->publicWidgets->forLocation($location, $request),
            'Widgets retrieved successfully'
        );
    }

    /**
     * FE console uses title/content; DB columns are name/settings.
     *
     * @return array<string, mixed>
     */
    private function presentWidget(Widget $widget): array
    {
        $settings = is_array($widget->settings) ? $widget->settings : [];

        return [
            'id' => $widget->id,
            'name' => $widget->name,
            'title' => $widget->name,
            'type' => $widget->type,
            'location' => $widget->location,
            'settings' => $settings,
            'content' => is_string($settings['content'] ?? null) ? $settings['content'] : '',
            'module_scope' => $widget->module_scope,
            'sort_order' => $widget->sort_order,
            'is_active' => $widget->is_active,
            'created_at' => $widget->created_at,
            'updated_at' => $widget->updated_at,
        ];
    }
}
