<?php

namespace Modules\Analytics\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Modules\Analytics\Database\Factories\AnalyticsSessionFactory;
use Modules\Core\System\Helpers\IpHelper;
use Modules\Core\System\Models\User;
use Modules\Core\System\Services\GeoIpService;

/**
 * @property int $id
 * @property string $session_id
 * @property int|null $user_id
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string|null $device_type
 * @property string|null $browser
 * @property string|null $os
 * @property string|null $country
 * @property string|null $city
 * @property int $page_views
 * @property int $duration
 * @property Carbon|null $started_at
 * @property Carbon|null $ended_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 * @property-read Collection<int, AnalyticsVisit> $visits
 * @property-read Collection<int, AnalyticsEvent> $events
 */
class AnalyticsSession extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'srv_analytics_sessions';

    /** @use HasFactory<AnalyticsSessionFactory> */
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): AnalyticsSessionFactory
    {
        return AnalyticsSessionFactory::new();
    }

    protected $fillable = [
        'session_id',
        'user_id',
        'ip_address',
        'user_agent',
        'device_type',
        'browser',
        'os',
        'country',
        'city',
        'page_views',
        'duration',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'page_views' => 'integer',
        'duration' => 'integer',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<AnalyticsVisit, $this>
     */
    public function visits(): HasMany
    {
        return $this->hasMany(AnalyticsVisit::class, 'session_id', 'session_id');
    }

    /**
     * @return HasMany<AnalyticsEvent, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(AnalyticsEvent::class, 'session_id', 'session_id');
    }

    public static function start(Request $request, ?string $sessionId = null): self
    {
        $sessionId ??= session()->getId();

        $userAgent = $request->userAgent();
        $deviceInfo = self::parseUserAgent($userAgent);
        $clientIp = IpHelper::getClientIp($request);
        $location = self::getLocation($clientIp);

        return self::firstOrCreate(
            ['session_id' => $sessionId],
            [
                'user_id' => Auth::id(),
                'ip_address' => $clientIp,
                'user_agent' => $userAgent,
                'device_type' => $deviceInfo['device_type'],
                'browser' => $deviceInfo['browser'],
                'os' => $deviceInfo['os'],
                'country' => $location['country'],
                'city' => $location['city'],
                'started_at' => now(),
            ]
        );
    }

    public function end(): void
    {
        $this->update([
            'ended_at' => now(),
            'duration' => $this->started_at?->diffInSeconds(now()) ?? 0,
            'page_views' => $this->visits()->count(),
        ]);
    }

    public function incrementPageViews(): void
    {
        $this->increment('page_views');
    }

    /**
     * @return array{device_type: string, browser: string, os: string}
     */
    protected static function parseUserAgent(?string $userAgent): array
    {
        $deviceType = 'desktop';
        $browser = 'unknown';
        $os = 'unknown';

        if (! $userAgent) {
            return [
                'device_type' => $deviceType,
                'browser' => $browser,
                'os' => $os,
            ];
        }

        if (preg_match('/mobile|android|iphone|ipad/i', $userAgent)) {
            $deviceType = 'mobile';
        } elseif (preg_match('/tablet|ipad/i', $userAgent)) {
            $deviceType = 'tablet';
        }

        if (preg_match('/chrome/i', $userAgent)) {
            $browser = 'chrome';
        } elseif (preg_match('/firefox/i', $userAgent)) {
            $browser = 'firefox';
        } elseif (preg_match('/safari/i', $userAgent)) {
            $browser = 'safari';
        } elseif (preg_match('/edge/i', $userAgent)) {
            $browser = 'edge';
        }

        if (preg_match('/windows/i', $userAgent)) {
            $os = 'windows';
        } elseif (preg_match('/mac|os x/i', $userAgent)) {
            $os = 'macos';
        } elseif (preg_match('/linux/i', $userAgent)) {
            $os = 'linux';
        } elseif (preg_match('/android/i', $userAgent)) {
            $os = 'android';
        } elseif (preg_match('/ios|iphone|ipad/i', $userAgent)) {
            $os = 'ios';
        }

        return [
            'device_type' => $deviceType,
            'browser' => $browser,
            'os' => $os,
        ];
    }

    /**
     * @return array{country: string|null, city: string|null}
     */
    public static function getLocation(?string $ipAddress): array
    {
        if (! $ipAddress) {
            return ['country' => null, 'city' => null];
        }

        $geoService = app(GeoIpService::class);

        return $geoService->getLocation($ipAddress);
    }
}
