<?php

declare(strict_types=1);

namespace Modules\Member\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;

/**
 * @property string $id
 * @property string $member_id
 * @property string|null $secret
 * @property array<int, string>|null $backup_codes
 * @property bool $enabled
 * @property Carbon|null $enabled_at
 * @property Carbon|null $recovery_codes_generated_at
 * @property-read Member $member
 */
class MemberTwoFactor extends Model
{
    use HasUuids;

    protected $table = 'mem_member_two_factor';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'member_id',
        'secret',
        'backup_codes',
        'enabled',
        'enabled_at',
        'recovery_codes_generated_at',
    ];

    protected $casts = [
        'backup_codes' => 'array',
        'enabled' => 'boolean',
        'enabled_at' => 'datetime',
        'recovery_codes_generated_at' => 'datetime',
    ];

    protected $hidden = [
        'secret',
        'backup_codes',
    ];

    /**
     * @return BelongsTo<Member, $this>
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    public function getDecryptedSecret(): ?string
    {
        if (! $this->secret) {
            return null;
        }

        try {
            return Crypt::decryptString($this->secret);
        } catch (\Throwable) {
            return null;
        }
    }

    public function setSecret(string $secret): void
    {
        $this->secret = Crypt::encryptString($secret);
    }

    public function verifyBackupCode(string $code): bool
    {
        if (empty($this->backup_codes)) {
            return false;
        }

        foreach ($this->backup_codes as $index => $hashedCode) {
            if (password_verify($code, $hashedCode)) {
                $codes = $this->backup_codes;
                unset($codes[$index]);
                $this->backup_codes = array_values($codes);
                $this->save();

                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int, string>  $codes
     */
    public function setBackupCodes(array $codes): void
    {
        $this->backup_codes = array_map(
            static fn (string $code): string => password_hash($code, PASSWORD_BCRYPT),
            $codes,
        );
        $this->recovery_codes_generated_at = now();
    }

    public function getRemainingBackupCodesCount(): int
    {
        return empty($this->backup_codes) ? 0 : count($this->backup_codes);
    }
}
