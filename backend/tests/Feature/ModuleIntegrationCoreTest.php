<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\System\Models\Permission;
use Modules\Core\System\Services\PermissionRegistry;
use Modules\Operational\Database\Seeders\AccountingSeeder;
use Tests\TestCase;

class ModuleIntegrationCoreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
        $this->seed(AccountingSeeder::class);
    }

    public function test_permission_registry_includes_accounting_permissions(): void
    {
        $registry = app(PermissionRegistry::class);
        $registry->syncToDatabase();

        $this->assertNotNull(Permission::findByName('reconcile accounting', 'web'));
        $this->assertNotNull(Permission::findByName('close accounting period', 'web'));
    }

    public function test_crm_health_endpoint(): void
    {
        $admin = $this->createAdminUser();

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/crm/health')
            ->assertOk()
            ->assertJsonPath('data.module', 'crm')
            ->assertJsonPath('data.status', 'ok');
    }

    public function test_accounting_health_endpoint(): void
    {
        $admin = $this->createAdminUser();
        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/accounting/health');

        $response->assertOk()
            ->assertJsonPath('data.module', 'accounting')
            ->assertJsonPath('data.checks.ledger.status', 'ok');
    }
}
