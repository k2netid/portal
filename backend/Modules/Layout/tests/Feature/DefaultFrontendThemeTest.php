<?php

declare(strict_types=1);

namespace Modules\Layout\Tests\Feature;

use Modules\Layout\Models\Theme;
use Modules\Layout\Services\ThemeService;
use Tests\TestCase;

class DefaultFrontendThemeTest extends TestCase
{
    public function test_auto_activate_prefers_janari_over_sarangenge(): void
    {
        Theme::query()->delete();

        Theme::factory()->create([
            'name' => 'Sarangenge',
            'slug' => 'sarangenge',
            'type' => 'frontend',
            'status' => 'active',
            'is_active' => false,
        ]);
        Theme::factory()->create([
            'name' => 'Janari',
            'slug' => 'janari',
            'type' => 'frontend',
            'status' => 'active',
            'is_active' => false,
        ]);

        $active = Theme::getActiveTheme('frontend');

        $this->assertNotNull($active);
        $this->assertSame(Theme::DEFAULT_FRONTEND_SLUG, $active->slug);
        $this->assertTrue((bool) $active->is_active);
    }

    public function test_ensure_default_is_noop_when_theme_already_active(): void
    {
        Theme::query()->delete();

        $sarangenge = Theme::factory()->create([
            'name' => 'Sarangenge',
            'slug' => 'sarangenge',
            'type' => 'frontend',
            'status' => 'active',
            'is_active' => true,
        ]);
        Theme::factory()->create([
            'name' => 'Janari',
            'slug' => 'janari',
            'type' => 'frontend',
            'status' => 'active',
            'is_active' => false,
        ]);

        $ensured = app(ThemeService::class)->ensureDefaultFrontendTheme();

        $this->assertNotNull($ensured);
        $this->assertSame($sarangenge->id, $ensured->id);
    }
}
