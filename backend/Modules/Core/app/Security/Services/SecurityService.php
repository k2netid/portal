<?php

namespace Modules\Core\Security\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Modules\Core\Security\Models\IpList;
use Modules\Core\Security\Models\SecurityLog;
use Modules\Core\System\Helpers\IpHelper;
use Modules\Core\System\Models\Setting;
use Modules\Core\System\Models\User;
use Modules\Core\System\Services\AnomalyDetectionService;

class SecurityService
{
    // Cache prefix for all security-related keys
    protected string $cachePrefix = 'security:';

    // Account lockout settings
    protected int $accountLockMinutes = 15;

    // Progressive blocking settings
    protected int $maxFailedAttempts;

    protected int $baseBlockMinutes;

    protected int $maxBlockMinutes = 60; // Maximum block duration in minutes

    protected int $offenseResetHours = 24; // Reset offense count after this many hours

    public function __construct()
    {
        $this->maxFailedAttempts = is_scalar($v1 = Setting::get('login_attempts_limit', 5)) ? (int) $v1 : 5;
        $this->baseBlockMinutes = is_scalar($v2 = Setting::get('block_duration_minutes', 15)) ? (int) $v2 : 15;
        // Ensure baseBlockMinutes is at least 1 to avoid math issues or immediate expiry
        $this->baseBlockMinutes = max(1, $this->baseBlockMinutes);
        $this->accountLockMinutes = $this->baseBlockMinutes;
    }

    /**
     * Record a failed login attempt.
     * Uses progressive blocking - block duration increases with each offense.
     *
     * @return array{ip_attempts: int, email_attempts: int, ip_blocked: bool, account_locked: bool, block_duration: int}
     */
    public function recordFailedLogin(string $email, string $ipAddress): array
    {
        SecurityLog::log('login_failed', null, $ipAddress, "Failed login attempt for email: {$email}", [
            'email' => $email,
        ]);

        // Track failed attempts by IP
        $ipAttempts = $this->incrementFailedAttempts($ipAddress);

        // Track failed attempts by email
        $emailAttempts = $this->incrementFailedAttempts($email, 'email');

        $result = [
            'ip_attempts' => $ipAttempts,
            'email_attempts' => $emailAttempts,
            'ip_blocked' => false,
            'account_locked' => false,
            'block_duration' => 0,
        ];

        // Block IP if too many attempts (progressive blocking)
        if ($ipAttempts >= $this->maxFailedAttempts && ! $this->isProtectedIp($ipAddress)) {
            $blockSeconds = $this->blockIpTemporarily($ipAddress, "Too many failed login attempts ({$ipAttempts})");
            $result['ip_blocked'] = true;
            $result['block_duration'] = $blockSeconds;
        }

        // Lock account if too many attempts
        if ($emailAttempts >= $this->maxFailedAttempts) {
            $this->lockAccount($email, "Too many failed login attempts ({$emailAttempts})");
            $result['account_locked'] = true;
            SecurityLog::log('account_locked', null, $ipAddress, "Account locked due to {$emailAttempts} failed login attempts", [
                'email' => $email,
            ]);
        }

        return $result;
    }

    /**
     * Record a successful login and clear all security counters.
     */
    public function recordSuccessfulLogin(User $user, string $ipAddress): void
    {
        SecurityLog::log('login_success', $user, $ipAddress, "Successful login for user: {$user->email}");

        // Clear all security restrictions for this user
        $this->clearSecurityCache($ipAddress);
        $this->clearSecurityCache($user->email, 'email');
        $this->unlockAccount($user->email);
    }

    /**
     * Increment failed attempts counter with cache TTL for auto-expiry.
     *
     * [SECURITY FIX H-05] Use true sliding window rate limiting.
     * Only set TTL on first attempt — subsequent increments use Cache::increment()
     * which does NOT reset the TTL. This prevents the bypass where an attacker
     * makes N-1 attempts, waits for the TTL to almost expire, then repeats.
     */
    protected function incrementFailedAttempts(string $identifier, string $type = 'ip'): int
    {
        $key = $this->cachePrefix."failed_attempts:{$type}:{$identifier}";

        if (! Cache::has($key)) {
            // First attempt: initialize the counter with the sliding window TTL.
            Cache::put($key, 1, now()->addMinutes($this->accountLockMinutes));

            return 1;
        }

        // Subsequent attempts: increment WITHOUT resetting TTL (true sliding window).
        $newValue = Cache::increment($key);

        return is_int($newValue) ? $newValue : 1;
    }

