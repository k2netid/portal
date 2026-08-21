<?php

declare(strict_types=1);

namespace Modules\Core\System\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileManagerApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
        Storage::fake('public');
    }

    public function test_admin_can_list_files_and_directories(): void
    {
        $admin = $this->createAdminUser();

        Storage::disk('public')->put('documents/test.txt', 'Hello World');

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/infra/file-manager?disk=public&path=');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'files',
                    'folders',
                    'path',
                ],
            ]);
    }

    public function test_admin_can_create_folder_and_upload_file(): void
    {
        $admin = $this->createAdminUser();

        // 1. Create Folder
        $folderResponse = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/manage/infra/file-manager/folder', [
                'disk' => 'public',
                'path' => '',
                'name' => 'uploads',
            ]);

        $folderResponse->assertCreated();
        Storage::disk('public')->assertExists('uploads');

        // 2. Upload File
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $uploadResponse = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/manage/infra/file-manager/upload', [
                'disk' => 'public',
                'path' => 'uploads',
                'files' => [$file],
            ]);

        $uploadResponse->assertCreated();
    }

    public function test_admin_can_rename_and_delete_file(): void
    {
        $admin = $this->createAdminUser();

        Storage::disk('public')->put('sample.txt', 'Sample text');

        // Rename
        $renameResponse = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/manage/infra/file-manager/rename', [
                'disk' => 'public',
                'path' => 'sample.txt',
                'newName' => 'renamed_sample.txt',
                'type' => 'file',
            ]);

        $renameResponse->assertOk();
        Storage::disk('public')->assertExists('renamed_sample.txt');
        Storage::disk('public')->assertMissing('sample.txt');

        // Delete permanently
        $deleteResponse = $this->actingAs($admin, 'sanctum')
            ->deleteJson('/api/v1/manage/infra/file-manager', [
                'disk' => 'public',
                'path' => 'renamed_sample.txt',
                'permanent' => true,
            ]);

        $deleteResponse->assertOk();
    }

    public function test_path_traversal_is_blocked(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/infra/file-manager?disk=public&path=../../etc');

        $response->assertForbidden();
    }

    public function test_unauthenticated_cannot_access_file_manager(): void
    {
        $this->getJson('/api/v1/manage/infra/file-manager')->assertUnauthorized();
        $this->postJson('/api/v1/manage/infra/file-manager/upload', [])->assertUnauthorized();
        $this->postJson('/api/v1/manage/infra/file-manager/folder', [])->assertUnauthorized();
        $this->deleteJson('/api/v1/manage/infra/file-manager', [])->assertUnauthorized();
    }
}
