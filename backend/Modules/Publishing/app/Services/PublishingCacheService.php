<?php

namespace Modules\Publishing\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\Publishing\Models\Content;

class PublishingCacheService
{
    /**
     * Clear all Jejakawan-related caches
     */
    public function clearAll(): void
    {
        $this->clearContentCaches();
        $this->clearSeoCaches();
        Cache::forget('cms_statistics');
    }

    /**
     * Clear content caches
     */
    public function clearContentCaches(?string $contentId = null): void
    {
        try {
            // Clear general list caches
            // Note: We use a wildcard approach if the driver supports tags, otherwise specific keys
            if ($this->tagsSupported()) {
                Cache::tags(['publishing', 'contents'])->flush();
            } else {
                // Manual key clearing for simple drivers
                Cache::forget('contents_published_list');
            }

            if ($contentId) {
                Cache::forget("content_detail_{$contentId}");
            }
        } catch (\Exception $e) {
            Log::warning('Failed to clear Jejakawan content cache: '.$e->getMessage());
        }
    }

    /**
     * Clear SEO related caches
     */
    public function clearSeoCaches(): void
    {
        try {
            if ($this->tagsSupported()) {
                Cache::tags(['publishing', 'seo'])->flush();
            } else {
                Cache::forget('cms_sitemap');
            }
        } catch (\Exception $e) {
            Log::warning('Failed to clear Jejakawan SEO cache: '.$e->getMessage());
        }
    }

    /**
     * Warm up important Jejakawan caches
     */
    public function warmUp(): int
    {
        $count = 0;
        try {
            // Pre-cache top 10 published contents
            $recentContents = Content::where('status', 'published')
                ->latest('published_at')
                ->limit(10)
                ->get();

            foreach ($recentContents as $content) {
                Cache::put("content_detail_{$content->id}", $content, now()->addHours(1));
                $count++;
            }
        } catch (\Exception $e) {
            Log::error('Failed to warm up Jejakawan cache: '.$e->getMessage());
        }

        return $count;
    }

    /**
     * Check if cache tags are supported
     */
    protected function tagsSupported(): bool
    {
        return in_array(config('cache.default'), ['redis', 'memcached']);
    }
}
