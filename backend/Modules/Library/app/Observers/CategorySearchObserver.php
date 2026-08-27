<?php

declare(strict_types=1);

namespace Modules\Library\Observers;

use Modules\Core\System\Models\Extension;
use Modules\Library\Events\TaxonomySearchIndexChanged;
use Modules\Library\Models\Category;

class CategorySearchObserver
{
    public function saved(Category $category): void
    {
        if (! Extension::isProductActive('search')) {
            return;
        }

        $key = $category->getKey();
        if (! is_scalar($key)) {
            return;
        }

        TaxonomySearchIndexChanged::dispatch('category', (string) $key, 'sync');
    }

    public function deleted(Category $category): void
    {
        if (! Extension::isProductActive('search')) {
            return;
        }

        $key = $category->getKey();
        if (! is_scalar($key)) {
            return;
        }

        TaxonomySearchIndexChanged::dispatch('category', (string) $key, 'remove');
    }
}
