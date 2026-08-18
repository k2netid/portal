<?php

namespace Modules\Content\Publishing\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Modules\Content\Publishing\Models\Content;
use Modules\Content\Publishing\Services\SeoService;
use Modules\Core\System\Http\Controllers\BaseApiController;

class SeoController extends BaseApiController
{
    public function __construct(protected SeoService $seoService) {}

    public function stats(): JsonResponse
    {
        return $this->success([
            'total_indexed' => Content::where('status', 'published')->count(),
            'average_score' => 85, // Placeholder
        ], 'SEO stats retrieved successfully');
    }

    public function checkUrl(Request $request): JsonResponse
    {
        $url = $request->input('url');

        return $this->success([
            'url' => $url,
            'is_available' => true,
        ], 'URL check completed');
    }

    public function generateSitemap(): JsonResponse
    {
        // Sitemap is generated on-the-fly by SitemapController
        return $this->success([
            'url' => url('/sitemap.xml'),
        ], 'Sitemap is available at /sitemap.xml');
    }

    public function getRobotsTxt(): JsonResponse
    {
        $path = public_path('robots.txt');

        if (File::exists($path)) {
            $content = File::get($path);
        } else {
            $content = "User-agent: *\nAllow: /\n\nSitemap: ".url('/sitemap.xml');
        }

        return $this->success([
            'content' => $content,
        ], 'Robots.txt retrieved successfully');
    }

    public function updateRobotsTxt(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $content = is_string($validated['content']) ? $validated['content'] : '';
        $path = public_path('robots.txt');
        File::put($path, $content);

        return $this->success([
            'content' => $content,
        ], 'Robots.txt updated successfully');
    }

    /**
     * Analyze content SEO using the SeoService.
     */
    public function analyzeContent(Content $content): JsonResponse
    {
        $analysis = $this->seoService->analyze($content);

        return $this->success($analysis, 'Content SEO analysis completed');
    }

    /**
     * Generate Schema markup using the SeoService.
     */
    public function generateSchema(Content $content): JsonResponse
    {
        $schemaType = $content->type === 'post' ? 'BlogPosting' : 'Article';
        $schema = $this->seoService->generateSchema($content, $schemaType);

        return $this->success($schema, 'Schema generated successfully');
    }
}
