<?php

declare(strict_types=1);

namespace Modules\Mail\Http\Controllers\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Core\System\Models\Setting;
use Modules\Core\System\Models\User;
use Modules\Mail\Models\MailMessage;
use Modules\Mail\Services\UserMailRepository;

trait InteractsWithUserMailbox
{
    protected function mailRepo(Request $request): UserMailRepository|JsonResponse
    {
        $user = $this->resolveConsoleUser($request);
        if (! $user instanceof User) {
            return $this->unauthorized();
        }

        return new UserMailRepository($user);
    }

    protected function userSettingKey(User $user, string $base): string
    {
        return $base.'_user_'.$user->id;
    }

    /**
     * @return array<string, mixed>
     */
    protected function calculateStorageStatsForUser(User $user): array
    {
        $query = MailMessage::query()->where('user_id', $user->id);
        $bodyBytes = (int) (clone $query)->sum(DB::raw('LENGTH(body) + LENGTH(COALESCE(snippet, \'\'))'));
        $count = (clone $query)->count();
        $overhead = $count * 2048;
        $usedBytes = max(24576, $bodyBytes + $overhead);

        $quotaGbRaw = Setting::where('key', 'mail_client_storage_quota_gb')->value('value') ?? 15;
        $quotaGb = is_numeric($quotaGbRaw) ? (int) $quotaGbRaw : 15;
        $quotaBytes = $quotaGb * 1024 * 1024 * 1024;
        $percentage = $quotaBytes > 0 ? min(100.0, round(($usedBytes / $quotaBytes) * 100, 2)) : 0.0;

        return [
            'used_bytes' => $usedBytes,
            'quota_bytes' => $quotaBytes,
            'used_formatted' => $this->formatBytes($usedBytes),
            'quota_formatted' => "{$quotaGb} GB",
            'percentage' => $percentage,
        ];
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return round($bytes / 1073741824, 2).' GB';
        }
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2).' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 2).' KB';
        }

        return $bytes.' B';
    }

    protected function ownedMessage(Request $request, string $id): MailMessage|JsonResponse
    {
        $repo = $this->mailRepo($request);
        if ($repo instanceof JsonResponse) {
            return $repo;
        }

        $message = $repo->findMessage($id);
        if (! $message instanceof MailMessage) {
            return $this->error('Message not found', 404);
        }

        return $message;
    }
}
