<?php

declare(strict_types=1);

namespace Modules\Search\Listeners;

use Modules\Publishing\Contracts\PublishingSearchReadPortInterface;
use Modules\Publishing\Events\ContentDeleted;
use Modules\Publishing\Events\ContentPublished;
use Modules\Publishing\Events\ContentUnpublished;
use Modules\Search\Contracts\SearchIndexerInterface;

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
