<?php

declare(strict_types=1);

namespace Modules\Mail\Tests\Support;

use Modules\Core\System\Models\Extension;

trait ActivatesMailExtension
{
    protected function activateMailExtension(): void
    {
        Extension::query()->updateOrCreate(
            ['slug' => 'mail'],
            [
                'name' => 'JA-Mail',
                'type' => 'module',
                'version' => '1.0.0',
                'status' => 'active',
                'license' => 'Proprietary',
            ],
        );
        Extension::flushProductActiveMemo();
    }
}
