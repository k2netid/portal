<?php

namespace Modules\Core\System\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;

/**
 * @property string $id
 * @property string $user_id
 * @property string|null $secret
 * @property array<int, string>|null $backup_codes
 * @property bool $enabled
 * @property Carbon|null $enabled_at
 * @property Carbon|null $recovery_codes_generated_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 */
class TwoFactorAuth extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'sys_two_factor_auth';

    protected $fillable = [
        'user_id',
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
     * Get the user that owns the 2FA.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the decrypted secret.
     */
    public function getDecryptedSecret(): ?string
    {
        if (! $this->secret) {
            return null;
        }

        try {
            return Crypt::decryptString($this->secret);
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Set the encrypted secret.
     */
    public function setSecret(string $secret): void
    {
        $this->secret = Crypt::encryptString($secret);
    }

    /**
     * Check if a backup code is valid and remove it if found.
     */
    public function verifyBackupCode(string $code): bool
    {
        if (empty($this->backup_codes)) {
            return false;
        }

        foreach ($this->backup_codes as $index => $hashedCode) {
            if (password_verify($code, $hashedCode)) {
                // Remove used backup code
                $codes = $this->backup_codes;
                unset($codes[$index]);
                $this->backup_codes = array_values($codes); // Re-index
                $this->save();

                return true;
            }
        }

        return false;
    }

    /**
     * Add backup codes (hashed).
     *
     * @param  array<int, string>  $codes
     */
    public function setBackupCodes(array $codes): void
    {
        $hashedCodes = array_map(fn (string $code) => password_hash($code, PASSWORD_BCRYPT), $codes);

        $this->backup_codes = $hashedCodes;
        $this->recovery_codes_generated_at = now();
    }

    /**
     * Get remaining backup codes count.
     */
    public function getRemainingBackupCodesCount(): int
    {
        if (empty($this->backup_codes)) {
            return 0;
        }

        return count($this->backup_codes);
    }
}
