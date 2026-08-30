<?php

declare(strict_types=1);

namespace Modules\Search\Services;

use Modules\Library\Contracts\TaxonomySearchPortInterface;
use Modules\Publishing\Contracts\PublishingSearchReadPortInterface;
use Modules\Search\Models\SearchIndex;

class SearchIndexHealthService
{
    public function __construct(
        private readonly PublishingSearchReadPortInterface $publishingRead,
        private readonly TaxonomySearchPortInterface $taxonomyPort,
    ) {}

    /**
     * @return array{
     *     in_sync: bool,
     *     total_lag: int,
     *     checked_at: string,
     *     resources: array<int, array{key: string, label: string, source: int, indexed: int, lag: int}>,
     *     index_totals: array{all: int, post: int, page: int, category: int, tag: int}
     * }
     */
    public function snapshot(): array
    {
        $published = $this->publishingRead->publishedContentCount();
        $indexedPosts = SearchIndex::query()->where('type', 'post')->count();
        $indexedPages = SearchIndex::query()->where('type', 'page')->count();
        $indexedContent = $indexedPosts + $indexedPages;

        $activeCategories = $this->taxonomyPort->activeCategoryCount();
        $indexedCategories = SearchIndex::query()->where('type', 'category')->count();

        $tags = $this->taxonomyPort->tagCount();
        $indexedTags = SearchIndex::query()->where('type', 'tag')->count();

        $resources = [
            $this->row('content', 'Published content', $published, $indexedContent),
            $this->row('category', 'Active categories', $activeCategories, $indexedCategories),
            $this->row('tag', 'Tags', $tags, $indexedTags),
        ];

        $totalLag = array_sum(array_column($resources, 'lag'));

        return [
            'in_sync' => $totalLag === 0,
            'total_lag' => $totalLag,
            'checked_at' => now()->toIso8601String(),
            'resources' => $resources,
            'index_totals' => [
                'all' => SearchIndex::query()->count(),
                'post' => $indexedPosts,
                'page' => $indexedPages,
                'category' => $indexedCategories,
                'tag' => $indexedTags,
            ],
        ];
    }

    /**
     * @return array{key: string, label: string, source: int, indexed: int, lag: int}
     */
    private function row(string $key, string $label, int $source, int $indexed): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'source' => $source,
            'indexed' => $indexed,
            'lag' => max(0, $source - $indexed),
        ];
    }
}
