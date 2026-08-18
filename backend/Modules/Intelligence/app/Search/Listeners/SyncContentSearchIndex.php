<?php

declare(strict_types=1);

namespace Modules\Intelligence\Search\Listeners;

use Modules\Content\Publishing\Contracts\PublishingSearchReadPortInterface;
use Modules\Content\Publishing\Events\ContentDeleted;
use Modules\Content\Publishing\Events\ContentPublished;
use Modules\Content\Publishing\Events\ContentUnpublished;
use Modules\Intelligence\Search\Contracts\SearchIndexerInterface;

class SyncContentSearchIndex
{
    public function __construct(
        protected SearchIndexerInterface $searchIndexer,
        protected PublishingSearchReadPortInterface $publishingSearchRead,
    ) {}

    public function handlePublished(ContentPublished $event): void
    {
        $key = $event->content->getKey();
        $contentId = is_scalar($key) ? (string) $key : '';
        if ($contentId === '') {
            return;
        }
        $snapshot = $this->publishingSearchRead->snapshotById($contentId);
        if ($snapshot !== null) {
            $this->searchIndexer->syncPublishing($snapshot);
        }
    }

    public function handleUnpublished(ContentUnpublished $event): void
    {
        $key = $event->content->getKey();
        $contentId = is_scalar($key) ? (string) $key : '';
        if ($contentId === '') {
            return;
        }
        $this->searchIndexer->removePublishing(
            $this->publishingSearchRead->publishingSearchableType(),
            $contentId,
        );
    }

    public function handleDeleted(ContentDeleted $event): void
    {
        $this->searchIndexer->removePublishing(
            $this->publishingSearchRead->publishingSearchableType(),
            $event->contentId,
        );
    }
}
