<?php

declare(strict_types=1);

namespace Modules\Core\System\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemDiagnosticsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
    }

    public function test_admin_can_fetch_system_info(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/system/info');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'php_version',
                    'laravel_version',
                    'server',
                    'server_software',
                    'database',
                    'database_version',
                    'memory_usage',
                    'disk_usage',
                    'uptime',
                    'queue_health',
                ],
            ]);
    }

    public function test_admin_can_fetch_system_requirements(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/system/requirements');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'overview' => [
                        'total',
                        'passed',
                        'warnings',
                        'errors',
                        'score_percent',
                        'is_ready',
                    ],
                    'server_spec' => [
                        'distro',
                        'php_version',
                        'database_engine',
                        'database_version',
                    ],
                    'items',
                ],
            ]);
    }

    public function test_admin_can_autofix_requirements(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/manage/system/requirements/autofix');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'fixed',
                    'failed',
                    'message',
                ],
            ]);
    }

    public function test_unauthenticated_user_cannot_access_system_diagnostics(): void
    {
        $this->getJson('/api/v1/manage/system/info')->assertUnauthorized();
        $this->getJson('/api/v1/manage/system/requirements')->assertUnauthorized();
        $this->postJson('/api/v1/manage/system/requirements/autofix')->assertUnauthorized();
    }
}
