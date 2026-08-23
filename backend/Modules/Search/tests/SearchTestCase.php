<?php

declare(strict_types=1);

namespace Modules\Search\Tests;

use Modules\Core\System\Models\Extension;
use Tests\TestCase as BaseTestCase;

abstract class SearchTestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
        $this->activateSearchExtension();
    }

    protected function activateSearchExtension(): void
    {
        Extension::query()->updateOrCreate(
            ['slug' => 'search'],
            [
                'type' => 'module',
                'name' => 'Search',
                'version' => '1.0.0',
                'database_version' => '1.0.0',
                'status' => 'active',
                'is_core' => false,
            ],
        );
    }
}
