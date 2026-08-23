<?php

declare(strict_types=1);

namespace Modules\Publishing\Contracts;

use Modules\Publishing\Dto\SearchableContentSnapshot;

/**
 * Read port: Intelligence Search may consume publishing snapshots without importing Content models.
 */
interface PublishingSearchReadPortInterface
{
    public function publishingSearchableType(): string;

    /**
     * @return iterable<int, SearchableContentSnapshot>
     */
    public function publishedSnapshots(): iterable;

    public function snapshotById(string $contentId): ?SearchableContentSnapshot;

    public function publishedContentCount(): int;
}
