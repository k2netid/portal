<?php

declare(strict_types=1);

namespace Modules\Layout\Tests\Feature;

use Modules\Core\System\Models\Setting;
use Modules\Core\System\Models\User;
use Modules\Layout\Models\Theme;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ThemePackageLifecycleTest extends TestCase
{
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->activatePack('layout');
        $this->seedPermissionsAndRoles();
        $this->admin = $this->createSuperAdminUser();

        Setting::set('license_type', 'enterprise', 'string', 'license');
        Setting::set('enable_theme_upload', true, 'boolean', 'security');
        Setting::set('enable_theme_export', true, 'boolean', 'security');
    }

    public function test_upload_status_returns_capabilities(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/manage/layout/themes/upload-status')
            ->assertOk();

        $response->assertJsonPath('data.enabled', true);
        $response->assertJsonPath('data.export_enabled', true);
    }

    public function test_upload_status_respects_settings_toggle(): void
    {
        Setting::set('enable_theme_upload', false, 'boolean', 'security');
        Setting::set('enable_theme_export', false, 'boolean', 'security');

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/manage/layout/themes/upload-status')
            ->assertOk();

        $response->assertJsonPath('data.enabled', false);
        $response->assertJsonPath('data.export_enabled', false);
    }

    public function test_export_theme_blocked_when_disabled(): void
    {
        Setting::set('enable_theme_export', false, 'boolean', 'security');

        $theme = Theme::query()->where('slug', 'janari')->first() ?? Theme::factory()->create([
            'name' => 'Janari',
            'slug' => 'janari',
            'path' => 'resources/themes/janari',
            'type' => 'frontend',
            'status' => 'active',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/v1/manage/layout/themes/{$theme->slug}/export");

        $response->assertStatus(403);
    }

    public function test_admin_can_export_theme_zip(): void
    {
        Setting::set('enable_theme_export', true, 'boolean', 'security');

        $theme = Theme::query()->where('slug', 'janari')->first() ?? Theme::factory()->create([
            'name' => 'Janari',
            'slug' => 'janari',
            'path' => 'resources/themes/janari',
            'type' => 'frontend',
            'status' => 'active',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/v1/manage/layout/themes/{$theme->slug}/export");

        $response->assertOk();
        $this->assertTrue(
            str_contains((string) $response->headers->get('content-type'), 'zip') ||
            str_contains((string) $response->headers->get('content-disposition'), 'attachment')
        );
    }
}
