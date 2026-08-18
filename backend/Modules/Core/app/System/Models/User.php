<?php

namespace Modules\Core\System\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Laravel\Passkeys\Contracts\PasskeyUser;
use Laravel\Passkeys\PasskeyAuthenticatable;
use Laravel\Passport\Client;
use Laravel\Passport\Passport;
use Laravel\Sanctum\HasApiTokens;
use Modules\Content\Media\Models\File;
use Modules\Core\System\Database\Factories\UserFactory;
use Modules\Core\System\Notifications\VerifyEmail;
use Modules\Core\System\Traits\CoreLogsActivity;
use Modules\Core\System\Traits\DispatchesWebhooks;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property string $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $username
 * @property int $username_changes_count
 * @property string|null $avatar
 * @property string|null $phone
 * @property string|null $bio
 * @property string|null $website
 * @property string|null $location
 * @property Carbon|null $last_login_at
 * @property string|null $last_login_ip
 * @property Collection|Role[] $roles
 * @property Collection|Permission[] $permissions
 * @property array<string, mixed>|null $preferences
 * @property Carbon|null $email_verified_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property string $kyc_level
 * @property int $onboarding_step
 * @property-read Collection<int, File> $media
 * @property-read Collection<int, ActivityLog> $activityLogs
 * @property-read Collection<int, Notification> $notifications
 * @property-read TwoFactorAuth|null $twoFactorAuth
 */
class User extends Authenticatable implements MustVerifyEmail, PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use CoreLogsActivity, DispatchesWebhooks, HasApiTokens, HasFactory, HasRoles, HasUuids, Notifiable, PasskeyAuthenticatable, SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * OAuth2 clients owned by this user (Laravel Passport 13).
     *
     * @return MorphMany<Client, $this>
     */
    public function oauthApps(): MorphMany
    {
        return $this->morphMany(Passport::clientModel(), 'owner');
    }

    protected $table = 'srv_auth_users';

    /**
     * Role ranks contributed by other modules.
     *
     * @var array<string, int>
     */
    protected static array $moduleRoleRanks = [];

    /**
     * Register role ranks for modules.
     *
     * @param  array<string, int>  $ranks
     */
    public static function registerRoleRanks(array $ranks): void
    {
        static::$moduleRoleRanks = array_merge(static::$moduleRoleRanks, $ranks);
    }

    const MAX_USERNAME_CHANGES = 3;

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    protected $fillable = [
        'name',
        'username',
        'username_changes_count',
        'email',
        'password',
        'avatar',
        'phone',
        'bio',
        'website',
        'location',
        'last_login_at',
        'last_login_ip',
        'preferences',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'preferences' => 'array',
        ];
    }

    public function getPreference(string $key, mixed $default = null): mixed
    {
        return data_get($this->preferences, $key, $default);
    }

    public function setPreference(string $key, mixed $value): self
    {
        /** @var array<string, mixed> $preferences */
        $preferences = is_array($this->preferences) ? $this->preferences : [];
        Arr::set($preferences, $key, $value);
        $this->preferences = $preferences;

        return $this;
    }

    /**
     * @return HasMany<File, $this>
     */
    public function media(): HasMany
    {
        return $this->hasMany(File::class, 'author_id');
    }

    /**
     * @return HasMany<ActivityLog, $this>
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * @return HasMany<Notification, $this>
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * @return HasOne<TwoFactorAuth, $this>
     */
    public function twoFactorAuth(): HasOne
    {
        return $this->hasOne(TwoFactorAuth::class);
    }

    public function hasTwoFactorEnabled(): bool
    {
        if (! Setting::get('enable_2fa', false)) {
            return false;
        }

        return $this->twoFactorAuth && $this->twoFactorAuth->enabled;
    }

    public function requiresTwoFactor(): bool
    {
        if (! Setting::get('enable_2fa', false)) {
            return false;
        }
        $enforcement = Setting::get('two_factor_enforced_roles', 'no');
        if ($enforcement === 'all') {
            return true;
        }
        if ($enforcement === 'admin') {
            return $this->isAtLeastRole('admin');
        }

        return false;
    }

    public function getRoleRank(): int
    {
        $roleRanks = static::getRoleRankMap();
        $userRoles = $this->getRoleNames();
        $maxRank = 0;
        foreach ($userRoles as $role) {
            if (! is_string($role)) {
                continue;
            }
            if (isset($roleRanks[$role]) && $roleRanks[$role] > $maxRank) {
                $maxRank = $roleRanks[$role];
            }
        }

        return $maxRank;
    }

    public function isHigherThan(User $target): bool
    {
        return $this->getRoleRank() > $target->getRoleRank();
    }

    public function isAtLeastRole(string $roleName): bool
    {
        $roleRanks = self::getRoleRankMap();
        if (! isset($roleRanks[$roleName])) {
            return false;
        }

        return $this->getRoleRank() >= $roleRanks[$roleName];
    }

    /**
     * @return array<string, int>
     */
    public static function getRoleRankMap(): array
    {
        $coreRanks = [
            'super' => 100,
            'system-admin' => 98,
            'admin' => 95,
            'operator' => 85,
            'member' => 20,
        ];

        return array_merge($coreRanks, static::$moduleRoleRanks);
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmail);
    }
}
