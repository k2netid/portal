<?php

namespace Modules\Core\System\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Modules\Core\System\Traits\CoreLogsActivity;

/**
 * @property string $id
 * @property string|null $account_id
 * @property string|null $message_id
 * @property string $folder
 * @property string $sender_name
 * @property string $sender_email
 * @property array<string> $recipients
 * @property array<string>|null $cc
 * @property array<string>|null $bcc
 * @property string $subject
 * @property string|null $snippet
 * @property string|null $body
 * @property bool $is_read
 * @property bool $is_starred
 * @property array<string>|null $labels
 * @property array<int, array<string, mixed>>|null $attachments
 * @property Carbon|null $sent_at
 * @property Carbon|null $received_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class MailMessage extends Model
{
    use HasUuids;
    use CoreLogsActivity;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'sys_mail_messages';

    protected $fillable = [
        'account_id',
        'message_id',
        'folder',
        'sender_name',
        'sender_email',
        'recipients',
        'cc',
        'bcc',
        'subject',
        'snippet',
        'body',
        'is_read',
        'is_starred',
        'labels',
        'attachments',
        'sent_at',
        'received_at',
    ];

    protected $casts = [
        'recipients' => 'array',
        'cc' => 'array',
        'bcc' => 'array',
        'is_read' => 'boolean',
        'is_starred' => 'boolean',
        'labels' => 'array',
        'attachments' => 'array',
        'sent_at' => 'datetime',
        'received_at' => 'datetime',
    ];
}
