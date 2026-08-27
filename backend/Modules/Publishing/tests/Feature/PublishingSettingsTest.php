<?php

declare(strict_types=1);

namespace Modules\Publishing\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\System\Models\Extension;
use Modules\Core\System\Models\Setting;
use Tests\TestCase;

class PublishingSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
        Extension::query()->updateOrCreate(
            ['slug' => 'publishing'],
            [
                'name' => 'Publishing',
                'type' => 'module',
                'version' => '1.0.0',
                'status' => 'active',
                'license' => 'Proprietary',
            ],
        );
    }

    public function test_publishing_can_update_seo_settings(): void
    {
        $admin = $this->createAdminUser();

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/manage/publishing/settings/bulk-update', [
                'settings' => [
                    [
                        'key' => 'meta_title',
                        'value' => 'Public site title',
                        'type' => 'string',
                        'group' => 'seo',
                    ],
                ],
            ])
            ->assertOk();

        $this->assertSame('Public site title', Setting::get('meta_title'));
    }

    public function test_publishing_rejects_analytics_group(): void
    {
        $admin = $this->createAdminUser();

        Setting::create([
            'key' => 'analytics_retention_days',
            'value' => '90',
            'type' => 'integer',
            'group' => 'analytics',
        ]);

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/manage/publishing/settings/bulk-update', [
                'settings' => [
                    [
                        'key' => 'analytics_retention_days',
                        'value' => '7',
                        'type' => 'integer',
                        'group' => 'analytics',
                    ],
                ],
            ])
            ->assertOk();

        $this->assertEquals(90, Setting::get('analytics_retention_days'));
    }
}
