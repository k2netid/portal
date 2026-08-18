<?php

declare(strict_types=1);

namespace Modules\Intelligence\Search\Contracts;

use Modules\Content\Library\Dto\TaxonomySearchSnapshot;
use Modules\Content\Publishing\Dto\SearchableContentSnapshot;
use Modules\Crm\Dto\CrmSearchSnapshot;

/**
 * Single entry point for writing to the search index (Intelligence-owned storage).
 */
interface SearchIndexerInterface
{
    public function syncPublishing(SearchableContentSnapshot $snapshot): void;

    public function syncCrm(CrmSearchSnapshot $snapshot): void;

    public function syncTaxonomy(TaxonomySearchSnapshot $snapshot): void;

    public function removePublishing(string $searchableType, string $searchableId): void;

    public function removeCrm(string $searchableType, string $searchableId): void;

    public function removeTaxonomy(string $searchableType, string $searchableId): void;

    /**
     * @return array{pub_contents: int, pub_categories: int, pub_tags: int, crm_records: int}
     */
    public function reindexAll(): array;
}
