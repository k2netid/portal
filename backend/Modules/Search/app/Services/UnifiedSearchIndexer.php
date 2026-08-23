<?php

declare(strict_types=1);

namespace Modules\Search\Services;

use Modules\Library\Contracts\TaxonomySearchPortInterface;
use Modules\Library\Dto\TaxonomySearchSnapshot;
use Modules\Publishing\Contracts\PublishingSearchReadPortInterface;
use Modules\Publishing\Dto\SearchableContentSnapshot;
use Modules\Search\Contracts\SearchIndexerInterface;
use Modules\Search\Models\SearchIndex;

class UnifiedSearchIndexer implements SearchIndexerInterface
{
    public function __construct(
        private readonly PublishingSearchReadPortInterface $publishingSearchRead,
        private readonly TaxonomySearchPortInterface $taxonomySearchPort,
    ) {}

    public function syncPublishing(SearchableContentSnapshot $snapshot): void
    {
        if ($snapshot->status !== 'published') {
            $this->removePublishing($snapshot->searchableType, $snapshot->searchableId);

            return;
        }

        $bodyText = trim(strip_tags($snapshot->intro.' '.$snapshot->body));

        SearchIndex::indexFromSnapshot($snapshot, [
            'title' => $snapshot->title,
            'content' => trim("{$bodyText} {$snapshot->categoryName} {$snapshot->authorName}"),
            'excerpt' => $snapshot->excerpt,
            'url' => $snapshot->type === 'page'
                ? url('/'.$snapshot->slug)
                : url('/blog/'.$snapshot->slug),
            'type' => $snapshot->type,
        ]);
    }

    public function syncTaxonomy(TaxonomySearchSnapshot $snapshot): void
    {
        if ($snapshot->taxonomyKind === 'category' && ! $snapshot->isActive) {
            $this->removeTaxonomy($snapshot->searchableType, $snapshot->searchableId);

            return;
        }

        $url = $snapshot->taxonomyKind === 'category'
            ? url('/category/'.$snapshot->slug)
            : url('/tag/'.$snapshot->slug);

        SearchIndex::query()->updateOrCreate(
            [
                'searchable_type' => $snapshot->searchableType,
                'searchable_id' => $snapshot->searchableId,
            ],
            [
                'title' => $snapshot->name,
                'content' => $snapshot->description,
                'url' => $url,
                'type' => $snapshot->taxonomyKind,
                'relevance_score' => SearchIndex::calculateRelevanceScore($snapshot->name, $snapshot->description),
            ]
        );
    }

    public function removePublishing(string $searchableType, string $searchableId): void
    {
        $this->removeByKey($searchableType, $searchableId);
    }

    public function removeTaxonomy(string $searchableType, string $searchableId): void
    {
        $this->removeByKey($searchableType, $searchableId);
    }

    public function reindexAll(): array
    {
        $contentCount = 0;
        foreach ($this->publishingSearchRead->publishedSnapshots() as $snapshot) {
            $this->syncPublishing($snapshot);
            $contentCount++;
        }

        $categoryCount = 0;
        foreach ($this->taxonomySearchPort->activeCategorySnapshots() as $snapshot) {
            $this->syncTaxonomy($snapshot);
            $categoryCount++;
        }

        $tagCount = 0;
        foreach ($this->taxonomySearchPort->tagSnapshots() as $snapshot) {
            $this->syncTaxonomy($snapshot);
            $tagCount++;
        }

        return [
            'pub_contents' => $contentCount,
            'pub_categories' => $categoryCount,
            'pub_tags' => $tagCount,
        ];
    }

    private function removeByKey(string $searchableType, string $searchableId): void
    {
        SearchIndex::query()
            ->where('searchable_type', $searchableType)
            ->where('searchable_id', $searchableId)
            ->delete();
    }
}
