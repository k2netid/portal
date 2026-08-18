<?php

declare(strict_types=1);

namespace Modules\Core\System\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Modules\Core\System\Models\Notification;
use Modules\Operational\Member\Models\Member;
use Modules\Operational\Member\Models\MemberNotification;

class BroadcastNotificationService
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function deliver(array $payload): void
    {
        $title = is_string($payload['title'] ?? null) ? $payload['title'] : 'Notification';
        $message = is_string($payload['message'] ?? null) ? $payload['message'] : '';
        $type = is_string($payload['type'] ?? null) ? $payload['type'] : 'info';
        $targetType = is_string($payload['target_type'] ?? null) ? $payload['target_type'] : 'all';
        $broadcastId = is_string($payload['broadcast_id'] ?? null) && $payload['broadcast_id'] !== ''
            ? (string) $payload['broadcast_id']
            : (string) Str::uuid();
        $actionUrl = is_string($payload['action_url'] ?? null) ? $payload['action_url'] : null;
        $meta = array_merge($payload, ['broadcast_id' => $broadcastId]);
        $memberSourceKey = 'broadcast-'.$broadcastId;

        if ($targetType === 'all') {
            Notification::createForAll($type, $title, $message, $actionUrl, null, $meta);
            $this->deliverToAllMembers($type, $title, $message, $actionUrl, $memberSourceKey);

            return;
        }

        if ($targetType === 'user' && ! empty($payload['target_id'])) {
            $targetId = is_scalar($payload['target_id'] ?? null) ? (string) $payload['target_id'] : '';
            Notification::createForUser($targetId, $type, $title, $message, $actionUrl, null, $meta);
            $this->deliverToMemberUser($targetId, $type, $title, $message, $actionUrl, $memberSourceKey);

            return;
        }
    }

    /**
     * @return array{console: int, member: int}
     */
    public function revoke(string $title, string $message, string $createdAt): array
    {
        $created = Carbon::parse($createdAt);
        $startOfMinute = (clone $created)->startOfMinute();
        $endOfMinute = (clone $created)->endOfMinute();

        $consoleDeleted = Notification::query()
            ->where('title', $title)
            ->where('message', $message)
            ->whereBetween('created_at', [$startOfMinute, $endOfMinute])
            ->delete();

        $memberDeletedRaw = MemberNotification::query()
            ->where('title', $title)
            ->where('message', $message)
            ->whereBetween('created_at', [$startOfMinute, $endOfMinute])
            ->where('source_key', 'like', 'broadcast-%')
            ->delete();
        $memberDeleted = is_int($memberDeletedRaw) ? $memberDeletedRaw : (is_numeric($memberDeletedRaw) ? (int) $memberDeletedRaw : 0);

        $consoleCount = is_int($consoleDeleted) ? $consoleDeleted : 0;

        return [
            'console' => $consoleCount,
            'member' => $memberDeleted,
        ];
    }

    private function deliverToAllMembers(
        string $type,
        string $title,
        string $message,
        ?string $actionUrl,
        string $sourceKey,
    ): void {
        if (! class_exists(Member::class) || ! class_exists(MemberNotification::class)) {
            return;
        }

        foreach (Member::withoutGlobalScopes()->get() as $member) {
            $this->upsertMemberBroadcast($member, $type, $title, $message, $actionUrl, $sourceKey);
        }
    }

    private function deliverToMemberUser(
        string $userId,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl,
        string $sourceKey,
    ): void {
        if (! class_exists(Member::class) || ! class_exists(MemberNotification::class)) {
            return;
        }

        $member = Member::withoutGlobalScopes()->where('user_id', $userId)->first();
        if ($member) {
            $this->upsertMemberBroadcast($member, $type, $title, $message, $actionUrl, $sourceKey);
        }
    }

    private function upsertMemberBroadcast(
        Member $member,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl,
        string $sourceKey,
    ): void {
        MemberNotification::query()->updateOrCreate(
            [
                'member_id' => $member->id,
                'source_key' => $sourceKey,
            ],
            [
                'subscription_id' => $member->subscription_id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'action_url' => $actionUrl,
                'read_at' => null,
            ],
        );
    }
}