    /**
     * Get current failed attempts count.
     */
    public function getFailedAttempts(string $identifier, string $type = 'ip'): int
    {
        $key = $this->cachePrefix."failed_attempts:{$type}:{$identifier}";
        $attempts = Cache::get($key, 0);

        return is_numeric($attempts) ? (int) $attempts : 0;
    }

    /**
     * Block IP temporarily with progressive duration.
     * Duration doubles with each offense (1min → 2min → 4min → 8min... max 60min).
     *
     * @return int Block duration in seconds
     */
    public function blockIpTemporarily(string $ipAddress, ?string $reason = null): int
    {
        if ($this->isProtectedIp($ipAddress)) {
            return 0;
        }

        if (IpList::isWhitelisted($ipAddress)) {
            return 0;
        }

        // Get and increment offense count
        $offenseKey = $this->cachePrefix."offense_count:{$ipAddress}";
        $currentOffense = Cache::get($offenseKey, 0);
        $offenseCount = (is_numeric($currentOffense) ? (int) $currentOffense : 0) + 1;
        Cache::put($offenseKey, $offenseCount, now()->addHours($this->offenseResetHours));

        // Calculate progressive block duration (exponential backoff)
        $blockMinutes = min(
            $this->baseBlockMinutes * 2 ** ($offenseCount - 1),
            $this->maxBlockMinutes
        );

        // Store block_until timestamp (auto-expires via cache TTL)
        $blockUntil = now()->addMinutes($blockMinutes);
        Cache::put(
            $this->cachePrefix."block_until:{$ipAddress}",
            $blockUntil->toIso8601String(),
            $blockMinutes * 60 // TTL in seconds
        );

        SecurityLog::log('ip_blocked_temp', null, $ipAddress,
            "IP temporarily blocked for {$blockMinutes} minute(s): ".($reason ?? 'Too many failed attempts'),
            [
                'duration_minutes' => $blockMinutes,
                'offense_count' => $offenseCount,
                'block_until' => $blockUntil->toIso8601String(),
            ]
        );

        return $blockMinutes * 60; // Return seconds
    }

    /**
     * Check if IP is currently blocked.
     * Temporary blocks auto-expire via cache TTL (no manual cache:clear needed).
     */
    public function isIpBlocked(string $ipAddress): bool
    {
        // Never block protected IPs
        if ($this->isProtectedIp($ipAddress)) {
            return false;
        }

        // Whitelisted IPs are never blocked
        if (IpList::isWhitelisted($ipAddress)) {
            return false;
        }

        // Check permanent blocklist (database)
        if (IpList::isBlocked($ipAddress)) {
            return true;
        }

        // Check temporary block (auto-expires via cache TTL)
        return Cache::has($this->cachePrefix."block_until:{$ipAddress}");
    }

    /**
     * Get remaining block time in seconds.
     */
    public function getRemainingBlockTime(string $ipAddress): int
    {
        $key = $this->cachePrefix."block_until:{$ipAddress}";
        $blockUntil = Cache::get($key);

        if (! is_string($blockUntil)) {
            return 0;
        }

        $remaining = Carbon::parse($blockUntil)->diffInSeconds(now(), false);

        return (int) max(0, -$remaining);
    }

    /**
     * Get block info for API response.
     *
     * @return array{is_blocked: bool, remaining_seconds: int, offense_count: int, failed_attempts: int}
     */
    public function getBlockInfo(string $ipAddress): array
    {
        return [
            'is_blocked' => $this->isIpBlocked($ipAddress),
            'remaining_seconds' => $this->getRemainingBlockTime($ipAddress),
            'offense_count' => is_numeric($offenseCount = Cache::get($this->cachePrefix."offense_count:{$ipAddress}", 0)) ? (int) $offenseCount : 0,
            'failed_attempts' => $this->getFailedAttempts($ipAddress),
        ];
    }

