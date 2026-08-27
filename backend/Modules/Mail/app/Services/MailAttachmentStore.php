<?php

declare(strict_types=1);

namespace Modules\Mail\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Modules\Core\Infra\Services\MediaLibraryBridge;
use Modules\Core\System\Models\User;

class MailAttachmentStore
{
    private const DISK = 'local';

    private const MAX_FILES = 10;

    private const MAX_BYTES = 10_485_760; // 10 MB each

    /** High-risk extensions blocked on outbound attach (industry blocklist approach). */
    private const BLOCKED_EXTENSIONS = [
        'exe', 'bat', 'cmd', 'com', 'cpl', 'scr', 'js', 'jse', 'vbs', 'vbe', 'wsf', 'wsh',
        'ps1', 'msi', 'dll', 'jar', 'hta', 'msp', 'msc', 'pif', 'reg', 'inf', 'lnk',
        'iso', 'img', 'dmg',
    ];

    /** Dangerous MIME types even when the filename extension is renamed. */
    private const BLOCKED_MIME_TYPES = [
        'application/x-msdownload',
        'application/x-msdos-program',
        'application/x-ms-installer',
        'application/x-dosexec',
        'application/x-executable',
        'application/x-msi',
        'application/vnd.microsoft.portable-executable',
        'application/x-sh',
        'application/x-bat',
        'application/x-csh',
        'application/javascript',
        'text/javascript',
        'application/x-java-archive',
        'application/java-archive',
    ];

    public function __construct(
        protected MediaLibraryBridge $mediaBridge,
    ) {}

    /**
     * @param  array<int, UploadedFile|mixed>  $files
     * @return list<array{name: string, size: int, mime: string, path: string, disk: string, media_id?: int|string|null}>
     */
    public function storeMany(User $user, array $files): array
    {
        $stored = [];

        foreach (array_slice(array_values($files), 0, self::MAX_FILES) as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            if ($file->getSize() > self::MAX_BYTES) {
                continue;
            }

            $original = $file->getClientOriginalName();
            $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
            if ($ext !== '' && in_array($ext, self::BLOCKED_EXTENSIONS, true)) {
                throw new InvalidArgumentException('Attachment type not allowed: .'.$ext);
            }

            $clientMime = strtolower((string) ($file->getClientMimeType() ?: ''));
            $detectedMime = strtolower((string) ($file->getMimeType() ?: 'application/octet-stream'));
            if ($this->isBlockedMime($clientMime) || $this->isBlockedMime($detectedMime)) {
                throw new InvalidArgumentException('Attachment MIME type not allowed: '.($detectedMime !== '' ? $detectedMime : $clientMime));
            }

            $safeName = Str::limit(preg_replace('/[^\w.\-]+/', '_', $original) ?: 'file.bin', 180, '');
            $relative = 'mail-attachments/'.$user->id.'/'.Str::uuid()->toString().'/'.$safeName;
            Storage::disk(self::DISK)->putFileAs(
                dirname($relative),
                $file,
                basename($relative),
            );

            $mime = (string) ($file->getMimeType() ?: 'application/octet-stream');
            $meta = [
                'name' => $original,
                'size' => (int) $file->getSize(),
                'mime' => $mime,
                'path' => $relative,
                'disk' => self::DISK,
            ];

            $mediaId = $this->tryRegisterWithMediaLibrary($relative, $user->id, $mime);
            if ($mediaId !== null) {
                $meta['media_id'] = $mediaId;
            }

            $stored[] = $meta;
        }

        return $stored;
    }

    public function isOwnedPath(string $userId, string $path): bool
    {
        $normalized = ltrim(str_replace('\\', '/', $path), '/');
        if ($normalized === '' || str_contains($normalized, '..')) {
            return false;
        }

        return str_starts_with($normalized, 'mail-attachments/'.$userId.'/');
    }

    /**
     * @param  array<int, array<string, mixed>>  $attachments
     */
    public function deleteStored(array $attachments): void
    {
        $sync = $this->mediaBridge->sync();

        foreach ($attachments as $attachment) {
            if (! is_array($attachment)) {
                continue;
            }

            $path = is_string($attachment['path'] ?? null) ? $attachment['path'] : '';
            $disk = is_string($attachment['disk'] ?? null) ? $attachment['disk'] : self::DISK;

            if ($path !== '' && Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
            }

            if ($sync === null) {
                continue;
            }

            $mediaId = $attachment['media_id'] ?? null;
            if ($mediaId === null && $path !== '') {
                $record = $this->mediaBridge->findByPath($path);
                if ($record !== null) {
                    try {
                        $sync->delete($record, true);
                    } catch (\Throwable $e) {
                        Log::debug('Mail attachment media delete skipped', ['error' => $e->getMessage()]);
                    }
                }

                continue;
            }
        }
    }

    /**
     * @param  list<array<string, mixed>>  $attachments
     * @return list<array{path: string, name: string, mime: string}>
     */
    public function absoluteAttachSpecs(array $attachments): array
    {
        $specs = [];

        foreach ($attachments as $attachment) {
            if (! is_array($attachment)) {
                continue;
            }

            $path = is_string($attachment['path'] ?? null) ? $attachment['path'] : '';
            $disk = is_string($attachment['disk'] ?? null) ? $attachment['disk'] : self::DISK;
            $name = is_string($attachment['name'] ?? null) ? $attachment['name'] : basename($path);
            $mime = is_string($attachment['mime'] ?? null) ? $attachment['mime'] : 'application/octet-stream';

            if ($path === '' || ! Storage::disk($disk)->exists($path)) {
                continue;
            }

            $specs[] = [
                'path' => Storage::disk($disk)->path($path),
                'name' => $name,
                'mime' => $mime,
            ];
        }

        return $specs;
    }

    /**
     * @param  list<array<string, mixed>>  $attachments
     * @return list<array<string, mixed>>
     */
    public function withPublicMeta(array $attachments, string $messageId): array
    {
        $out = [];

        foreach (array_values($attachments) as $index => $attachment) {
            if (! is_array($attachment)) {
                continue;
            }

            $out[] = array_merge($attachment, [
                'url' => '/api/v1/manage/mail/messages/'.$messageId.'/attachments/'.$index,
                'index' => $index,
            ]);
        }

        return $out;
    }

    private function tryRegisterWithMediaLibrary(string $relativePath, string $authorId, string $mime): int|string|null
    {
        $sync = $this->mediaBridge->sync();
        if ($sync === null) {
            return null;
        }

        try {
            if (! $sync->shouldIndexPublicPath($relativePath)) {
                return null;
            }

            $record = $sync->registerFromDisk(
                self::DISK,
                $relativePath,
                $authorId,
                str_starts_with($mime, 'image/'),
            );

            return $record?->getId();
        } catch (\Throwable $e) {
            Log::debug('Mail attachment media register skipped', [
                'path' => $relativePath,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function isBlockedMime(string $mime): bool
    {
        $mime = strtolower(trim($mime));
        if ($mime === '') {
            return false;
        }

        $base = strtolower(trim(explode(';', $mime, 2)[0]));

        return in_array($base, self::BLOCKED_MIME_TYPES, true);
    }
}
