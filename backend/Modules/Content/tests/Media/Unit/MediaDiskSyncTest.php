<?php

declare(strict_types=1);

namespace Modules\Content\Media\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Modules\Content\Media\Models\File;
use Modules\Content\Media\Services\MediaService;
use Tests\TestCase;

class MediaDiskSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_should_index_only_media_paths_on_public_disk(): void
    {
        $service = new MediaService;

        $this->assertTrue($service->shouldIndexPublicPath('media/2024/photo.jpg'));
        $this->assertFalse($service->shouldIndexPublicPath('themes/janari/logo.png'));
        $this->assertFalse($service->shouldIndexPublicPath('.trash/file.jpg'));
    }

    public function test_sync_after_move_updates_media_path(): void
    {
        Storage::fake('public');

        Storage::disk('public')->put('media/old/photo.jpg', 'bytes');

        $file = File::withoutGlobalScopes()->create([
            'module' => 'system',
            'name' => 'photo',
            'file_name' => 'photo.jpg',
            'mime_type' => 'image/jpeg',
            'disk' => 'public',
            'path' => 'media/old/photo.jpg',
            'size' => 5,
            'is_shared' => true,
        ]);

        Storage::disk('public')->move('media/old/photo.jpg', 'media/new/photo.jpg');

        $service = new MediaService;
        $service->syncAfterMove('public', 'media/old/photo.jpg', 'media/new/photo.jpg', false);

        $file->refresh();
        $this->assertSame('media/new/photo.jpg', $file->path);
    }
}