    /**
     * Permanently block an IP (adds to database).
     */
    public function blockIpPermanently(string $ipAddress, ?string $reason = null): bool
    {
        if ($this->isProtectedIp($ipAddress)) {
            return false;
        }

        if (IpList::isWhitelisted($ipAddress)) {
            return false;
        }

        $defaultReasonRaw = json_encode(['key' => 'features.security.reasons.manual']);
        $defaultReason = is_string($defaultReasonRaw) ? $defaultReasonRaw : 'Manual block';

        IpList::updateOrCreate(
            ['ip_address' => $ipAddress, 'type' => 'blocklist'],
            [
                'reason' => $reason ?? $defaultReason,
                'created_by' => Auth::id(),
            ]
        );

        SecurityLog::log('ip_blocked_permanent', null, $ipAddress, $reason ?? $defaultReason);

        // Send real-time alert
        $notifier = app(SecurityNotificationService::class);
        $notifier->critical(
            'auto_block',
            'IP Permanently Blocked',
            $reason ?? $defaultReason,
            ['ip' => $ipAddress]
        );

        return true;
    }

    /**
     * Unblock an IP (removes from both cache and database).
     */
    public function unblockIp(string $ipAddress): void
    {
        // Remove from database blocklist
        IpList::where('ip_address', $ipAddress)->where('type', 'blocklist')->delete();
        IpList::forgetIpCache($ipAddress);

        // Clear all cache entries for this IP
        $this->clearSecurityCache($ipAddress);

        SecurityLog::log('ip_unblocked', null, $ipAddress, 'IP address unblocked');
    }

    /**
     * Clear all security-related cache for an identifier.
     */
    public function clearSecurityCache(string $identifier, string $type = 'ip'): void
    {
        $keys = $type === 'ip' ? [
            "block_until:{$identifier}",
            "failed_attempts:ip:{$identifier}",
            "offense_count:{$identifier}",
        ] : [
            "failed_attempts:email:{$identifier}",
            "account_locked:{$identifier}",
        ];

        foreach ($keys as $key) {
            Cache::forget($this->cachePrefix.$key);
        }
    }

    /**
     * Add IP to whitelist.
     */
    public function addToWhitelist(string $ipAddress, ?string $reason = null): bool
    {
        // Remove from blocklist if exists
        IpList::where('ip_address', $ipAddress)->where('type', 'blocklist')->delete();
        $this->clearSecurityCache($ipAddress);

        IpList::updateOrCreate(
            ['ip_address' => $ipAddress, 'type' => 'whitelist'],
            [
                'reason' => $reason ?? 'IP address whitelisted',
                'created_by' => Auth::id(),
            ]
        );

        IpList::forgetIpCache($ipAddress);
        SecurityLog::log('ip_whitelisted', null, $ipAddress, $reason ?? 'IP address whitelisted');

        return true;
    }

    /**
     * Remove IP from whitelist.
     */
    public function removeFromWhitelist(string $ipAddress): void
    {
        IpList::where('ip_address', $ipAddress)->where('type', 'whitelist')->delete();
        IpList::forgetIpCache($ipAddress);
        SecurityLog::log('ip_whitelist_removed', null, $ipAddress, 'IP address removed from whitelist');
    }

    /**
     * Get all blocked IPs.
     *
     * @return Collection<int, IpList>
     */
    public function getBlocklist()
    {
        return IpList::blocklist()->with('creator')->latest()->get();
    }

    /**
     * Get all whitelisted IPs.
     *
     * @return Collection<int, IpList>
     */
    public function getWhitelist()
    {
        return IpList::whitelist()->with('creator')->latest()->get();
    }

    /**
     * Check if account is locked.
     */
    public function isAccountLocked(string $email): bool
    {
        return Cache::has($this->cachePrefix."account_locked:{$email}");
    }

