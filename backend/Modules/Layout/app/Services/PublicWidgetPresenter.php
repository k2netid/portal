<?php

declare(strict_types=1);

namespace Modules\Layout\Services;

use Illuminate\Http\Request;
use Modules\Core\System\Models\Extension;
use Modules\Layout\Models\Widget;
use Modules\Library\Models\Category;
use Modules\Publishing\Models\Content;

/**
 * Public theme widgets: registry-gated, enriched for visitor runtime.
 */
class PublicWidgetPresenter
{
    /**
     * @return list<array<string, mixed>>
     */
    public function forLocation(string $location, Request $request): array
    {
        if (! Extension::isProductActive('layout')) {
            return [];
        }

        $scope = $this->resolveScope($request);

        $widgets = Widget::query()
            ->where('location', $location)
            ->where('module_scope', $scope)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $out = [];
        foreach ($widgets as $widget) {
            $out[] = $this->present($widget);
        }

        return $out;
    }

    /**
     * @return array<string, mixed>
     */
    public function present(Widget $widget): array
    {
        $settings = is_array($widget->settings) ? $widget->settings : [];
        $body = is_string($settings['content'] ?? null) ? $settings['content'] : '';
        if ($body === '' && is_string($settings['text'] ?? null)) {
            $body = $settings['text'];
        }

        $payload = [
            'id' => $widget->id,
            'name' => $widget->name,
            'title' => $widget->name,
            'type' => $widget->type,
            'location' => $widget->location,
            'settings' => $settings,
            'content' => $body,
            'module_scope' => $widget->module_scope,
            'sort_order' => $widget->sort_order,
            'is_active' => $widget->is_active,
            'items' => [],
        ];

        if (in_array($widget->type, ['recent_posts', 'content_list'], true)) {
            $payload['items'] = $this->recentPosts($settings);
        }

        if ($widget->type === 'categories') {
            $payload['items'] = $this->categories($settings);
        }

        return $payload;
    }

    private function resolveScope(Request $request): string
    {
        $raw = $request->input('module_scope', $request->input('module', 'publishing'));
        $scope = is_string($raw) && $raw !== '' ? $raw : 'publishing';
        if (strcasecmp($scope, 'Jejakawan') === 0) {
            return 'publishing';
        }

        return $scope;
    }

    /**
     * @param  array<string, mixed>  $settings
     * @return list<array{id: string, title: string, slug: string, excerpt?: string|null}>
     */
    private function recentPosts(array $settings): array
    {
        if (! Extension::isProductActive('publishing') || ! class_exists(Content::class)) {
            return [];
        }

        $limit = isset($settings['count']) && is_numeric($settings['count'])
            ? max(1, min(12, (int) $settings['count']))
            : 5;

        return Content::query()
            ->where('status', 'published')
            ->where('type', 'post')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->limit($limit)
            ->get(['id', 'title', 'slug', 'excerpt'])
            ->map(static fn ($row): array => [
                'id' => (string) $row->id,
                'title' => (string) $row->title,
                'slug' => (string) $row->slug,
                'excerpt' => $row->excerpt,
            ])
            ->all();
    }

    /**
     * @param  array<string, mixed>  $settings
     * @return list<array{id: string, name: string, slug: string}>
     */
    private function categories(array $settings): array
    {
        if (! Extension::isProductActive('library') || ! class_exists(Category::class)) {
            return [];
        }

        $limit = isset($settings['count']) && is_numeric($settings['count'])
            ? max(1, min(24, (int) $settings['count']))
            : 12;

        return Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->limit($limit)
            ->get(['id', 'name', 'slug'])
            ->map(static fn ($row): array => [
                'id' => (string) $row->id,
                'name' => (string) $row->name,
                'slug' => (string) $row->slug,
            ])
            ->all();
    }
}
