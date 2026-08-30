<?php

declare(strict_types=1);

namespace Modules\Publishing\Services;

use Modules\Publishing\Contracts\PublishedContentAnalyticsPortInterface;
use Modules\Publishing\Dto\PublishedContentAnalyticsRow;
use Modules\Publishing\Models\Content;

class PublishedContentAnalyticsPortAdapter implements PublishedContentAnalyticsPortInterface
{
    public function publishedRowsBySlugs(array $slugs): array
    {
        if ($slugs === []) {
            return [];
        }

        $rows = [];
        foreach (Content::query()
            ->with('author')
            ->whereIn('slug', $slugs)
            ->where('status', 'published')
            ->get() as $content) {
            if (! $content instanceof Content) {
                continue;
            }
            $key = $content->getKey();
            $rows[] = new PublishedContentAnalyticsRow(
                id: is_scalar($key) ? (string) $key : '',
                title: (string) $content->title,
                slug: (string) $content->slug,
                type: (string) $content->type,
                authorName: $content->author !== null ? (string) $content->author->name : null,
            );
        }

        return $rows;
    }
}
