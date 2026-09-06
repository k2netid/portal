<?php

declare(strict_types=1);

namespace Modules\Core\Tests\Feature;

use Modules\Core\System\Models\ContentType;
use Modules\Core\System\Models\DynamicRecord;
use Modules\Core\System\Models\Extension;
use Modules\Core\System\Services\ExtensionHealthService;
use Modules\Core\System\Services\LicenseService;
use Tests\TestCase;

final class DataStudioExtensionGateTest extends TestCase
{
    private LicenseService $licenseService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
        $this->licenseService = app(LicenseService::class);
    }

    public function test_license_matrix_gates_data_studio(): void
    {
        $this->licenseService->deactivateLicense();
        $this->assertFalse($this->licenseService->canUseFeature('data_studio'));

        $this->licenseService->activateLicense('JACP-PRO-TEST-1234-5678');
        $this->assertTrue($this->licenseService->canUseFeature('data_studio'));

        $this->licenseService->activateLicense('JACP-ENT-VIP-9999-0000');
        $this->assertTrue($this->licenseService->canUseFeature('data_studio'));

        $this->licenseService->deactivateLicense();
    }

    public function test_license_blocker_prevents_activation_on_community(): void
    {
        $this->licenseService->deactivateLicense();

        $ext = Extension::query()->where('slug', 'data-studio')->first();
        if (! $ext) {
            $ext = Extension::query()->create([
                'slug' => 'data-studio',
                'type' => 'plugin',
                'name' => 'Data Model Studio',
                'version' => '1.0.0',
                'database_version' => '1.0.0',
                'status' => 'inactive',
                'is_core' => false,
                'manifest' => [
                    'slug' => 'data-studio',
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

    public function test_data_studio_routes_gated_by_extension_active_middleware(): void
    {
        $admin = $this->createAdminUser();

        Extension::query()->updateOrCreate(
            ['slug' => 'data-studio'],
            [
                'type' => 'plugin',
                'name' => 'Data Model Studio',
                'version' => '1.0.0',
                'database_version' => '1.0.0',
                'status' => 'inactive',
                'is_core' => false,
                'manifest' => ['license_tier' => 'pro'],
            ]
        );
        Extension::flushProductActiveMemo();

        // 1. Manage models endpoint is blocked
        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/infra/models/types')
            ->assertStatus(403)
            ->assertJsonPath('error_code', 'DATA_STUDIO_EXTENSION_INACTIVE');

        // 2. Dynamic EAV public endpoint is blocked
        $this->getJson('/api/v1/dynamic/non-existent-type')
            ->assertStatus(403)
            ->assertJsonPath('error_code', 'DATA_STUDIO_EXTENSION_INACTIVE');

        // Activate extension
        Extension::query()->updateOrCreate(
            ['slug' => 'data-studio'],
            [
                'status' => 'active',
            ]
        );
        Extension::flushProductActiveMemo();

        // Manage models endpoint passes through
        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/infra/models/types')
            ->assertStatus(200);
    }

    public function test_deactivation_preserves_content_types_and_dynamic_records(): void
    {
        Extension::query()->updateOrCreate(
            ['slug' => 'data-studio'],
            ['status' => 'active']
        );
        Extension::flushProductActiveMemo();

        $type = ContentType::query()->create([
            'name' => 'Test Medical Doctors',
            'slug' => 'test-doctors-'.uniqid(),
            'description' => 'Test entity for doctor directory',
            'fields' => [
                ['name' => 'Speciality', 'slug' => 'speciality', 'type' => 'text', 'required' => true],
            ],
            'is_active' => true,
        ]);

        $record = DynamicRecord::query()->create([
            'content_type_id' => $type->id,
            'data' => [
                'speciality' => 'Cardiologist',
            ],
        ]);

        // Simulate deactivation
        Extension::query()->where('slug', 'data-studio')->update(['status' => 'inactive']);
        Extension::flushProductActiveMemo();

        // Ensure both the schema and the record still exist intact
        $this->assertDatabaseHas('sys_content_types', ['id' => $type->id]);
        $this->assertDatabaseHas('sys_dynamic_records', ['id' => $record->id]);

        $reloadedRecord = DynamicRecord::query()->find($record->id);
        $this->assertNotNull($reloadedRecord);
        $this->assertEquals('Cardiologist', $reloadedRecord->data['speciality'] ?? null);

        // Cleanup
        $record->delete();
        $type->delete();
        Extension::query()->where('slug', 'data-studio')->update(['status' => 'active']);
        Extension::flushProductActiveMemo();
    }
}
