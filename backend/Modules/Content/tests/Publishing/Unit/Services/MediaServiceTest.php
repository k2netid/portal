<?php

namespace Modules\Content\Publishing\Tests\Unit\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Content\Media\Models\File;
use Modules\Content\Media\Models\Folder;
use Modules\Content\Media\Services\MediaService;
use Modules\Core\System\Models\Setting;
use Tests\TestCase;

class MediaServiceTest extends TestCase
{
    use RefreshDatabase;

    protected MediaService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Setting::set('media_auto_convert_webp', 'false', 'boolean', 'media');
        $this->service = new MediaService;
    }

    public function test_upload_image(): void
    {
        $file = UploadedFile::fake()->image('test.jpg', 600, 600);

        $media = $this->service->upload($file);

        $this->assertInstanceOf(File::class, $media);
        $this->assertStringContainsString('test', $media->file_name);
        $this->assertStringStartsWith('image/', $media->mime_type);
        Storage::disk('public')->assertExists($media->path);
    }

    public function test_upload_to_folder(): void
    {
        $folder = Folder::factory()->create();
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $media = $this->service->upload($file, $folder->id);

        $this->assertEquals($folder->id, $media->folder_id);
        Storage::disk('public')->assertExists($media->path);
    }

    public function test_optimize_image(): void
    {
        $file = UploadedFile::fake()->image('large.jpg', 2000, 2000);
        $tempPath = $file->getRealPath();
        $this->assertNotFalse($tempPath);

        $method = new \ReflectionMethod(MediaService::class, 'optimizeImage');
        $method->invoke($this->service, $tempPath, 1000);

        $size = getimagesize($tempPath);
        $this->assertNotFalse($size);
        $this->assertLessThanOrEqual(1000, $size[0]);
    }

    public function test_convert_to_webp(): void
    {
        $file = UploadedFile::fake()->image('convert.jpg');
        $tempPath = $file->getRealPath();
        $this->assertNotFalse($tempPath);

        $method = new \ReflectionMethod(MediaService::class, 'convertToWebP');
        $resultPath = $method->invoke($this->service, $tempPath);

        $this->assertNotNull($resultPath);
        $this->assertFileExists($resultPath);
        $this->assertEquals('image/webp', mime_content_type($resultPath));
    }

    public function test_generate_thumbnail(): void
    {
        $file = UploadedFile::fake()->image('photo.jpg', 800, 600);
        $media = $this->service->upload($file);

        $thumbnailPath = $this->service->generateThumbnail($media, 150, 150);

        $this->assertNotNull($thumbnailPath);
        Storage::disk('public')->assertExists($thumbnailPath);
    }

    public function test_delete_media_soft(): void
    {
        $file = UploadedFile::fake()->image('to_delete.jpg');
        $media = $this->service->upload($file);

        $this->service->delete($media);

        $this->assertSoftDeleted('srv_media_files', ['id' => $media->id]);
    }

    public function test_delete_media_permanent(): void
    {
        $file = UploadedFile::fake()->image('permanent.jpg');
        $media = $this->service->upload($file);
        $path = $media->path;

        $this->service->delete($media, true);

        $this->assertDatabaseMissing('srv_media_files', ['id' => $media->id]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_restore_media(): void
    {
        $file = UploadedFile::fake()->image('restore.jpg');
        $media = $this->service->upload($file);
        $this->service->delete($media);

        $restored = $this->service->restore($media->id);

        $this->assertNotNull($restored);
        $this->assertFalse($restored->trashed());
    }

    public function test_bulk_action_move(): void
    {
        $media1 = File::factory()->create();
        $media2 = File::factory()->create();
        $folder = Folder::factory()->create();

        $result = $this->service->bulkAction('move', [$media1->id, $media2->id], $folder->id);

        $this->assertEquals(2, $result['media_count']);
        $this->assertEquals($folder->id, $media1->fresh()->folder_id);
        $this->assertEquals($folder->id, $media2->fresh()->folder_id);
    }

    public function test_bulk_action_restore(): void
    {
        $media = File::factory()->create();
        $this->service->delete($media);
        $this->assertTrue($media->fresh()->trashed());

        $result = $this->service->bulkAction('restore', [$media->id]);

        $this->assertEquals(1, $result['media_count']);
        $this->assertFalse($media->fresh()->trashed());
    }

    public function test_create_zip(): void
    {
        $media1 = File::factory()->create(['path' => 'media/1.txt', 'file_name' => '1.txt']);
        $media2 = File::factory()->create(['path' => 'media/2.txt', 'file_name' => '2.txt']);
        Storage::disk('public')->put($media1->path, 'content1');
        Storage::disk('public')->put($media2->path, 'content2');

        $zipPath = $this->service->createZip([$media1->id, $media2->id]);

        $this->assertNotNull($zipPath);
        $this->assertFileExists($zipPath);
        $this->assertEquals('application/zip', mime_content_type($zipPath));

        if (file_exists($zipPath)) {
            unlink($zipPath);
        }
    }

    public function test_create_zip_empty(): void
    {
        $this->assertNull($this->service->createZip([]));
    }

    public function test_get_usage_info(): void
    {
        $media = File::factory()->create();
        $info = $this->service->getUsageInfo($media);

        $this->assertIsArray($info);
    }

    public function test_resize_with_height(): void
    {
        $file = UploadedFile::fake()->image('resize.jpg', 800, 600);
        $media = $this->service->upload($file);

        $result = $this->service->resize($media, 400, 300);

        $this->assertTrue($result);
        $size = getimagesize(Storage::disk('public')->path($media->path));
        $this->assertNotFalse($size);
        $this->assertEquals(400, $size[0]);
    }

    public function test_sanitize_svg(): void
    {
        $svgContent = '<svg><script>alert(1)</script><path d="M10 10"/></svg>';
        Storage::disk('public')->put('test.svg', $svgContent);
        $fullPath = Storage::disk('public')->path('test.svg');

        $method = new \ReflectionMethod(MediaService::class, 'sanitizeSvg');
        $method->invoke($this->service, $fullPath);

        $sanitized = Storage::disk('public')->get('test.svg');
        $this->assertStringNotContainsString('<script>', $sanitized);
    }

    public function test_convert_to_webp_failure(): void
    {
        $file = UploadedFile::fake()->create('test.txt', 10);
        $tempPath = $file->getRealPath();
        $this->assertNotFalse($tempPath);

        $method = new \ReflectionMethod(MediaService::class, 'convertToWebP');
        $result = $method->invoke($this->service, $tempPath);

        $this->assertNull($result);
    }
}
