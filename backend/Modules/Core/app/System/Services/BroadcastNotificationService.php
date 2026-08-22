<?php

declare(strict_types=1);

namespace Modules\Core\System\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Modules\Core\System\Models\Notification;

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

        if ($targetType === 'all') {
            Notification::createForAll($type, $title, $message, $actionUrl, null, $meta);

            return;
        }

        if ($targetType === 'user' && ! empty($payload['target_id'])) {
            $targetId = is_scalar($payload['target_id'] ?? null) ? (string) $payload['target_id'] : '';
            Notification::createForUser($targetId, $type, $title, $message, $actionUrl, null, $meta);

            return;
        }

        if ($targetType === 'roles' && ! empty($payload['target_roles']) && is_array($payload['target_roles'])) {
            $roles = array_values(array_filter($payload['target_roles'], 'is_string'));
            Notification::createForRoles($roles, $type, $title, $message, $actionUrl, null, $meta);
        }
    }

    /**
     * @return array{console: int, member: int}
     */
    public function recall(string $title, string $message, ?Carbon $sentAt = null, ?string $broadcastId = null, ?string $id = null): array
    {
        $query = Notification::query();

        if (! empty($broadcastId)) {
            $query->whereRaw("jsonb_extract_path_text(data, 'broadcast_id') = ?", [$broadcastId]);
        } elseif (! empty($id)) {
            $query->where('id', $id);
        } else {
            $query->where('title', $title)
                ->where('message', $message);

            if ($sentAt) {
                $query->whereBetween('created_at', [
                    $sentAt->copy()->subHours(24),
                    $sentAt->copy()->addHours(24),
                ]);
            }
        }

        $consoleDeleted = $query->delete();
        $consoleCount = is_int($consoleDeleted) ? $consoleDeleted : 0;

        return [
            'console' => $consoleCount,
            'member' => 0,
        ];
    }

    /**
     * @return array{console: int, member: int}
     */
    public function revoke(string $title, string $message, string|Carbon|null $sentAt = null, ?string $broadcastId = null, ?string $id = null): array
    {
        $carbon = is_string($sentAt) ? Carbon::parse($sentAt) : $sentAt;

        return $this->recall($title, $message, $carbon, $broadcastId, $id);
    }
}
