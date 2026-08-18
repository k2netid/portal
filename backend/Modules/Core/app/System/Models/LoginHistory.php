<?php

namespace Modules\Core\System\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property int|null $user_id
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property Carbon|null $login_at
 * @property Carbon|null $logout_at
 * @property int|null $session_duration
 * @property string $status
 * @property string|null $failure_reason
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read int|null $ip_count
 * @property-read User|null $user
 */
class LoginHistory extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'sys_login_histories';

    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'login_at',
        'logout_at',
        'session_duration',
        'status', // success, failed, blocked
        'failure_reason',
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'logout_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * @param  Builder<$this>  $query
     * @param  int|null  $days
     * @return Builder<$this>
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('login_at', '>=', now()->subDays($days ?? 30));
    }
}
