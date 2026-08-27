<?php

declare(strict_types=1);

namespace Modules\Library\Observers;

use Modules\Core\System\Models\Extension;
use Modules\Library\Events\TaxonomySearchIndexChanged;
use Modules\Library\Models\Tag;

class TagSearchObserver
{
    public function saved(Tag $tag): void
    {
        if (! Extension::isProductActive('search')) {
            return;
        }

        $key = $tag->getKey();
        if (! is_scalar($key)) {
            return;
        }

        TaxonomySearchIndexChanged::dispatch('tag', (string) $key, 'sync');
    }

    public function deleted(Tag $tag): void
    {
        if (! Extension::isProductActive('search')) {
            return;
        }

        $key = $tag->getKey();
        if (! is_scalar($key)) {
            return;
        }

        TaxonomySearchIndexChanged::dispatch('tag', (string) $key, 'remove');
    }
}
