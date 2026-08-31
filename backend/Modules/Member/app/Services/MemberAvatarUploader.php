<?php

declare(strict_types=1);

namespace Modules\Member\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Core\System\Models\Extension;
use Modules\Media\Contracts\MediaServiceInterface;
use Modules\Member\Models\Member;

final class MemberAvatarUploader
{
    /**
     * Store the image and persist its URL on the member.
     */
    public function upload(Member $member, UploadedFile $file): string
    {
        $url = $this->store($member, $file);
        $member->forceFill(['avatar' => $url])->save();

        return $url;
    }

    private function store(Member $member, UploadedFile $file): string
    {
        $subPath = 'media/members/'.$member->id;

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
}
