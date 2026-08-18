<?php

declare(strict_types=1);

namespace Modules\Content\Library\Observers;

use Modules\Content\Library\Events\TaxonomySearchIndexChanged;
use Modules\Content\Library\Models\Tag;

class TagSearchObserver
{
    public function saved(Tag $tag): void
    {
        $key = $tag->getKey();
        if (! is_scalar($key)) {
            return;
        }

        TaxonomySearchIndexChanged::dispatch('tag', (string) $key, 'sync');
    }

    public function deleted(Tag $tag): void
    {
        $key = $tag->getKey();
        if (! is_scalar($key)) {
            return;
        }

        TaxonomySearchIndexChanged::dispatch('tag', (string) $key, 'remove');
    }
}
