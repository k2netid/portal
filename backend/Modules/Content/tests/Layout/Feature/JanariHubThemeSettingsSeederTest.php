<?php

declare(strict_types=1);

namespace Modules\Content\Layout\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Modules\Content\Layout\Database\Seeders\JanariHubThemeSettingsSeeder;
use Modules\Content\Layout\Models\Theme;
use Modules\Content\Layout\Services\ThemeCacheService;
use Tests\TestCase;

class JanariHubThemeSettingsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_clears_active_theme_api_payload_cache(): void
    {
        $theme = Theme::factory()->create([
            'slug' => 'janari',
            'type' => 'frontend',
            'is_active' => true,
            'settings' => ['hero_badge' => 'OLD'],
        ]);

        Cache::put(
            ThemeCacheService::PREFIX_ACTIVE_API_PAYLOAD.'frontend',
            ['settings' => ['hero_badge' => 'STALE']],
            3600,
        );

        $this->seed(JanariHubThemeSettingsSeeder::class);

        $this->assertFalse(
            Cache::has(ThemeCacheService::PREFIX_ACTIVE_API_PAYLOAD.'frontend'),
            'Seeder should clear cached public theme payload',
        );

        $theme->refresh();
        $this->assertSame('PLATFORM JEJAKAWAN', $theme->settings['hero_badge'] ?? null);
        $this->assertSame('Lihat harga', $theme->settings['cta_secondary_text'] ?? null);
    }
}
