<?php

declare(strict_types=1);

namespace Modules\Mail\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;
use Modules\Core\System\Models\User;

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

    /** @var list<string> */
    private const SECRET_FIELDS = ['smtp_password', 'imap_password'];

    protected static function booted(): void
    {
        static::created(function (?self $model): void {
            if ($model instanceof self) {
                $model->logActivity('created', $model->redactedAttributes());
            }
        });

        static::updated(function (?self $model): void {
            if (! $model instanceof self) {
                return;
            }

            $changes = $model->getChanges();
            unset($changes['updated_at']);

            if ($changes === []) {
                return;
            }

            $original = [];
            foreach (array_keys($changes) as $key) {
                $original[$key] = self::redactValue($key, $model->getOriginal($key));
            }

            $model->logActivity('updated', [
                'old' => $original,
                'new' => self::redactChanges($changes),
            ]);
        });

        static::deleted(function (?self $model): void {
            if ($model instanceof self) {
                $model->logActivity('deleted', $model->redactedAttributes());
            }
        });
    }

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

    /**
     * @return BelongsTo<User, $this>
     */
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
        $payload = $this->attributes['smtp_password'] ?? null;
        if (! is_string($payload) || $payload === '') {
            return null;
        }

        try {
            return Crypt::decryptString($payload);
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
        $payload = $this->attributes['imap_password'] ?? null;
        if (! is_string($payload) || $payload === '') {
            return null;
        }

        try {
            return Crypt::decryptString($payload);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function logActivity(string $event, array $payload): void
    {
        \Modules\Core\System\Models\ActivityLog::log($event, $this, $payload);
    }

    /**
     * @return array<string, mixed>
     */
    private function redactedAttributes(): array
    {
        return self::redactChanges($this->getAttributes());
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private static function redactChanges(array $values): array
    {
        foreach (self::SECRET_FIELDS as $field) {
            if (array_key_exists($field, $values) && $values[$field] !== null && $values[$field] !== '') {
                $values[$field] = '[REDACTED]';
            }
        }

        return $values;
    }

    private static function redactValue(string $key, mixed $value): mixed
    {
        if (in_array($key, self::SECRET_FIELDS, true) && $value !== null && $value !== '') {
            return '[REDACTED]';
        }

        return $value;
    }
}
