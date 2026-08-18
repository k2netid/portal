<?php

declare(strict_types=1);

namespace Modules\Content\Publishing\Contracts;

use Modules\Content\Publishing\Dto\PublishedContentAnalyticsRow;

interface PublishedContentAnalyticsPortInterface
{
    /**
     * @param  list<string>  $slugs
     * @return list<PublishedContentAnalyticsRow>
     */
    public function publishedRowsBySlugs(array $slugs): array;
}
