<?php

declare(strict_types=1);

namespace Modules\Content\Studio\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\System\Contracts\LayoutRegistryInterface;
use Modules\Core\System\Services\DashboardRegistry;
use Tests\TestCase;

class ContentStudioServiceProviderTest extends TestCase
{
    use RefreshDatabase;

    public function test_studio_registers_layout_locations_for_publishing(): void
    {
        $this->app->boot();

        $registry = $this->app->make(LayoutRegistryInterface::class);
        $locations = $registry->getMenuLocations('publishing');

        $this->assertNotEmpty($locations);
    }

    public function test_studio_registers_dashboard_stats_providers(): void
    {
        $this->app->boot();

        $registry = $this->app->make(DashboardRegistry::class);
        $stats = $registry->getAllStats();

        $this->assertArrayHasKey('library_taxonomy', $stats);
        $this->assertArrayHasKey('forms', $stats);
        $this->assertArrayHasKey('categories', $stats['library_taxonomy']);
        $this->assertArrayHasKey('forms', $stats['forms']);
    }
}
