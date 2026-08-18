<?php

namespace Modules\Core\Security\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Modules\Core\Security\Database\Factories\IpListFactory;
use Modules\Core\System\Models\User;

/**
 * @property string $id
 * @property string $ip_address
 * @property string $type
 * @property string|null $reason
 * @property string|null $created_by
 */
class IpList extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'sec_ip_lists';

    /** @use HasFactory<IpListFactory> */
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): IpListFactory
    {
        return IpListFactory::new();
    }

    protected $fillable = [
        'ip_address',
        'type',
        'reason',
        'created_by',
    ];

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeBlocklist($query)
    {
        return $query->where('type', 'blocklist');
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeWhitelist($query)
    {
        return $query->where('type', 'whitelist');
    }

    /**
     * Get the user who created this entry
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if IP is in blocklist
     */
    public static function isBlocked(string $ip): bool
    {
        return (bool) Cache::remember(self::cacheKey('blocklist', $ip), 60, fn () => self::query()
            ->where('ip_address', $ip)
            ->where('type', 'blocklist')
            ->exists());
    }

    /**
     * Check if IP is in whitelist
     */
    public static function isWhitelisted(string $ip): bool
    {
        return (bool) Cache::remember(self::cacheKey('whitelist', $ip), 60, fn () => self::query()
            ->where('ip_address', $ip)
            ->where('type', 'whitelist')
            ->exists());
    }

    public static function forgetIpCache(?string $ip = null): void
    {
        if ($ip === null || $ip === '') {
            return;
        }
        Cache::forget(self::cacheKey('blocklist', $ip));
        Cache::forget(self::cacheKey('whitelist', $ip));
    }

    private static function cacheKey(string $type, string $ip): string
    {
        return 'sec:ip:'.$type.':'.md5($ip);
    }
}
