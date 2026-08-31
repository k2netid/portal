<?php

declare(strict_types=1);

namespace Modules\Member\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\Core\System\Models\Extension;
use Modules\Media\Contracts\MediaServiceInterface;
use Modules\Media\Models\File;
use Modules\Member\Models\Member;

/**
 * Member avatar storage under media/members/{id}/.
 * External https:// URLs are never deleted — only owned public-disk paths.
 */
final class MemberAvatarUploader
{
    /**
     * Store the image, replace the member avatar, and remove the previous owned file.
     */
    public function upload(Member $member, UploadedFile $file): string
    {
        $previous = is_string($member->avatar) ? $member->avatar : null;
        $url = $this->store($member, $file);
        $member->forceFill(['avatar' => $url])->save();
        $this->discardOwned($member, $previous, keepUrl: $url);

        return $url;
    }

    /**
     * When avatar URL changes (or is cleared), drop the previous owned file if any.
     */
    public function replaceUrl(Member $member, ?string $nextUrl): void
    {
        $previous = is_string($member->avatar) ? $member->avatar : null;
        $normalized = $this->normalizeUrl($nextUrl);
        $member->forceFill(['avatar' => $normalized])->save();
        $this->discardOwned($member, $previous, keepUrl: $normalized);
    }

    /**
     * Remove every owned avatar file for this member (account force-delete).
     */
    public function purgeMemberStorage(Member $member): void
    {
        $previous = is_string($member->avatar) ? $member->avatar : null;
        $this->discardOwned($member, $previous, keepUrl: null);
        $this->deleteMemberDirectory($member);
    }

    private function store(Member $member, UploadedFile $file): string
    {
        $subPath = $this->memberPrefix($member);

        if (Extension::isProductActive('media') && app()->bound(MediaServiceInterface::class)) {
            $alt = trim((string) $member->name);
            $media = app(MediaServiceInterface::class)->upload(
                $file,
                null,
                true,
                null,
                false,
                ['alt' => $alt !== '' ? $alt.' avatar' : 'Member avatar'],
                $subPath,
                'member',
            );

            return (string) $media->url;
        }

        $path = $file->store($subPath, 'public');

        return '/storage/'.$path;
    }

    private function discardOwned(Member $member, ?string $avatarUrl, ?string $keepUrl): void
    {
        $path = $this->ownedRelativePath($member, $avatarUrl);
        if ($path === null) {
            return;
        }

        $keepPath = $this->ownedRelativePath($member, $keepUrl);
        if ($keepPath !== null && $keepPath === $path) {
            return;
        }

        $this->deleteRelativePath($path);
    }

    private function deleteMemberDirectory(Member $member): void
    {
        $prefix = $this->memberPrefix($member);

        try {
            if (class_exists(File::class)) {
                $files = File::query()
                    ->where('disk', 'public')
                    ->where('path', 'like', $prefix.'/%')
                    ->get();

                foreach ($files as $file) {
                    $this->deleteMediaRecord($file);
                }
            }

            if (Storage::disk('public')->exists($prefix)) {
                Storage::disk('public')->deleteDirectory($prefix);
            }
        } catch (\Throwable $e) {
            Log::warning('Member avatar purge failed', [
                'member_id' => $member->id,
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function deleteRelativePath(string $path): void
    {
        try {
            if (class_exists(File::class)) {
                $file = File::query()
                    ->where('disk', 'public')
                    ->where('path', $path)
                    ->first();

                if ($file instanceof File) {
                    $this->deleteMediaRecord($file);

                    return;
                }
            }

            Storage::disk('public')->delete($path);
            $dir = dirname($path);
            $base = pathinfo($path, PATHINFO_FILENAME);
            $thumb = $dir.'/thumbnails/'.$base.'_thumb.webp';
            if (Storage::disk('public')->exists($thumb)) {
                Storage::disk('public')->delete($thumb);
            }
        } catch (\Throwable $e) {
            Log::warning('Member avatar delete failed', [
                'path' => $path,
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function deleteMediaRecord(File $file): void
    {
        if (Extension::isProductActive('media') && app()->bound(MediaServiceInterface::class)) {
            app(MediaServiceInterface::class)->delete($file, permanent: true);

            return;
        }

        Storage::disk($file->disk)->delete($file->path);
        if (is_string($file->thumbnail_path) && $file->thumbnail_path !== '') {
            Storage::disk($file->disk)->delete($file->thumbnail_path);
        }
        $file->forceDelete();
    }

    private function ownedRelativePath(Member $member, ?string $avatarUrl): ?string
    {
        $relative = $this->publicRelativePath($avatarUrl);
        if ($relative === null) {
            return null;
        }

        $prefix = $this->memberPrefix($member).'/';
        if (! str_starts_with($relative, $prefix)) {
            return null;
        }

        return $relative;
    }

    private function publicRelativePath(?string $avatarUrl): ?string
    {
        $url = $this->normalizeUrl($avatarUrl);
        if ($url === null) {
            return null;
        }

        // Relative app storage URL
        if (str_starts_with($url, '/storage/')) {
            return ltrim(substr($url, strlen('/storage/')), '/');
        }

        // Absolute URL pointing at this app's /storage/…
        $path = parse_url($url, PHP_URL_PATH);
        if (is_string($path) && str_starts_with($path, '/storage/')) {
            return ltrim(substr($path, strlen('/storage/')), '/');
        }

        return null;
    }

    private function normalizeUrl(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }
        $trimmed = trim($url);

        return $trimmed === '' ? null : $trimmed;
    }

    private function memberPrefix(Member $member): string
    {
        return 'media/members/'.$member->id;
    }
}
