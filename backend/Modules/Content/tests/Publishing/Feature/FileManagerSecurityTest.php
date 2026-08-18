<?php

namespace Modules\Content\Publishing\Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Content\Media\Models\File as MediaFile;
use Modules\Core\System\Models\Permission;
use Modules\Core\System\Models\Role;
use Modules\Core\System\Models\User;
use Tests\TestCase;

class FileManagerSecurityTest extends TestCase
{
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();

        // Reserve ID 1 for superadmin test
        User::factory()->create(['id' => 1]);

        // Create normal admin with permission (Force ID 2)
        $this->admin = User::factory()->create(['id' => 2]);
        $permission = Permission::firstOrCreate(['name' => 'manage files']);
        $this->admin->givePermissionTo($permission);
    }

    public function test_cannot_access_unauthorized_disk(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/manage/infra/file-manager?disk=local');

        // Should return validation error for 'disk'
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['disk']);
    }

    public function test_cannot_traverse_path_in_list(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/manage/infra/file-manager?path=../private');

        $response->assertStatus(403);
    }

    public function test_cannot_upload_to_traversal_path(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('test.jpg');

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/manage/infra/file-manager/upload', [
                'file' => $file,
                'path' => '../',
                'disk' => 'public',
            ]);

        $response->assertStatus(403);
    }

    public function test_cannot_move_files_to_restricted_disk(): void
    {
        Storage::fake('public');
        Storage::put('test.txt', 'content');

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/manage/infra/file-manager/move', [
                'source' => 'test.txt',
                'destination' => 'new.txt',
                'type' => 'file',
                'disk' => 'local', // Restricted
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['disk']);
    }

    public function test_super_admin_can_access_any_disk(): void
    {
        // User ID 1: needs super-admin role so FileManagerController allows any configured disk.
        $superAdmin = User::find(1);
        $superAdmin->givePermissionTo('manage files');
        $superAdminRole = Role::firstOrCreate(['name' => 'super', 'guard_name' => 'web']);
        $superAdmin->assignRole($superAdminRole);

        // Mock local disk to avoid real error
        Storage::fake('local');

        // Use correct URL for index
        $response = $this->actingAs($superAdmin, 'sanctum')
            ->getJson('/api/v1/manage/infra/file-manager?disk=local');

        // Should NOT be 422.
        $response->assertSuccessful();
    }

    public function test_upload_registers_media_library_record_on_public_disk(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('gallery.jpg');

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/manage/infra/file-manager/upload', [
                'file' => $file,
                'path' => 'media',
                'disk' => 'public',
            ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.files.0.media_id', fn ($id) => $id !== null);

        $this->assertDatabaseHas('srv_media_files', [
            'disk' => 'public',
            'path' => 'media/gallery.jpg',
        ]);

        $this->assertSame(1, MediaFile::withoutGlobalScopes()->where('path', 'media/gallery.jpg')->count());
    }
}
