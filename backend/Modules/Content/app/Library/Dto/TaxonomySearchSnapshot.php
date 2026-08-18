<?php

declare(strict_types=1);

namespace Modules\Content\Library\Dto;

/**
 * Immutable taxonomy row for Intelligence search indexing.
 */
final readonly class TaxonomySearchSnapshot
{
    public function __construct(
        public string $searchableType,
        public string $searchableId,
        public string $taxonomyKind,
        public string $name,
        public string $slug,
        public string $description,
        public bool $isActive,
    ) {}
}
