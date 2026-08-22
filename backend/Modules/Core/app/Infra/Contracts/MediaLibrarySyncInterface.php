<?php

declare(strict_types=1);

namespace Modules\Core\Infra\Contracts;

/**
 * Optional downstream media library sync (e.g. Content module MediaService).
 *
 * Downstream apps that ship Content/Media should implement this on MediaService.
 */
interface MediaLibrarySyncInterface
{
    public function shouldIndexPublicPath(string $filePath): bool;

    public function registerFromDisk(
        string $disk,
        string $filePath,
        ?string $authorId,
        bool $isImage,
    ): ?MediaFileRecordInterface;

    public function delete(MediaFileRecordInterface $media, bool $permanent): void;

    public function moveVariantsToTrash(MediaFileRecordInterface $media, string $trashPath): void;

    public function moveVariantsFromTrash(
        MediaFileRecordInterface $media,
        string $fromPath,
        string $toPath,
    ): void;

    public function syncAfterMove(
        string $disk,
        string $source,
        string $newPath,
        bool $isFolder,
    ): void;

    public function syncAfterCopy(
        string $disk,
        string $newPath,
        bool $isFolder,
        ?string $authorId,
    ): void;
}