    /**
     * Lock an account temporarily.
     */
    public function lockAccount(string $email, ?string $reason = null): void
    {
        Cache::put(
            $this->cachePrefix."account_locked:{$email}",
            now()->addMinutes($this->accountLockMinutes)->toIso8601String(),
            $this->accountLockMinutes * 60
        );

        // Send real-time alert
        app(SecurityNotificationService::class)->warning(
            'account_locked',
            'Account Locked',
            $reason ?? "Account {$email} has been locked for {$this->accountLockMinutes} minutes",
            ['email' => $email, 'lock_duration_minutes' => $this->accountLockMinutes]
        );
    }

    /**
     * Unlock an account.
     */
    public function unlockAccount(string $email): void
    {
        Cache::forget($this->cachePrefix."account_locked:{$email}");
        Cache::forget($this->cachePrefix."failed_attempts:email:{$email}");
    }

    /**
     * Get remaining account lockout time in seconds.
     */
    public function getAccountLockoutRemaining(string $email): int
    {
        $key = $this->cachePrefix."account_locked:{$email}";
        $lockUntil = Cache::get($key);

        if (! is_string($lockUntil)) {
            return 0;
        }

        $remaining = Carbon::parse($lockUntil)->diffInSeconds(now(), false);

        return (int) max(0, -$remaining);
    }

    /**
     * Get lockout duration in minutes.
     */
    public function getLockoutDuration(): int
    {
        return $this->accountLockMinutes;
    }

    /**
     * Get max failed attempts threshold.
     */
    public function getMaxFailedAttempts(): int
    {
        return $this->maxFailedAttempts;
    }

    /**
     * Record suspicious activity.
     *
     * @param  array<string, mixed>  $metadata
     */
    public function recordSuspiciousActivity(string $description, ?User $user = null, array $metadata = [], string $eventType = 'suspicious_activity'): void
    {
        SecurityLog::log($eventType, $user, IpHelper::getClientIp(request()), $description, $metadata);
    }

    /**
     * Record a hit on a global blacklist
     */
    public function recordGlobalBlacklistHit(string $ipAddress, string $reason): void
    {
        SecurityLog::log('global_blacklist_blocked', null, $ipAddress, $reason);

        // Send real-time alert
        app(SecurityNotificationService::class)->critical(
            'dnsbl_hit',
            'DNSBL Blacklist Hit',
            "IP {$ipAddress} found on global blacklist",
            ['ip' => $ipAddress, 'reason' => $reason]
        );
    }

    /**
     * Record a hit on a geolocation policy
     */
    public function recordCountryBlock(string $ipAddress, string $reason): void
    {
        SecurityLog::log('country_blocked', null, $ipAddress, $reason);
    }

    /**
     * Get security statistics.
     */

    /**
     * @return array<string, mixed>
     */
    public function getSecurityStats(int $days = 30): array
    {
        $since = now()->subDays($days);

        return [
            'total_events' => SecurityLog::where('created_at', '>=', $since)->count(),
            'failed_logins' => SecurityLog::where('event_type', 'login_failed')
                ->where('created_at', '>=', $since)
                ->count(),
            'successful_logins' => SecurityLog::where('event_type', 'login_success')
                ->where('created_at', '>=', $since)
                ->count(),
            'blocked_ips' => SecurityLog::whereIn('event_type', ['ip_blocked_temp', 'ip_blocked_permanent'])
                ->where('created_at', '>=', $since)
                ->count(),
            'suspicious_activities' => SecurityLog::where('event_type', 'suspicious_activity')
                ->where('created_at', '>=', $since)
                ->count(),
            'recent_events' => SecurityLog::where('created_at', '>=', $since)
                ->latest()
                ->limit(50)
                ->with('user')
                ->get(),
        ];
    }

    /**
     * Check if an IP is protected (should never be blocked).
     * Only protects localhost - private IPs can still be blocked for security.
     */
    public function isProtectedIp(string $ip): bool
    {
        // Only protect localhost IPs (development/testing)
        $localhostIps = ['127.0.0.1', '::1', 'localhost'];

        return in_array($ip, $localhostIps);
    }

