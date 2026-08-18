<?php

declare(strict_types=1);

namespace Modules\Core\System\Tests\Feature;

use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class EnforceDeployRoleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Config::set('deploy.role', 'unified');
    }

    public function test_deploy_role_product_instance_blocks_platform_admin_api(): void
    {
        Config::set('deploy.role', 'organization');

        $response = $this->getJson('/api/v1/manage/platform');

        $response->assertStatus(403)
            ->assertJsonPath('code', 'DEPLOY_ROLE_FORBIDDEN');
    }

    public function test_ops_plane_allows_member_public_api(): void
    {
        Config::set('deploy.role', 'ops');

        $response = $this->getJson('/api/v1/public/member/registration-policy', [
            'X-Subscription-Domain' => 'demo.jejakawan.com',
        ]);

        $response->assertStatus(422);
        $this->assertNotSame('DEPLOY_ROLE_FORBIDDEN', $response->json('code'));
    }

    public function test_ops_plane_allows_license_verify(): void
    {
        Config::set('deploy.role', 'ops');

        $response = $this->postJson('/api/v1/license/verify', []);

        $response->assertStatus(422);
    }

    public function test_unified_allows_platform_path_without_deploy_forbidden(): void
    {
        Config::set('deploy.role', 'unified');

        $response = $this->getJson('/api/v1/manage/platform');

        $this->assertNotSame('DEPLOY_ROLE_FORBIDDEN', $response->json('code'));
    }
}
