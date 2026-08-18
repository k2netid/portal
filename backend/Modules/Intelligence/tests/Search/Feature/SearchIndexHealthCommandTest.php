<?php

declare(strict_types=1);

namespace Modules\Intelligence\Search\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchIndexHealthCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_index_health_command_runs(): void
    {
        $this->artisan('search:index-health')
            ->assertExitCode(0);
    }
}
