<?php

declare(strict_types=1);

namespace Modules\Content\Publishing\Dto;

/**
 * Read model for Intelligence analytics (top content by slug).
 */
final readonly class PublishedContentAnalyticsRow
{
    public function __construct(
        public string $id,
        public string $title,
        public string $slug,
        public string $type,
        public ?string $authorName,
    ) {}
}
