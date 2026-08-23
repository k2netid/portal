<?php

namespace Modules\Analytics\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Modules\Core\System\Helpers\IpHelper;
use Modules\Core\System\Models\User;
use Modules\Analytics\Database\Factories\AnalyticsVisitFactory;

/**
 * @property int $id
 * @property string $session_id
 * @property int|null $user_id
 * @property string $ip_address
 * @property string|null $user_agent
 * @property string|null $referer
 * @property string|null $referer_host
 * @property string $url
 * @property string $method
 * @property int $status_code
 * @property int|null $duration
 * @property Carbon $visited_at
 * @property int|null $visits_count
 * @property int|null $count
 */
class AnalyticsVisit extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'srv_analytics_visits';

    /** @use HasFactory<AnalyticsVisitFactory> */
    use HasFactory;

    protected static function newFactory(): AnalyticsVisitFactory
    {
        return AnalyticsVisitFactory::new();
    }

    public $timestamps = true; // DB has created_at/updated_at

    protected $fillable = [
        'session_id',
        'user_id',
        'ip_address',
        'user_agent',
        'referer',
        'url',
        'method',
        'status_code',
        'duration',
        'visited_at',
    ];

    protected $casts = [
        'visited_at' => 'datetime',
        'status_code' => 'integer',
        'duration' => 'integer',
    ];

    /**
     * Track a visit.
     */
    public static function trackVisit(Request $request): self
    {
        $sessInput = $request->input('session_id') ?? session()->getId();
        $sessionId = is_scalar($sessInput) ? (string) $sessInput : '';

        // Ensure session exists and sync basic info
        $analyticsSession = AnalyticsSession::start($request, $sessionId);

        $userAgent = $request->userAgent();
        $referer = $request->headers->get('referer');

        $visit = self::create([
            'session_id' => $sessionId,
            'user_id' => Auth::id(),
            'ip_address' => IpHelper::getClientIp($request),
            'user_agent' => is_string($userAgent) ? $userAgent : null,
            'referer' => is_string($referer) ? $referer : null,
            'url' => is_string($urlInput = $request->input('url')) ? $urlInput : $request->fullUrl(),
            'method' => $request->method(),
            'status_code' => 200,
            'duration' => is_numeric($timingInput = $request->input('timing')) ? intval($timingInput) : null,
            'visited_at' => now(),
        ]);

        // Increment page views on session
        $analyticsSession->incrementPageViews();

        return $visit;
    }

    /**
     * Get the session associated with the visit.
     *
     * @return BelongsTo<AnalyticsSession, $this>
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(AnalyticsSession::class, 'session_id', 'session_id');
    }

    /**
     * Get the user that made the visit.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
