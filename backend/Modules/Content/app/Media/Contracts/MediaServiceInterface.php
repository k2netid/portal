<?php

declare(strict_types=1);

namespace Modules\Content\Media\Contracts;

use Illuminate\Http\UploadedFile;
use Modules\Content\Media\Models\File;

interface MediaServiceInterface
{
    /**
     * Upload and process a media file.
     *
     * @param  array<string, mixed>  $metadata
     */
    public function upload(
        UploadedFile $file,
        ?string $folderId = null,
        bool $optimize = true,
        ?string $authorId = null,
        bool $isShared = false,
        array $metadata = [],
        ?string $subPath = null,
        string $module = 'system'
    ): File;

    /**
     * Register an existing file on disk in the media library (idempotent by disk + path).
     */
    public function registerFromDisk(
        string $disk,
        string $relativePath,
        ?string $authorId = null,
        bool $generateThumbnail = false,
        string $module = 'system'
    ): ?File;

    /**
     * Optimize an image file.
     */
    public function optimizeImage(string $fullPath, int $maxWidth = 1920, int $quality = 85): bool;

    /**
     * Convert an image to WebP format.
     */
    public function convertToWebP(string $fullPath, int $quality = 85): ?string;

    /**
     * Generate thumbnail for media.
     */
    public function generateThumbnail(File $file, ?int $width = null, ?int $height = null): ?string;

    /**
     * Resize an image.
     */
    public function resize(File $file, int $width, ?int $height = null, int $quality = 85): bool;

    /**
     * Delete media.
     */
    public function delete(File $file, bool $permanent = false): void;

    /**
     * Restore a soft-deleted media item.
     */
    public function restore(string $fileId): ?File;

    /**
     * Perform bulk action on media.
     *
     * @param  array<int, string>  $mediaIds
     * @param  array<int, string>  $folderIds
     * @return array{media_count: int, folder_count: int}
     */
    public function bulkAction(string $action, array $mediaIds, ?string $folderId = null, ?string $altText = null, array $folderIds = []): array;

    /**
     * Replace tags linked to a media file.
     *
     * @param  array<int|string, mixed>  $tags
     */
    public function syncTags(File $file, array $tags): void;

    /**
     * Create ZIP download from multiple media.
     *
     * @param  array<int, string>  $mediaIds
     */
    public function createZip(array $mediaIds): ?string;

    /**
     * Get usage information for media.
     *
     * @return array<int, mixed>
     */
    public function getUsageInfo(File $file): array;

    /**
     * Get media statistics.
     *
     * @return array{total_count: int, total_size: int, types: array<int, mixed>, trash_count: int}
     */
    public function getStatistics(): array;
}
