<?php

declare(strict_types=1);

namespace Modules\Core\System\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RedisApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
    }

    public function test_admin_can_fetch_redis_settings(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/redis/settings');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    }

    public function test_admin_can_update_redis_settings(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin, 'sanctum')
            ->putJson('/api/v1/manage/redis/settings', [
                'settings' => [
                    ['key' => 'redis_host', 'value' => '127.0.0.1'],
                    ['key' => 'redis_port', 'value' => 6379],
                    ['key' => 'redis_cache_db', 'value' => '1'],
                ],
            ]);

        $response->assertOk();
    }

    public function test_admin_can_fetch_redis_info_and_cache_stats(): void
    {
        $admin = $this->createAdminUser();

        $infoResponse = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/redis/info');

        $infoResponse->assertOk()
            ->assertJsonStructure([
                'success',
                'data',
            ]);

        $statsResponse = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/redis/cache-stats');

        $statsResponse->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_keys',
                    'cache_size',
                    'expired_keys',
                    'top_keys',
                    'key_prefix',
                ],
            ]);
    }

    public function test_admin_can_search_redis_keys(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/redis/keys?connection=cache&pattern=*');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'pattern',
                    'connection',
                    'total_found',
                    'items',
                ],
            ]);
    }

    public function test_unauthenticated_cannot_access_redis_management(): void
    {
        $this->getJson('/api/v1/manage/redis/settings')->assertUnauthorized();
        $this->getJson('/api/v1/manage/redis/info')->assertUnauthorized();
        $this->getJson('/api/v1/manage/redis/cache-stats')->assertUnauthorized();
        $this->getJson('/api/v1/manage/redis/keys')->assertUnauthorized();
        $this->postJson('/api/v1/manage/redis/flush-cache', [])->assertUnauthorized();
        $this->postJson('/api/v1/manage/redis/warm-cache', [])->assertUnauthorized();
    }
}
