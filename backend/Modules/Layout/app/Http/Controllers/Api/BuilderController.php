<?php

namespace Modules\Layout\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Models\Setting;
use Modules\Layout\Services\DynamicTagService;
use Modules\Publishing\Models\Content;

class BuilderController extends BaseApiController
{
    public function __construct(
        protected DynamicTagService $dynamicTagService
    ) {}

    /**
     * Get available dynamic data sources for the builder
     */
    public function dynamicSources(Request $request): JsonResponse
    {
        $contentId = $request->query('content_id');
        $contextRaw = $request->query('context', 'all'); // all | page | loop | site
        $context = is_string($contextRaw) ? $contextRaw : 'all';

        $sources = [];

        if (in_array($context, ['all', 'page'])) {
            $sources['page'] = [
                'label' => 'Page / Post',
                'items' => [
                    ['id' => 'post_title', 'label' => 'Post Title', 'tag' => '{{post_title}}', 'type' => 'text'],
                    ['id' => 'post_excerpt', 'label' => 'Post Excerpt', 'tag' => '{{post_excerpt}}', 'type' => 'text'],
                    ['id' => 'post_content', 'label' => 'Post Content', 'tag' => '{{post_content}}', 'type' => 'html'],
                    ['id' => 'post_date', 'label' => 'Post Date', 'tag' => '{{post_date}}', 'type' => 'date'],
                    ['id' => 'post_author', 'label' => 'Author Name', 'tag' => '{{post_author}}', 'type' => 'text'],
                    ['id' => 'post_author_avatar', 'label' => 'Author Avatar', 'tag' => '{{post_author_avatar}}', 'type' => 'image'],
                    ['id' => 'post_featured_image', 'label' => 'Featured Image', 'tag' => '{{post_featured_image}}', 'type' => 'image'],
                    ['id' => 'post_url', 'label' => 'Post URL', 'tag' => '{{post_url}}', 'type' => 'url'],
                    ['id' => 'post_category', 'label' => 'Category Name', 'tag' => '{{post_category}}', 'type' => 'text'],
                    ['id' => 'post_tags', 'label' => 'Tags', 'tag' => '{{post_tags}}', 'type' => 'text'],
                ],
            ];
        }

        // Loop context (for dynamic lists)
        if (in_array($context, ['all', 'loop'])) {
            $sources['loop'] = [
                'label' => 'Current Loop Item',
                'items' => [
                    ['id' => 'loop_title', 'label' => 'Item Title', 'tag' => '{{loop_title}}', 'type' => 'text'],
                    ['id' => 'loop_excerpt', 'label' => 'Item Excerpt', 'tag' => '{{loop_excerpt}}', 'type' => 'text'],
                    ['id' => 'loop_date', 'label' => 'Item Date', 'tag' => '{{loop_date}}', 'type' => 'date'],
                    ['id' => 'loop_author', 'label' => 'Item Author', 'tag' => '{{loop_author}}', 'type' => 'text'],
                    ['id' => 'loop_thumbnail', 'label' => 'Item Featured Image', 'tag' => '{{loop_thumbnail}}', 'type' => 'image'],
                    ['id' => 'loop_url', 'label' => 'Item Link', 'tag' => '{{loop_url}}', 'type' => 'url'],
                    ['id' => 'loop_category', 'label' => 'Item Category', 'tag' => '{{loop_category}}', 'type' => 'text'],
                    ['id' => 'loop_index', 'label' => 'Loop Index', 'tag' => '{{loop_index}}', 'type' => 'number'],
                ],
            ];
        }

        if (in_array($context, ['all', 'site'])) {
            $siteTitle = Setting::get('site_title', config('app.name'));
            $siteTagline = Setting::get('site_tagline', '');

            $sources['site'] = [
                'label' => 'Site Settings',
                'items' => [
                    ['id' => 'site_title', 'label' => 'Site Title', 'tag' => '{{site_title}}', 'type' => 'text', 'preview' => $siteTitle],
                    ['id' => 'site_tagline', 'label' => 'Site Tagline', 'tag' => '{{site_tagline}}', 'type' => 'text', 'preview' => $siteTagline],
                    ['id' => 'site_logo', 'label' => 'Site Logo', 'tag' => '{{site_logo}}', 'type' => 'image'],
                    ['id' => 'current_date', 'label' => 'Current Date', 'tag' => '{{current_date}}', 'type' => 'date', 'preview' => now()->format('Y-m-d')],
                    ['id' => 'current_year', 'label' => 'Current Year', 'tag' => '{{current_year}}', 'type' => 'text', 'preview' => (string) now()->year],
                ],
            ];
        }

        if (in_array($context, ['all', 'archive'])) {
            $sources['archive'] = [
                'label' => 'Archive',
                'items' => [
                    ['id' => 'archive_title', 'label' => 'Archive Title', 'tag' => '{{archive_title}}', 'type' => 'text'],
                    ['id' => 'archive_description', 'label' => 'Archive Description', 'tag' => '{{archive_description}}', 'type' => 'text'],
                    ['id' => 'archive_count', 'label' => 'Post Count', 'tag' => '{{archive_count}}', 'type' => 'number'],
                ],
            ];
        }

        if (in_array($context, ['all', 'user'])) {
            $sources['user'] = [
                'label' => 'Current User',
                'items' => [
                    ['id' => 'user_name', 'label' => 'User Name', 'tag' => '{{user_name}}', 'type' => 'text'],
                    ['id' => 'user_email', 'label' => 'User Email', 'tag' => '{{user_email}}', 'type' => 'text'],
                    ['id' => 'user_avatar', 'label' => 'User Avatar', 'tag' => '{{user_avatar}}', 'type' => 'image'],
                ],
            ];
        }

        return $this->success($sources, 'Dynamic sources retrieved successfully');
    }

    /**
     * Resolve dynamic tags to actual values for preview
     */
    public function resolveDynamic(Request $request): JsonResponse
    {
        $request->validate([
            'tags' => 'required|array',
            'content_id' => 'nullable|integer',
            'loop_item' => 'nullable|array',
        ]);

        $tagsRaw = $request->input('tags');
        $tags = is_array($tagsRaw) ? $tagsRaw : [];
        $contentId = $request->input('content_id');
        $loopItemRaw = $request->input('loop_item');
        /** @var array<string, mixed>|null $loopItem */
        $loopItem = is_array($loopItemRaw) ? $loopItemRaw : null;

        // Load content if provided
        $content = null;
        if ($contentId) {
            $content = Content::with(['author', 'category', 'tags'])->find($contentId);
            /** @var Content|null $content */
        }

        $resolved = [];

        foreach ($tags as $tag) {
            if (is_string($tag)) {
                $resolved[$tag] = $this->dynamicTagService->resolveTag($tag, $content, $loopItem);
            }
        }

        return $this->success($resolved, 'Tags resolved successfully');
    }
}
