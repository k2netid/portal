<?php

namespace Modules\Content\Layout\Services;

use Modules\Content\Publishing\Models\Content;
use Modules\Core\System\Models\Setting;
use Modules\Core\System\Models\User;

class DynamicTagService
{
    /**
     * Resolve all dynamic tags in blocks array
     *
     * @param  array<int, array<string, mixed>>  $blocks  The blocks array with potential dynamic tags
     * @param  Content|null  $content  The content context for post/page tags
     * @param  array<string, mixed>|null  $loopItem  Loop item context if in a loop
     * @return array<int, array<string, mixed>> Blocks with resolved tags
     */
    public function resolveBlocks(array $blocks, ?Content $content = null, ?array $loopItem = null): array
    {
        return $this->processBlocks($blocks, $content, $loopItem);
    }

    /**
     * Recursively process blocks and resolve dynamic tags
     *
     * @param  array<int, array<string, mixed>>  $blocks
     * @param  array<string, mixed>|null  $loopItem
     * @return array<int, array<string, mixed>>
     */
    protected function processBlocks(array $blocks, ?Content $content, ?array $loopItem): array
    {
        foreach ($blocks as &$block) {
            // Process settings
            if (isset($block['settings']) && is_array($block['settings'])) {
                foreach ($block['settings'] as $key => &$value) {
                    if (is_string($value) && str_starts_with($value, '@dynamic:')) {
                        $tag = str_replace('@dynamic:', '', $value);
                        $value = $this->resolveTag($tag, $content, $loopItem);
                    }
                }
            }

            // Process children recursively
            if (isset($block['children']) && is_array($block['children'])) {
                /** @var array<int, array<string, mixed>> $children */
                $children = $block['children'];
                $block['children'] = $this->processBlocks($children, $content, $loopItem);
            }
        }

        return $blocks;
    }

    /**
     * Resolve a single dynamic tag to its value
     *
     * @param  string  $tag  The tag like "{{post_title}}"
     * @param  Content|null  $content  Content context
     * @param  array<string, mixed>|null  $loopItem  Loop item context
     * @return string The resolved value
     */
    public function resolveTag(string $tag, ?Content $content = null, ?array $loopItem = null): string
    {
        // Strip {{ and }}
        $key = trim(str_replace(['{{', '}}'], '', $tag));

        // Post/Page tags
        if ($content && str_starts_with($key, 'post_')) {
            $author = $content->author;

            return match ($key) {
                'post_title' => (string) ($content->title ?? ''),
                'post_excerpt' => (string) ($content->excerpt ?? ''),
                'post_content' => (string) ($content->body ?? ''),
                'post_date' => ($content->published_at ?? $content->created_at ?? now())->format('M d, Y'),
                'post_author' => $author instanceof User ? $author->name : '',
                'post_author_avatar' => $author instanceof User ? (string) ($author->getAttribute('avatar') ?? '') : '',
                'post_featured_image' => (string) ($content->getAttribute('featured_image') ?? ''),
                'post_url' => url('/'.$content->slug),
                'post_category' => $content->category ? (string) $content->category->name : '',
                'post_tags' => (string) ($content->tags->pluck('name')->join(', ') ?? ''),
                default => ''
            };
        }

        // Loop item tags
        if ($loopItem && str_starts_with($key, 'loop_')) {
            return match ($key) {
                'loop_title' => (string) ($loopItem['title'] ?? ''),
                'loop_excerpt' => (string) ($loopItem['excerpt'] ?? ''),
                'loop_date' => (string) ($loopItem['date'] ?? ''),
                'loop_author' => (string) ($loopItem['author'] ?? ''),
                'loop_thumbnail' => (string) ($loopItem['thumbnail'] ?? $loopItem['featured_image'] ?? ''),
                'loop_url' => (string) ($loopItem['url'] ?? ''),
                'loop_category' => (string) ($loopItem['category'] ?? ''),
                'loop_index' => (string) ($loopItem['index'] ?? '0'),
                default => ''
            };
        }

        // Site tags
        if (str_starts_with($key, 'site_') || str_starts_with($key, 'current_')) {
            return match ($key) {
                'site_title' => (string) Setting::get('site_title', (string) config('app.name')),
                'site_tagline' => (string) Setting::get('site_tagline', ''),
                'site_logo' => (string) Setting::get('site_logo', ''),
                'current_date' => now()->format('M d, Y'),
                'current_year' => (string) now()->year,
                default => ''
            };
        }

        // Archive tags
        if (str_starts_with($key, 'archive_')) {
            return match ($key) {
                'archive_title' => 'Archive',
                'archive_description' => '',
                'archive_count' => '0',
                default => ''
            };
        }

        // User tags
        if (str_starts_with($key, 'user_')) {
            $user = auth()->user();
            if (! $user instanceof User) {
                return '';
            }

            return match ($key) {
                'user_name' => (string) ($user->name ?? ''),
                'user_email' => (string) ($user->email ?? ''),
                'user_avatar' => (string) $user->getAttribute('avatar') ?? '',
                default => ''
            };
        }

        return '';
    }
}
