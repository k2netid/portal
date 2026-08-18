<?php

namespace Modules\Core\Security\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Modules\Core\System\Helpers\IpHelper;
use Modules\Core\System\Models\User;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $event_type
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string|null $description
 * @property array<string, mixed>|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read int|null $count
 * @property-read User|null $user
 */
class SecurityLog extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'sec_logs';

    protected $fillable = [
        'user_id',
        'event_type',
        'ip_address',
        'user_agent',
        'description',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper method to log security events
     *
     * @param  User|null  $user
     * @param  array<string, mixed>  $metadata
     */
    public static function log(string $eventType, $user = null, ?string $ipAddress = null, ?string $description = null, array $metadata = []): self
    {
        $log = self::create([
            'user_id' => $user?->id,
            'event_type' => $eventType,
            'ip_address' => $ipAddress ?? IpHelper::getClientIp(request()),
            'user_agent' => request()->userAgent(),
            'description' => $description ?? self::getDefaultDescription($eventType),
            'metadata' => $metadata,
        ]);

        // Forward to SIEM / Security Log Channel
        try {
            $context = [
                'event_type' => $eventType,
                'user_id' => $log->user_id,
                'ip_address' => $log->ip_address,
                'user_agent' => $log->user_agent,
                'description' => $log->description,
                'metadata' => $log->metadata,
            ];
            Log::channel('siem')->info("SECURITY_EVENT: {$eventType}", $context);
            Log::channel('siem_export')->info("SECURITY_EVENT: {$eventType}", $context);
        } catch (\Exception $e) {
            Log::error('Failed to export security log to SIEM: '.$e->getMessage());
        }

        return $log;
    }

    protected static function getDefaultDescription(string $eventType): string
    {
        return match ($eventType) {
            'login_failed' => 'Failed login attempt',
            'login_success' => 'Successful login',
            'login_blocked' => 'Login blocked due to too many failed attempts',
            'ip_blocked' => 'IP address blocked',
            'ip_unblocked' => 'IP address unblocked',
            'suspicious_activity' => 'Suspicious activity detected',
            'malicious_scanner_blocked' => 'Blocked malicious scanner',
            'malicious_extension_blocked' => 'Blocked malicious extension',
            'global_blacklist_blocked' => 'Blocked by global blacklist (Spamhaus)',
            'country_blocked' => 'Blocked by geolocation policy',
            'password_changed' => 'Password changed',
            'permission_denied' => 'Permission denied',

            default => ucfirst(str_replace('_', ' ', $eventType)),
        };
    }
}
