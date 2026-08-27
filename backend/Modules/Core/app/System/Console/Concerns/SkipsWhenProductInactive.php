<?php

declare(strict_types=1);

namespace Modules\Core\System\Console\Concerns;

use Modules\Core\System\Models\Extension;

trait SkipsWhenProductInactive
{
    protected function skipUnlessProductActive(string $slug): bool
    {
        if (Extension::isProductActive($slug)) {
            return false;
        }

        $this->warn("Skipped: product pack '{$slug}' is not active.");

        return true;
    }
}
