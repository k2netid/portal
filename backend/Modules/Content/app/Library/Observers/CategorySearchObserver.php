<?php

declare(strict_types=1);

namespace Modules\Content\Library\Observers;

use Modules\Content\Library\Events\TaxonomySearchIndexChanged;
use Modules\Content\Library\Models\Category;

class CategorySearchObserver
{
    public function saved(Category $category): void
    {
        $key = $category->getKey();
        if (! is_scalar($key)) {
            return;
        }

        TaxonomySearchIndexChanged::dispatch('category', (string) $key, 'sync');
    }

    public function deleted(Category $category): void
    {
        $key = $category->getKey();
        if (! is_scalar($key)) {
            return;
        }

        TaxonomySearchIndexChanged::dispatch('category', (string) $key, 'remove');
    }
}
