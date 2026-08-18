<?php

namespace Modules\Content\Library\Services;

use Illuminate\Support\Facades\Cache;
use Modules\Content\Library\Models\Category;
use Modules\Content\Publishing\Models\Content;

class LibraryCacheService
{
    /**
     * Clear all Jejakawan-related caches
     */
    public function clearAll(): void
    {
        $this->clearContentCaches();
        $this->clearCategoryCaches();
    }

    /**
     * Clear content-related caches
     */
    public function clearContentCaches(int|string|null $contentId = null): void
    {
        Cache::forget('contents_list');
        Cache::forget('contents_published');

        if ($contentId) {
            Cache::forget("content_{$contentId}");
            Cache::forget("content_slug_{$contentId}");
        }

        Cache::forget('sitemap_index');
        Cache::forget('sitemap_pages');
        Cache::forget('sitemap_posts');
        Cache::forget('sitemap_categories');
    }

    /**
     * Clear category-related caches
     */
    public function clearCategoryCaches(int|string|null $categoryId = null): void
    {
        Cache::forget('categories_list');
        Cache::forget('categories_tree');
        Cache::forget('categories_flat');

        if ($categoryId) {
            Cache::forget("category_{$categoryId}");
        }
    }

    /**
     * Clear SEO-related caches
     */
    public function clearSeoCaches(): void
    {
        Cache::forget('sitemap_index');
        Cache::forget('sitemap_pages');
        Cache::forget('sitemap_posts');
        Cache::forget('sitemap_categories');
        Cache::forget('robots_txt');
    }

    /**
     * Warm up Jejakawan caches
     */
    public function warmUp(): int
    {
        $count = 0;

        // Warm categories
        $categories = Category::orderBy('sort_order')->get();
        Cache::put('categories:list', $categories, 86400);
        $count += count($categories);

        // Warm popular content
        $popularContent = Content::where('status', 'published')
            ->orderBy('views', 'desc')
            ->limit(50)
            ->get();

        foreach ($popularContent as $content) {
            Cache::put("content:{$content->id}", $content, 3600);
            $count++;
        }

        return $count;
    }
}
