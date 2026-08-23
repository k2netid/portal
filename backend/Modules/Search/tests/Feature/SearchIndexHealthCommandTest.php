<?php

declare(strict_types=1);

namespace Modules\Search\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Search\Tests\SearchTestCase;

class SearchIndexHealthCommandTest extends SearchTestCase
{
    use RefreshDatabase;

    public function test_search_index_health_command_runs(): void
    {
        $this->artisan('search:index-health')
            ->assertExitCode(0);
    }
}