    /**
     * Check if the request has already passed the security shield (Trust Cache).
     * Now supports both IP-based and Cookie-based (Session) trust to handle mobile IP rotation.
     */
    public function isShieldVerified(string $ipAddress, string $userAgent): bool
    {
        // 1. Check cookie-based trust.
        // For browser page loads, cookie trust is the source of truth so incognito
        // or a fresh browser session will still see the challenge flow.
        $cookieToken = request()->cookie('shield_trust');
        $cookieTrusted = false;
        if (is_string($cookieToken)) {
            $expectedToken = $this->generateTrustToken($ipAddress, $userAgent);
            if (hash_equals($expectedToken, $cookieToken)) {
                $cookieTrusted = true;
            }
        }

        $request = request();
        $isApiRequest = $request->expectsJson() || $request->is('api/*');
        $allowCacheFallback = app()->runningUnitTests() || app()->runningInConsole();
        if (! $isApiRequest && ! $allowCacheFallback) {
            return $cookieTrusted;
        }

        // 2. API fallback: keep IP+UA cache trust for non-browser/API clients.
        if ($cookieTrusted) {
            return true;
        }

        // [SECURITY FIX H-03] Use HMAC-SHA256 cache key (was MD5).
        $appKeyForCache = config('app.key');
        $ipKey = 'shield:verified:'.hash_hmac('sha256', $ipAddress.'|'.$userAgent, is_string($appKeyForCache) ? $appKeyForCache : '');

        return Cache::has($this->cachePrefix.$ipKey);
    }

    /**
     * Generate a trust token that is stable across minor IP changes (same /24 subnet).
     */
    protected function generateTrustToken(string $ipAddress, string $userAgent): string
    {
        // Get /24 subnet for IPv4 or /64 for IPv6 to allow minor connectivity shifts
        $ipParts = explode('.', $ipAddress);
        $packedIp = inet_pton($ipAddress);
        $subnet = (count($ipParts) === 4)
            ? implode('.', array_slice($ipParts, 0, 3)) // e.g. 1.2.3.x
            : ($packedIp !== false ? substr(bin2hex($packedIp), 0, 16) : $ipAddress); // IPv6 prefix fallback

        $appKey = config('app.key');
        $appKeyString = is_string($appKey) ? $appKey : '';

        // [SECURITY FIX H-03] Use HMAC-SHA256 instead of MD5.
        // MD5 is cryptographically broken; HMAC-SHA256 with APP_KEY as secret
        // is resistant to collision and brute-force attacks.
        return hash_hmac('sha256', $subnet.'|'.$userAgent, $appKeyString);
    }

    /**
     * Record a successful shield verification.
     */
    public function recordShieldVerification(string $ipAddress, string $userAgent, ?string $fingerprint = null): void
    {
        // [SECURITY FIX H-03] Use HMAC-SHA256 cache key (was MD5).
        $appKey = config('app.key');
        $ipKey = 'shield:verified:'.hash_hmac('sha256', $ipAddress.'|'.$userAgent, is_string($appKey) ? $appKey : '');
        // Trust cache TTL mirrors shield trust cookie TTL for consistent behavior.
        Cache::put($this->cachePrefix.$ipKey, true, now()->addMinutes($this->getShieldTrustTtlMinutes()));

        // We issue the cookie in the controller for better response handling

        // Only log success if enabled in settings
        if (Setting::get('shield_log_verification_success', false)) {
            SecurityLog::log('shield_verified', null, $ipAddress, 'Security shield challenge passed', [
                'user_agent' => $userAgent,
                'fingerprint' => $fingerprint,
            ]);
        }
    }

    /**
     * Get the trust token for the current response.
     */
    public function getShieldTrustCookieValue(string $ipAddress, string $userAgent): string
    {
        return $this->generateTrustToken($ipAddress, $userAgent);
    }

