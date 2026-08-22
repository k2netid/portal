<?php

declare(strict_types=1);

namespace Modules\Core\System\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;
use Modules\Core\System\Traits\CoreLogsActivity;

/**
 * @property string $id
 * @property string $user_id
 * @property string $name
 * @property string $email
 * @property string $account_type
 * @property string|null $smtp_host
 * @property int|null $smtp_port
 * @property string|null $smtp_username
 * @property string|null $smtp_password
 * @property string|null $smtp_encryption
 * @property string|null $imap_host
 * @property int|null $imap_port
 * @property string|null $imap_username
 * @property string|null $imap_password
 * @property string|null $imap_encryption
 * @property bool $is_default
 * @property bool $is_active
 * @property string|null $signature
 */
class MailAccount extends Model
{
    use HasUuids;
    use CoreLogsActivity;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'sys_mail_accounts';

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'account_type',
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'smtp_encryption',
        'imap_host',
        'imap_port',
        'imap_username',
        'imap_password',
        'imap_encryption',
        'is_default',
        'is_active',
        'signature',
    ];

    protected $hidden = [
        'smtp_password',
        'imap_password',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'smtp_port' => 'integer',
        'imap_port' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function setSmtpPasswordAttribute(?string $value): void
    {
        $this->attributes['smtp_password'] = $value !== null && $value !== ''
            ? Crypt::encryptString($value)
            : null;
    }

    public function getDecryptedSmtpPassword(): ?string
    {
        if (empty($this->attributes['smtp_password'])) {
            return null;
        }

        try {
            return Crypt::decryptString($this->attributes['smtp_password']);
        } catch (\Throwable) {
            return null;
        }
    }

    public function setImapPasswordAttribute(?string $value): void
    {
        $this->attributes['imap_password'] = $value !== null && $value !== ''
            ? Crypt::encryptString($value)
            : null;
    }

    public function getDecryptedImapPassword(): ?string
    {
        if (empty($this->attributes['imap_password'])) {
            return null;
        }

        try {
            return Crypt::decryptString($this->attributes['imap_password']);
        } catch (\Throwable) {
            return null;
        }
    }
}
