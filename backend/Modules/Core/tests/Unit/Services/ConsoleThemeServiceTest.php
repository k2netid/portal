<?php

declare(strict_types=1);

namespace Modules\Core\Tests\Unit\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\System\Models\Setting;
use Modules\Core\System\Services\ConsoleThemeService;
use Tests\TestCase;

class ConsoleThemeServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_schema_contains_console_brand_primary(): void
    {
        $service = new ConsoleThemeService;
        $schema = $service->getSchema();

        $this->assertArrayHasKey('console_brand_primary', $schema);
        $this->assertSame('#4f46e5', $schema['console_brand_primary']['default'] ?? null);
    }

    public function test_schema_contains_console_color_preset(): void
    {
        $service = new ConsoleThemeService;
        $schema = $service->getSchema();

        $this->assertArrayHasKey('console_color_preset', $schema);
        $this->assertSame('custom', $schema['console_color_preset']['default'] ?? null);
    }

    public function test_resolved_settings_merge_database_over_defaults(): void
    {
        Setting::updateOrCreate(
            ['key' => 'console_brand_primary'],
            ['value' => '#ff0000', 'group' => 'console_branding', 'type' => 'string'],
        );

        $service = new ConsoleThemeService;
        $resolved = $service->getResolvedSettings();

        $this->assertSame('#ff0000', $resolved['console_brand_primary']);
    }

    public function test_resolved_settings_merge_brand_group_logos(): void
    {
        Setting::updateOrCreate(
            ['key' => 'app_logo_light'],
            ['value' => 'https://cdn.example/logo-light.png', 'group' => 'brand', 'type' => 'image'],
        );

        $service = new ConsoleThemeService;
        $resolved = $service->getResolvedSettings();

        $this->assertSame('https://cdn.example/logo-light.png', $resolved['app_logo_light']);
    }
}