    /**
     * Get trust TTL in minutes for shield trust cache/cookie.
     * Default: 7 days to reduce repeated challenge loops for normal users.
     */
    public function getShieldTrustTtlMinutes(): int
    {
        $raw = Setting::get('shield_trust_ttl_minutes', 10080);
        $ttl = is_numeric($raw) ? (int) $raw : 10080;

        // Clamp between 1 hour and 30 days.
        return max(60, min($ttl, 43200));
    }

    /**
     * Generate a signed nonce for the PoW challenge.
     * Includes difficulty in the signed payload so verification uses the same difficulty.
     */
    public function generateShieldNonce(string $ipAddress): string
    {
        $timestamp = time();
        $salt = bin2hex(random_bytes(16));
        $difficulty = $this->getShieldDifficulty();
        $data = "{$ipAddress}:{$timestamp}:{$salt}:{$difficulty}";
        $key = config('app.key');
        $signature = hash_hmac('sha256', $data, is_string($key) ? $key : '');

        return base64_encode("{$data}:{$signature}");
    }

    /**
     * Verify the PoW solution.
     * Supports both new (5-part with embedded difficulty) and legacy (4-part) nonce formats.
     */
    public function verifyShieldSolution(string $nonce, string $solution, string $ipAddress): bool
    {
        $decoded = base64_decode($nonce);
        if ($decoded === '' || $decoded === '0') {
            return false;
        }

        $parts = explode(':', $decoded);

        // Support new format (5 parts: ip:timestamp:salt:difficulty:signature)
        // and legacy format (4 parts: ip:timestamp:salt:signature)
        if (count($parts) === 5) {
            [$nonceIp, $timestamp, $salt, $nonceDifficulty, $signature] = $parts;
            $difficulty = is_numeric($nonceDifficulty) ? (int) $nonceDifficulty : $this->getShieldDifficulty();
            $extData = "{$nonceIp}:{$timestamp}:{$salt}:{$nonceDifficulty}";
        } elseif (count($parts) === 4) {
            // Legacy format — fall back to current difficulty
            [$nonceIp, $timestamp, $salt, $signature] = $parts;
            $difficulty = $this->getShieldDifficulty();
            $extData = "{$nonceIp}:{$timestamp}:{$salt}";
        } else {
            return false;
        }

        // Verify IP matches
        if ($nonceIp !== $ipAddress) {
            return false;
        }

        // Verify signature
        $key = config('app.key');
        if (! hash_equals(hash_hmac('sha256', $extData, is_string($key) ? $key : ''), $signature)) {
            return false;
        }

        // Verify TTL (nonce valid for 10 minutes)
        if (time() - (int) $timestamp > 600) {
            return false;
        }

        // Verify Nonce is not reused (Replay Attack Prevention)
        $nonceUsageKey = $this->cachePrefix.'shield:nonce_used:'.md5($nonce);
        if (Cache::has($nonceUsageKey)) {
            SecurityLog::log('shield_replay_detected', null, $ipAddress, 'PoW challenge replay detected', ['nonce' => $nonce]);

            return false;
        }

        // Verify PoW solution
        // The solution 'n' must make SHA256(nonce + n) start with the required number of zeros
        $hash = hash('sha256', $nonce.$solution);
        $target = str_repeat('0', $difficulty);

        if (str_starts_with($hash, $target)) {
            // Mark nonce as used (valid for the remainder of its 10-minute TTL)
            $remainingTtl = 600 - (time() - (int) $timestamp);
            if ($remainingTtl > 0) {
                Cache::put($nonceUsageKey, true, $remainingTtl);
            }

            return true;
        }

        return false;
    }

