<?php

declare(strict_types=1);

namespace Modules\Content\Library\Contracts;

use Modules\Content\Library\Dto\TaxonomySearchSnapshot;

/**
 * Resolves Library taxonomy data for cross-tier search consumers.
 */
interface TaxonomySearchPortInterface
{
    public function categorySearchableType(): string;

    public function tagSearchableType(): string;

    public function snapshotForIndex(string $taxonomyType, string $searchableId): ?TaxonomySearchSnapshot;

    /**
     * @return iterable<int, TaxonomySearchSnapshot>
     */
    public function activeCategorySnapshots(): iterable;

    /**
     * @return iterable<int, TaxonomySearchSnapshot>
     */
    public function tagSnapshots(): iterable;

    public function activeCategoryCount(): int;

    public function tagCount(): int;
}
