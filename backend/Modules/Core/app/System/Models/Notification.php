<?php

namespace Modules\Core\System\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $user_id
 * @property string $type
 * @property array<string, mixed> $data
 * @property Carbon|null $read_at
 * @property Carbon|null $created_at
 * @property string $title
 * @property string $message
 * @property string|null $action_url
 * @property string|null $action_text
 * @property bool $is_read
 * @property Carbon|null $updated_at
 */
class Notification extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'sys_notifications';

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'action_url',
        'action_text',
        'is_read',
        'read_at',
        'data',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'data' => 'array',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * @param  int|string  $userId
     * @param  array<string, mixed>  $data
     */
    public static function createForUser($userId, string $type, string $title, string $message, ?string $actionUrl = null, ?string $actionText = null, array $data = []): self
    {
        return static::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'action_url' => $actionUrl,
            'action_text' => $actionText,
            'data' => $data,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<int, self>
     */
    public static function createForAll(string $type, string $title, string $message, ?string $actionUrl = null, ?string $actionText = null, array $data = []): array
    {
        $users = User::all();
        $notifications = [];

        foreach ($users as $user) {
            $notifications[] = static::createForUser($user->id, $type, $title, $message, $actionUrl, $actionText, $data);
        }

        return $notifications;
    }
}
