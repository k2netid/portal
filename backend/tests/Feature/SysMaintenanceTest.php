<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Modules\Core\System\Models\Role;
use Modules\Core\System\Models\User;
use Tests\TestCase;

class SysMaintenanceTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected User $normalUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles
        $superRole = Role::firstOrCreate(['name' => 'super', 'guard_name' => 'web']);
        $memberRole = Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

        // 1. Create a Super Admin account
        $this->superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@jejakawan.test',
            'password' => 'password123',
        ]);
        $this->superAdmin->assignRole($superRole);

        // 2. Create a Regular User account
        $this->normalUser = User::create([
            'name' => 'Regular Member',
            'email' => 'member@jejakawan.test',
            'password' => 'password123',
        ]);
        $this->normalUser->assignRole($memberRole);
    }

    /**
     * Test junk cleaner removes temporary files.
     */
    public function test_junk_cleaner_removes_temp_files(): void
    {
        // Setup dummy temporary folder and files
        $tempDir = storage_path('app/scaffolds');
        if (! File::isDirectory($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }
        $dummyFile = "{$tempDir}/dummy-scaffold-temp.zip";
        File::put($dummyFile, 'content');

        $this->assertTrue(File::exists($dummyFile));

        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->postJson('/api/v1/manage/system/maintenance/clean-junk');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        // Assert file is cleaned up
        $this->assertFalse(File::exists($dummyFile));
    }

    /**
     * Test regular user cannot trigger junk cleaning.
     */
    public function test_regular_user_denied_junk_cleaning(): void
    {
        $response = $this->actingAs($this->normalUser, 'sanctum')
            ->postJson('/api/v1/manage/system/maintenance/clean-junk');

        $response->assertStatus(403);
    }

    /**
     * Test database optimizer clears orphan dynamic records.
     */
    public function test_database_optimizer_clears_orphans(): void
    {
        if (Schema::hasTable('sys_content_types') && Schema::hasTable('sys_dynamic_records')) {
            $response = $this->actingAs($this->superAdmin, 'sanctum')
                ->postJson('/api/v1/manage/system/maintenance/optimize-db');

            $response->assertStatus(200)
                ->assertJsonPath('success', true)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'optimized_tables',
                        'purged_orphans',
                    ],
                ]);

            $this->assertIsInt($response->json('data.purged_orphans'));
            $this->assertIsInt($response->json('data.optimized_tables'));
        } else {
            $this->markTestSkipped('Dynamic content types and dynamic records tables do not exist.');
        }
    }

    /**
     * Test performance booster caches.
     */
    public function test_performance_booster_cache_regeneration(): void
    {
        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->postJson('/api/v1/manage/system/maintenance/boost');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    /**
     * Test factory reset safety authentication check.
     */
    public function test_factory_reset_wrong_password_fails(): void
    {
        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->postJson('/api/v1/manage/system/maintenance/factory-reset', [
                'password' => 'wrongpassword',
            ]);

        $response->assertStatus(403)
            ->assertJsonPath('success', false);
    }

    /**
     * Test factory reset returns validation error when password is empty.
     */
    public function test_factory_reset_missing_password_fails(): void
    {
        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->postJson('/api/v1/manage/system/maintenance/factory-reset', []);

        $response->assertStatus(422);
    }
}
