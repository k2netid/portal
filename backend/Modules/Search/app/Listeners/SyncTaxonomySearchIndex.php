<?php

declare(strict_types=1);

namespace Modules\Search\Listeners;

use Modules\Library\Contracts\TaxonomySearchPortInterface;
use Modules\Library\Events\TaxonomySearchIndexChanged;
use Modules\Search\Contracts\SearchIndexerInterface;

class SyncTaxonomySearchIndex
{
    public function __construct(
        private readonly SearchIndexerInterface $searchIndexer,
        private readonly TaxonomySearchPortInterface $taxonomySearchPort,
    ) {}

    public function handle(TaxonomySearchIndexChanged $event): void
    {
        if (! \Modules\Core\System\Models\Extension::isProductActive('search')) {
            return;
        }

        if ($event->action === 'remove') {
            $type = $event->taxonomyType === 'category'
                ? $this->taxonomySearchPort->categorySearchableType()
                : $this->taxonomySearchPort->tagSearchableType();
            $this->searchIndexer->removeTaxonomy($type, $event->searchableId);

            return;
        }

        $snapshot = $this->taxonomySearchPort->snapshotForIndex($event->taxonomyType, $event->searchableId);
        if ($snapshot !== null) {
            $this->searchIndexer->syncTaxonomy($snapshot);
        }
    }
}
