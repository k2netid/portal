<?php

declare(strict_types=1);

namespace Modules\Core\System\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\System\Models\Setting;
use Tests\TestCase;

class SettingsApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
    }

    public function test_admin_can_list_and_filter_settings(): void
    {
        $admin = $this->createAdminUser();

        Setting::create([
            'key' => 'site_title',
            'value' => 'Jejakawan Control Plane',
            'type' => 'string',
            'group' => 'system',
            'is_public' => true,
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/system/settings?group=system');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'key', 'value', 'type', 'group'],
                ],
            ]);
    }

    public function test_admin_can_retrieve_settings_by_group(): void
    {
        $admin = $this->createAdminUser();

        Setting::create([
            'key' => 'maintenance_mode',
            'value' => '0',
            'type' => 'boolean',
            'group' => 'system',
            'is_public' => true,
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/system/settings/group/system');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    }

    public function test_admin_can_store_and_bulk_update_settings(): void
    {
        $admin = $this->createAdminUser();

        // 1. Store new setting
        $storeResponse = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/manage/system/settings', [
                'key' => 'custom_support_email',
                'value' => 'support@jejakawan.com',
                'type' => 'string',
                'group' => 'system',
                'description' => 'Helpdesk email address',
                'is_public' => true,
            ]);

        $storeResponse->assertCreated();
        $this->assertDatabaseHas('sys_settings', ['key' => 'custom_support_email']);

        // 2. Bulk update settings
        $bulkResponse = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/manage/system/settings/bulk-update', [
                'settings' => [
                    [
                        'key' => 'custom_support_email',
                        'value' => 'contact@jejakawan.com',
                        'type' => 'string',
                        'group' => 'system',
                    ],
                ],
            ]);

        $bulkResponse->assertOk();
        $this->assertEquals('contact@jejakawan.com', Setting::get('custom_support_email'));
    }

    public function test_unauthenticated_cannot_access_settings(): void
    {
        $this->getJson('/api/v1/manage/system/settings')->assertUnauthorized();
        $this->postJson('/api/v1/manage/system/settings', [])->assertUnauthorized();
    }

    public function test_kernel_settings_reject_product_setting_groups(): void
    {
        $admin = $this->createAdminUser();

        Setting::create([
            'key' => 'meta_title',
            'value' => 'Kernel should not own this',
            'type' => 'string',
            'group' => 'seo',
            'is_public' => true,
        ]);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/system/settings')
            ->assertOk()
            ->assertJsonMissing(['key' => 'meta_title']);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/system/settings/group/seo')
            ->assertForbidden();

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/manage/system/settings/bulk-update', [
                'settings' => [
                    [
                        'key' => 'meta_title',
                        'value' => 'Hijacked by kernel',
                        'type' => 'string',
                        'group' => 'seo',
                    ],
                ],
            ])
            ->assertOk();

        $this->assertSame('Kernel should not own this', Setting::get('meta_title'));
    }

    public function test_kernel_settings_list_includes_general_identity_group(): void
    {
        $admin = $this->createAdminUser();

        Setting::create([
            'key' => 'site_name',
            'value' => 'Public identity',
            'type' => 'string',
            'group' => 'general',
            'is_public' => true,
        ]);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/system/settings')
            ->assertOk()
            ->assertJsonFragment(['key' => 'site_name', 'group' => 'general']);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/system/settings/group/general')
            ->assertOk();
    }
}
