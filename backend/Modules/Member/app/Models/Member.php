<?php

declare(strict_types=1);

namespace Modules\Member\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Modules\Member\Support\MemberPublicProfile;

/**
 * @property string $id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property string|null $avatar
 * @property string|null $bio
 * @property string|null $locale
 * @property string|null $timezone
 * @property string|null $pending_email
 * @property string $password
 * @property string $status
 * @property Carbon|null $email_verified_at
 * @property Carbon|null $last_login_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $deleted_at
 * @property-read MemberTwoFactor|null $twoFactor
 */
class Member extends Authenticatable
{
    use HasApiTokens;
    use HasUuids;
    use Notifiable;
    use SoftDeletes;

    protected $table = 'mem_members';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'avatar',
        'bio',
        'locale',
        'timezone',
        'pending_email',
        'password',
        'status',
        'email_verified_at',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * @return HasOne<MemberTwoFactor, $this>
     */
    public function twoFactor(): HasOne
    {
        return $this->hasOne(MemberTwoFactor::class, 'member_id');
    }

    public function hasTwoFactorEnabled(): bool
    {
        return $this->twoFactor !== null && $this->twoFactor->enabled === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function toPublicProfile(): array
    {
        $profile = MemberPublicProfile::serialize($this);
        $profile['two_factor_enabled'] = $this->hasTwoFactorEnabled();

        return $profile;
    }
}
