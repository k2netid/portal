<?php

declare(strict_types=1);

namespace Modules\Mail\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Core\System\Models\User;

class MailAttachmentStore
{
    private const DISK = 'local';

    private const MAX_FILES = 10;

    private const MAX_BYTES = 10_485_760; // 10 MB each

    /**
     * @param  array<int, UploadedFile|mixed>  $files
     * @return list<array{name: string, size: int, mime: string, path: string, disk: string}>
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

            $safeName = Str::limit(preg_replace('/[^\w.\-]+/', '_', $file->getClientOriginalName()) ?: 'file.bin', 180, '');
            $relative = 'mail-attachments/'.$user->id.'/'.Str::uuid()->toString().'/'.$safeName;
            Storage::disk(self::DISK)->putFileAs(
                dirname($relative),
                $file,
                basename($relative),
            );

            $stored[] = [
                'name' => $file->getClientOriginalName(),
                'size' => (int) $file->getSize(),
                'mime' => (string) ($file->getMimeType() ?: 'application/octet-stream'),
                'path' => $relative,
                'disk' => self::DISK,
            ];
        }

        return $stored;
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
}