    /**
     * Get dynamic difficulty based on recent traffic.
     */
    public function getShieldDifficulty(?string $ip = null, ?string $sessionId = null): int
    {
        $settingValue = Setting::get('shield_protection_difficulty', 4);
        $baseDifficulty = is_numeric($settingValue) ? (int) $settingValue : 4;

        // 1. Global Scaling: Check for traffic spike in last minute
        $spikeKey = $this->cachePrefix.'shield:attempts_per_minute';
        $attemptsValue = Cache::get($spikeKey, 0);
        $attempts = is_numeric($attemptsValue) ? (int) $attemptsValue : 0;

        $globalModifier = 0;
        if ($attempts > 2000) {
            $globalModifier = 2;
        } elseif ($attempts > 500) {
            $globalModifier = 1;
        }

        // 2. Individual Scaling: Increase difficulty for suspicious behavior
        $individualModifier = 0;
        if ($ip) {
            $anomalyService = app(AnomalyDetectionService::class);
            $score = $anomalyService->getScore($ip, $sessionId);

            if ($score >= 75) {
                $individualModifier = 2; // Very suspicious
            } elseif ($score >= 40) {
                $individualModifier = 1; // Suspicious
            }
        }

        return $baseDifficulty + $globalModifier + $individualModifier;
    }

    /**
     * Track verification attempts for dynamic scaling.
     */
    public function trackShieldAttempt(): void
    {
        $key = $this->cachePrefix.'shield:attempts_per_minute';
        if (! Cache::has($key)) {
            Cache::put($key, 1, 60);
        } else {
            Cache::increment($key);
        }
    }

    /**
     * Get statistics for the Bot Shield protection.
     *
     * @return array<string, mixed>
     */
    public function getShieldStats(): array
    {
        $attemptsKey = $this->cachePrefix.'shield:attempts_per_minute';
        $attemptsValue = Cache::get($attemptsKey, 0);
        $attempts = is_numeric($attemptsValue) ? (int) $attemptsValue : 0;

        return [
            'verifications' => SecurityLog::where('event_type', 'shield_verified')->count(),
            'failures' => SecurityLog::where('event_type', 'shield_failed')->count(),
            'honeypot' => SecurityLog::where('event_type', 'shield_honeypot')->count(),
            'scannersBlocked' => SecurityLog::where('event_type', 'malicious_scanner_blocked')->count(),
            'extensionsBlocked' => SecurityLog::where('event_type', 'malicious_extension_blocked')->count(),
            'currentDifficulty' => $this->getShieldDifficulty(),
            'isScaling' => $attempts > 500,
        ];
    }

    /**
     * Check if an IP address is in a global blacklist (e.g. Spamhaus)
     */
    public function isIpInGlobalBlacklist(string $ipAddress): bool
    {
        if ($this->isProtectedIp($ipAddress) || IpList::isWhitelisted($ipAddress)) {
            return false;
        }

        // Check cache first
        $cacheKey = $this->cachePrefix."global_blacklist:{$ipAddress}";
        $cached = Cache::get($cacheKey);

        if ($cached !== null) {
            return (bool) $cached;
        }

        // Simple DNSBL check (Spamhaus ZEN)
        $dnsblResult = $this->checkDnsbl($ipAddress, 'zen.spamhaus.org');

        // Logic check:
        // 127.0.0.2  = SBL (Known Spammers)
        // 127.0.0.3  = SBL CSS (Spam detected)
        // 127.0.0.4  = XBL (Exploits - often VPNs/WARP)
        // 127.0.0.10 = PBL (Policy based - dynamic IPs)

        // Block only SBL/CSS which are high-confidence malicious sources.
        // We do NOT block XBL (127.0.0.4+) by default because it hits Cloudflare/VPN users.
        $isBlacklisted = in_array($dnsblResult, ['127.0.0.2', '127.0.0.3']);

        // Cache for 1 hour
        Cache::put($cacheKey, $isBlacklisted, now()->addHour());

        return $isBlacklisted;
    }

    /**
     * Check IP against a DNS Blacklist
     */
    protected function checkDnsbl(string $ipAddress, string $dnsblHost): ?string
    {
        // DNSBL works by reversing the IP and appending the host
        // e.g. 1.2.3.4 becomes 4.3.2.1.zen.spamhaus.org
        if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $reverseIp = implode('.', array_reverse(explode('.', $ipAddress)));
            $lookup = $reverseIp.'.'.$dnsblHost;

            // If gethostbyname returns something else than the input, it's listed
            $result = gethostbyname($lookup);

            if ($result !== $lookup) {
                return $result;
            }
        }

        return null;
    }
}
