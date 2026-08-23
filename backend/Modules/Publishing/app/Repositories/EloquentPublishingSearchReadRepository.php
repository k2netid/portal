<?php

declare(strict_types=1);

namespace Modules\Publishing\Repositories;

use Modules\Publishing\Contracts\PublishingSearchReadPortInterface;
use Modules\Publishing\Dto\SearchableContentSnapshot;
use Modules\Publishing\Models\Content;

class EloquentPublishingSearchReadRepository implements PublishingSearchReadPortInterface
{
    public function publishingSearchableType(): string
    {
        return Content::class;
    }

    public function publishedSnapshots(): iterable
    {
        $query = Content::query()
            ->where('status', 'published')
            ->with(['category', 'author']);

        foreach ($query->cursor() as $content) {
            if ($content instanceof Content) {
                yield SearchableContentSnapshot::fromContent($content);
            }
        }
    }

    public function snapshotById(string $contentId): ?SearchableContentSnapshot
    {
        $content = Content::query()
            ->with(['category', 'author'])
            ->find($contentId);

        if (! $content instanceof Content) {
            return null;
        }

        return SearchableContentSnapshot::fromContent($content);
    }

    public function publishedContentCount(): int
    {
        return Content::query()->where('status', 'published')->count();
    }
}
