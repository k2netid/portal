<?php

declare(strict_types=1);

namespace Modules\Publishing\Contracts;

use Modules\Publishing\Dto\PublishedContentAnalyticsRow;

interface PublishedContentAnalyticsPortInterface
{
    /**
     * @param  list<string>  $slugs
     * @return list<PublishedContentAnalyticsRow>
     */
    public function publishedRowsBySlugs(array $slugs): array;
}
