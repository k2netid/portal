<?php

declare(strict_types=1);

namespace Modules\Intelligence\Search\Listeners;

use Modules\Crm\Contracts\CrmSearchReadPortInterface;
use Modules\Crm\Events\CrmSearchIndexChanged;
use Modules\Intelligence\Search\Contracts\SearchIndexerInterface;

class SyncCrmSearchIndex
{
    public function __construct(
        protected SearchIndexerInterface $searchIndexer,
        protected CrmSearchReadPortInterface $crmSearchRead,
    ) {}

    public function handle(CrmSearchIndexChanged $event): void
    {
        if ($event->action === 'remove') {
            $this->searchIndexer->removeCrm(
                $this->crmSearchRead->crmSearchableType($event->entityKind),
                $event->entityId,
            );

            return;
        }

        $snapshot = $this->crmSearchRead->snapshotByKindAndId($event->entityKind, $event->entityId);
        if ($snapshot !== null) {
            $this->searchIndexer->syncCrm($snapshot);
        }
    }
}
