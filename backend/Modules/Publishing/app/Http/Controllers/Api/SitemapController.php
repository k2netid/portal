<?php

namespace Modules\Publishing\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Modules\Library\Models\Category;
use Modules\Publishing\Models\Content;

class SitemapController extends Controller
{
    public function index(): \Illuminate\Http\Response
    {
        /** @var \Illuminate\Http\Response $response */
        $response = Cache::remember('sitemap_index', now()->addHours(24), function () {
            $sitemap = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
            $sitemap .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
            $sitemap .= '  <sitemap>'."\n";
            $sitemap .= '    <loc>'.url('/sitemap.xml/pages').'</loc>'."\n";
            $sitemap .= '  </sitemap>'."\n";
            $sitemap .= '  <sitemap>'."\n";
            $sitemap .= '    <loc>'.url('/sitemap.xml/posts').'</loc>'."\n";
            $sitemap .= '  </sitemap>'."\n";
            $sitemap .= '  <sitemap>'."\n";
            $sitemap .= '    <loc>'.url('/sitemap.xml/categories').'</loc>'."\n";
            $sitemap .= '  </sitemap>'."\n";
            $sitemap .= '</sitemapindex>';

            return Response::make($sitemap, 200, [
                'Content-Type' => 'application/xml',
            ]);
        });

        return $response;
    }

    public function pages(): \Illuminate\Http\Response
    {
        /** @var \Illuminate\Http\Response $response */
        $response = Cache::remember('sitemap_pages', now()->addHours(12), function (): \Illuminate\Http\Response {
            $pages = Content::where('type', 'page')
                ->where('status', 'published')
                ->where(function ($q): void {
                    $q->whereNull('published_at')
                        ->orWhere('published_at', '<=', now());
                })
                ->latest('published_at')
                ->get();

            return $this->generateSitemap($pages);
        });

        return $response;
    }

    public function posts(): \Illuminate\Http\Response
    {
        /** @var \Illuminate\Http\Response $response */
        $response = Cache::remember('sitemap_posts', now()->addHours(12), function (): \Illuminate\Http\Response {
            $posts = Content::where('type', 'post')
                ->where('status', 'published')
                ->where(function ($q): void {
                    $q->whereNull('published_at')
                        ->orWhere('published_at', '<=', now());
                })
                ->latest('published_at')
                ->get();

            return $this->generateSitemap($posts);
        });

        return $response;
    }

    public function categories(): \Illuminate\Http\Response
    {
        /** @var \Illuminate\Http\Response $response */
        $response = Cache::remember('sitemap_categories', now()->addHours(24), function (): \Illuminate\Http\Response {
            $categories = Category::where('is_active', true)->get();
            $urls = [];

            foreach ($categories as $category) {
                /** @var Carbon $updatedAt */
                $updatedAt = $category->updated_at ?? now();
                $urls[] = [
                    'loc' => url('/category/'.$category->slug),
                    'lastmod' => $updatedAt->toAtomString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.7',
                ];
            }

            return $this->generateSitemapFromUrls($urls);
        });

        return $response;
    }

    /**
     * @param  Collection<int, Content>  $items
     */
    protected function generateSitemap($items): \Illuminate\Http\Response
    {
        $urls = [];

        foreach ($items as $item) {
            /** @var Carbon $updatedAt */
            $updatedAt = $item->updated_at ?? now();
            $urls[] = [
                'loc' => url('/content/'.$item->slug),
                'lastmod' => $updatedAt->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => $item->type === 'page' ? '0.8' : '0.6',
            ];
        }

        return $this->generateSitemapFromUrls($urls);
    }

    /**
     * @param  array<int, array{loc: string, lastmod?: string, changefreq?: string, priority?: string}>  $urls
     */
    protected function generateSitemapFromUrls(array $urls): \Illuminate\Http\Response
    {
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($urls as $url) {
            $sitemap .= '  <url>'."\n";
            $sitemap .= '    <loc>'.htmlspecialchars($url['loc']).'</loc>'."\n";
            $sitemap .= '    <lastmod>'.($url['lastmod'] ?? now()->toAtomString()).'</lastmod>'."\n";
            $sitemap .= '    <changefreq>'.($url['changefreq'] ?? 'weekly').'</changefreq>'."\n";
            $sitemap .= '    <priority>'.($url['priority'] ?? '0.5').'</priority>'."\n";
            $sitemap .= '  </url>'."\n";
        }

        $sitemap .= '</urlset>';

        return Response::make($sitemap, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }
}
