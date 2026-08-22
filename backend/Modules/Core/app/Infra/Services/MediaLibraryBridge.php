<?php

declare(strict_types=1);

namespace Modules\Core\Infra\Services;

use Modules\Core\Infra\Contracts\MediaFileRecordInterface;
use Modules\Core\Infra\Contracts\MediaLibrarySyncInterface;

/**
 * Typed bridge to optional Content/Media module — absent in kernel-only installs.
 */
class MediaLibraryBridge
{
    private const MEDIA_SERVICE_CLASS = 'Modules\Content\Media\Services\MediaService';

    private const MEDIA_FILE_CLASS = 'Modules\Content\Media\Models\File';

    public function sync(): ?MediaLibrarySyncInterface
    {
        if (! class_exists(self::MEDIA_SERVICE_CLASS) || ! app()->bound(self::MEDIA_SERVICE_CLASS)) {
            return null;
        }

        $service = app(self::MEDIA_SERVICE_CLASS);

        return $service instanceof MediaLibrarySyncInterface ? $service : null;
    }

    public function isFileModelAvailable(): bool
    {
        return class_exists(self::MEDIA_FILE_CLASS);
    }

    public function findByPath(string $path): ?MediaFileRecordInterface
    {
        if (! $this->isFileModelAvailable()) {
            return null;
        }

        $record = self::MEDIA_FILE_CLASS::where(function ($q) use ($path): void {
            $q->where('path', $path)
                ->orWhere('path', '/'.$path)
                ->orWhere('path', trim($path, '/'));
        })->first();

        return $record instanceof MediaFileRecordInterface ? $record : null;
    }

    /**
     * @return list<MediaFileRecordInterface>
     */
    public function findByFolderPath(string $folderPath): array
    {
        if (! $this->isFileModelAvailable()) {
            return [];
        }

        $searchPath = rtrim($folderPath, '/').'/';

        return $this->filterRecords(
            self::MEDIA_FILE_CLASS::where('path', 'like', $searchPath.'%')
                ->orWhere('path', 'like', '/'.$searchPath.'%')
                ->get(),
        );
    }

    public function findTrashedByTrashPath(string $trashPath): ?MediaFileRecordInterface
    {
        if (! $this->isFileModelAvailable()) {
            return null;
        }

        $record = self::MEDIA_FILE_CLASS::withTrashed()
            ->where(function ($q) use ($trashPath): void {
                $q->where('path', $trashPath)
                    ->orWhere('path', '/'.$trashPath);
            })
            ->first();

        return $record instanceof MediaFileRecordInterface ? $record : null;
    }

    /**
     * @return list<MediaFileRecordInterface>
     */
    public function findTrashedByFolderTrash(string $trashPath): array
    {
        if (! $this->isFileModelAvailable()) {
            return [];
        }

        return $this->filterRecords(
            self::MEDIA_FILE_CLASS::withTrashed()
                ->where('path', 'like', $trashPath.'%')
                ->orWhere('path', 'like', '/'.$trashPath.'%')
                ->get(),
        );
    }

    /**
     * @param  iterable<mixed>  $records
     * @return list<MediaFileRecordInterface>
     */
    private function filterRecords(iterable $records): array
    {
        $filtered = [];

        foreach ($records as $record) {
            if ($record instanceof MediaFileRecordInterface) {
                $filtered[] = $record;
            }
        }

        return $filtered;
    }
}
