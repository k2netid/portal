<?php

declare(strict_types=1);

namespace Modules\Core\Tests\Feature;

use Modules\Core\System\Models\Extension;
use Modules\Core\System\Services\ExtensionHealthService;
use Modules\Core\System\Services\LicenseService;
use Modules\Layout\Models\BuilderPreset;
use Modules\Publishing\Models\Content;
use Tests\TestCase;

final class VisualBuilderExtensionGateTest extends TestCase
{
    private LicenseService $licenseService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
        $this->licenseService = app(LicenseService::class);
        $this->activatePack('layout');
        $this->activatePack('publishing');
    }

    public function test_license_matrix_gates_visual_builder(): void
    {
        $this->licenseService->deactivateLicense();
        $this->assertFalse($this->licenseService->canUseFeature('visual_builder'));

        $this->licenseService->activateLicense('JACP-PRO-TEST-1234-5678');
        $this->assertTrue($this->licenseService->canUseFeature('visual_builder'));

        $this->licenseService->activateLicense('JACP-ENT-VIP-9999-0000');
        $this->assertTrue($this->licenseService->canUseFeature('visual_builder'));

        $this->licenseService->deactivateLicense();
    }

    public function test_license_blocker_prevents_activation_on_community(): void
    {
        $this->licenseService->deactivateLicense();

        $ext = Extension::query()->where('slug', 'visual-builder')->first();
        if (! $ext) {
            $ext = Extension::query()->create([
                'slug' => 'visual-builder',
                'type' => 'plugin',
                'name' => 'Visual Page & Block Builder',
                'version' => '1.0.0',
                'database_version' => '1.0.0',
                'status' => 'inactive',
                'is_core' => false,
                'manifest' => [
                    'slug' => 'visual-builder',
                    'license_tier' => 'pro',
                ],
            ]);
        }

        $healthService = app(ExtensionHealthService::class);
        $blocker = $healthService->licenseBlocker($ext);
        $this->assertNotNull($blocker);
        $this->assertStringContainsString('Lisensi situs (community) tidak mencukupi', $blocker);

        $this->licenseService->activateLicense('JACP-PRO-TEST-1234-5678');
        $blockerAfterPro = $healthService->licenseBlocker($ext);
        $this->assertNull($blockerAfterPro);

        $this->licenseService->deactivateLicense();
    }

    public function test_builder_routes_are_gated_by_extension_active_middleware(): void
    {
        $admin = $this->createAdminUser();

        Extension::query()->updateOrCreate(
            ['slug' => 'visual-builder'],
            [
                'type' => 'plugin',
                'name' => 'Visual Page & Block Builder',
                'version' => '1.0.0',
                'database_version' => '1.0.0',
                'status' => 'inactive',
                'is_core' => false,
                'manifest' => ['license_tier' => 'pro'],
            ]
        );
        Extension::flushProductActiveMemo();

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/layout/builder-presets')
            ->assertStatus(403)
            ->assertJsonPath('error_code', 'VISUAL_BUILDER_EXTENSION_INACTIVE');

        Extension::query()->updateOrCreate(
            ['slug' => 'visual-builder'],
            [
                'status' => 'active',
            ]
        );
        Extension::flushProductActiveMemo();

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/layout/builder-presets')
            ->assertStatus(200);
    }

    public function test_deactivation_preserves_content_blocks_and_presets(): void
    {
        Extension::query()->updateOrCreate(
            ['slug' => 'visual-builder'],
            ['status' => 'active']
        );
        Extension::flushProductActiveMemo();

        $preset = BuilderPreset::query()->create([
            'type' => 'hero',
            'name' => 'Test Section Preset',
            'settings' => [
                'blocks' => [
                    ['id' => 'b1', 'type' => 'hero', 'content' => ['title' => 'Awesome Title']],
                ],
            ],
            'is_system' => false,
        ]);

        $admin = $this->createAdminUser();

        $content = Content::query()->create([
            'author_id' => $admin->id,
            'title' => 'Page With Builder',
            'slug' => 'page-with-builder-'.uniqid(),
            'type' => 'page',
            'status' => 'published',
            'meta' => [
                'builder_blocks' => [
                    ['id' => 'b1', 'type' => 'hero', 'content' => ['title' => 'Awesome Title']],
                ],
            ],
        ]);

        // Now simulate deactivation
        Extension::query()->where('slug', 'visual-builder')->update(['status' => 'inactive']);
        Extension::flushProductActiveMemo();

        // Ensure data is intact
        $this->assertDatabaseHas('lay_builder_presets', ['id' => $preset->id]);
        $reloadedContent = Content::query()->find($content->id);
        $this->assertNotNull($reloadedContent);
        $this->assertEquals('hero', $reloadedContent->meta['builder_blocks'][0]['type'] ?? null);

        // Cleanup
        $preset->delete();
        $content->delete();
        Extension::query()->where('slug', 'visual-builder')->update(['status' => 'active']);
        Extension::flushProductActiveMemo();
    }
}
