<?php

declare(strict_types=1);

namespace Modules\Library\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class TaxonomySearchIndexChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $taxonomyType,
        public readonly string $searchableId,
        public readonly string $action,
    ) {}
}
