<?php

declare(strict_types=1);

namespace Modules\Core\Security\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Modules\Core\Security\Models\IpList;
use Modules\Core\Security\Services\SecurityService;
use Modules\Core\System\Helpers\IpHelper;
use Modules\Core\System\Models\Setting;
use Modules\Core\System\Models\User;
use Modules\Core\System\Services\AnomalyDetectionService;
use Modules\Core\System\Services\GeoIpService;
use Modules\Core\System\Services\SecurityMaintenanceService;
use Modules\Core\System\Traits\MaintenanceBypass;
use Symfony\Component\HttpFoundation\Response;

class VerifyConnection
{
    use MaintenanceBypass;

    /**
     * Known search engine bot User-Agent patterns.
     * These will be verified via rDNS to prevent spoofing.
     *
     * @var array<string, list<string>>
     */
    protected array $searchBotPatterns = [
        'Googlebot' => ['googlebot.com', 'google.com'],
        'Bingbot' => ['search.msn.com'],
        'Baiduspider' => ['baidu.com', 'baidu.jp'],
        'YandexBot' => ['yandex.ru', 'yandex.net', 'yandex.com'],
        'DuckDuckBot' => ['duckduckgo.com'],
        'Applebot' => ['applebot.apple.com'],
    ];

    public function __construct(protected SecurityService $securityService, protected AnomalyDetectionService $anomalyService) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $mode = Setting::get('shield_protection_mode', 'off');

        // Bypasses
        if ($mode === 'off') {
            return $next($request);
        }

        // Bypass shield ONLY if the module is explicitly paused via emergency switch
        if (app(SecurityMaintenanceService::class)->isModulePaused('shield')) {
            return $next($request);
        }

        // 1. Static assets, OPTIONS requests, and safe/public/auth API bypass
        if ($request->isMethod('OPTIONS') ||
            $this->isStaticAssetRequest($request) ||
            $request->is('api/v1/manage/*') ||
            $request->is('api/v1/student/*') ||
            $request->is('api/v1/teacher/*') ||
            $request->is('api/v1/dashboard/*') ||
            $request->is('api/v1/public/*') ||
            $request->is('api/v1/analytics/*') ||
            $request->is('api/v1/captcha/*') ||
            $request->is('api/v1/journal/frontend') ||
            $request->is('api/v1/user') ||
            $request->is('api/v1/logout') ||
            $request->is('api/v1/profile*') ||
            $request->is('api/v1/login*') ||
            $request->is('api/v1/register*') ||
            $request->is('api/v1/forgot-password') ||
            $request->is('api/v1/reset-password') ||
            $request->is('api/v1/verify-email') ||
            $request->is('api/v1/resend-verification') ||
            $request->is('api/v1/password/*') ||
            $request->is('api/v1/license/*')) {
            return $next($request);
        }

        // 2. IP Bypasses (Whitelisted or Protected)
        $ip = IpHelper::getClientIp($request);
        if ($this->securityService->isProtectedIp($ip) || IpList::isWhitelisted($ip)) {
            return $next($request);
        }

        // 3. Internal Polling request bypass (Prevent 429 anomaly blocks on dashboard metric polling)
        if ($this->isInternalPollingRequest($request)) {
            return $next($request);
        }

        // 4. Admin user bypass
        if (Auth::check()) {
            $authUser = Auth::user();
            if ($authUser instanceof User && ($authUser->hasRole('admin') || $authUser->hasRole('super'))) {
                return $next($request);
            }
        }

        // 5. Verify existing trust session
        if ($this->securityService->isShieldVerified($ip, (string) $request->userAgent())) {
            return $next($request);
        }

        // 6. Verification endpoint bypass (must allow the verification itself)
        if ($request->is('api/v1/security/verify-connection')) {
            return $next($request);
        }

        // 7. Suspicious Only mode — challenge only genuinely suspicious requests
        if ($mode === 'suspicious') {
            $sessionId = $request->hasSession() ? $request->session()->getId() : 'stateless_'.md5($ip.($request->userAgent() ?? ''));

            if ($this->anomalyService->shouldBlock($ip, $sessionId)) {
                $this->securityService->blockIpPermanently($ip, 'Extreme statistical anomaly score detected');

                return response()->json(['message' => 'Your connection has been flagged and blocked.'], 403);
            }

            if ($this->anomalyService->shouldChallenge($ip, $sessionId) || $this->isSuspiciousRequest($request, $ip)) {
                // Proceed to expensive verification before challenging
            } else {
                return $next($request);
            }
        }

        // 8. Expensive Verifications (Search Bots & Geolocation)
        // These are only run if we are about to challenge the user
        if ($this->isVerifiedSearchBot($request)) {
            return $next($request);
        }

        if (Setting::get('shield_enable_ip_intelligence', false) || ! empty(Setting::get('shield_allowed_countries', []))) {
            // Check Global Blacklist
            if (Setting::get('shield_enable_ip_intelligence', false) && $this->securityService->isIpInGlobalBlacklist($ip)) {
                $this->securityService->recordGlobalBlacklistHit($ip, 'Detected in global blacklist - Challenge required');
            }

            // Check Geolocation
            $geoIpService = app(GeoIpService::class);
            if (! $geoIpService->isCountryAllowed($ip)) {
                $this->securityService->recordCountryBlock($ip, 'Geolocation mismatch detected; challenge required');
            }
        }

        // ISSUE CHALLENGE
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->issueAjaxChallenge($request, $ip);
        }

        return $this->issueHtmlChallenge($request, $ip);
    }

    /**
     * Check if this is a static asset or resource request that should bypass the shield.
     */
    protected function isStaticAssetRequest(Request $request): bool
    {
        return $request->is(
            'assets/*',
            'storage/*',
            '*/favicon.ico',
            'build/*',
            'fonts/*',
            'images/*',
            'css/*',
            'js/*',
            '_debugbar/*',
            'sanctum/csrf-cookie',
            'cdn-cgi*'
        );
    }

    /**
     * Check if the request is an internal high-frequency polling endpoint (like dashboard cache stats).
     * Bypassing these prevents the anomaly detector from issuing 429 Security Challenges.
     */
    protected function isInternalPollingRequest(Request $request): bool
    {
        return $request->is(
            'api/v1/manage/system/cache-status',
            'api/v1/manage/system/redis/cache-stats',
            'api/v1/manage/system/cache/warm',
            'api/v1/manage/security/health',
            'api/v1/manage/security/file-integrity',
            'api/v1/manage/security/run-integrity-check',
            'api/v1/manage/security/file-integrity/resync'
        );
    }

    /**
     * Check if the request is from a verified search engine bot.
     * Uses User-Agent pattern matching first, then verifies via reverse DNS
     * to prevent User-Agent spoofing.
     */
    protected function isVerifiedSearchBot(Request $request): bool
    {
        $userAgent = (string) $request->userAgent();
        if ($userAgent === '' || $userAgent === '0') {
            return false;
        }

        $ip = IpHelper::getClientIp($request);

        // Check cache first to avoid repeated DNS lookups
        $cacheKey = "shield:bot_verified:{$ip}";
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return (bool) $cached;
        }

        // Check if User-Agent matches any known bot pattern
        $matchedDomains = null;
        foreach ($this->searchBotPatterns as $botName => $validDomains) {
            if (stripos($userAgent, $botName) !== false) {
                $matchedDomains = $validDomains;
                break;
            }
        }

        if ($matchedDomains === null) {
            return false;
        }

        // Verify via reverse DNS lookup to prevent spoofing
        $isVerified = $this->verifyBotIpViaDns($ip, $matchedDomains);

        // Cache the result for 6 hours
        Cache::put($cacheKey, $isVerified, now()->addHours(6));

        return $isVerified;
    }

    /**
     * Verify a bot's IP via reverse DNS lookup.
     * Steps: IP → rDNS hostname → check domain → forward DNS → compare IP.
     *
     * @param  array<string>  $validDomains
     */
    protected function verifyBotIpViaDns(string $ip, array $validDomains): bool
    {
        $hostname = gethostbyaddr($ip);

        // gethostbyaddr returns the IP itself if lookup fails
        if ($hostname === $ip || $hostname === false) {
            return false;
        }

        // Check if hostname ends with one of the valid domains
        $domainMatch = false;
        foreach ($validDomains as $domain) {
            if (str_ends_with($hostname, '.'.$domain) || $hostname === $domain) {
                $domainMatch = true;
                break;
            }
        }

        if (! $domainMatch) {
            return false;
        }

        // Forward DNS verification: hostname should resolve back to the original IP
        $forwardIp = gethostbyname($hostname);

        return $forwardIp === $ip;
    }

    /**
     * Determine if a request appears suspicious based on heuristics.
     * Used in "suspicious" mode to only challenge requests that exhibit bot-like signals.
     */
    protected function isSuspiciousRequest(Request $request, string $ip): bool
    {
        $suspicionScore = 0;

        $userAgent = (string) $request->userAgent();

        // 1. Missing or empty User-Agent (high signal)
        if ($userAgent === '' || $userAgent === '0') {
            $suspicionScore += 3;
            $sessionId = $request->hasSession() ? $request->session()->getId() : 'stateless_'.md5($ip);
            $this->anomalyService->trackEvent('suspicious_ua', $ip, $sessionId);
        }

        // 2. Missing Accept-Language header (browsers always send this)
        if (! $request->header('Accept-Language')) {
            $suspicionScore += 2;
            $sessionId = $request->hasSession() ? $request->session()->getId() : 'stateless_'.md5($ip);
            $this->anomalyService->trackEvent('missing_headers', $ip, $sessionId);
        }

        // 3. Missing Accept header
        if (! $request->header('Accept')) {
            $suspicionScore += 1;
        }

        // 4. Suspicious User-Agent patterns (known scanners/tools)
        $suspiciousUaPatterns = [
            'curl/', 'wget/', 'python-requests/', 'httpie/', 'postman',
            'scrapy/', 'httpclient/', 'java/', 'go-http-client/',
            'node-fetch/', 'libwww-perl/', 'mechanize', 'phantom', 'headless',
        ];
        foreach ($suspiciousUaPatterns as $pattern) {
            if (stripos($userAgent, $pattern) !== false) {
                $suspicionScore += 2;
                break;
            }
        }

        // 5. High request rate from this IP (>120 req/min = suspicious)
        $rateKey = "shield:rate:{$ip}";
        $rawRate = Cache::get($rateKey, 0);
        $currentRate = is_numeric($rawRate) ? (int) $rawRate : 0;
        Cache::put($rateKey, $currentRate + 1, now()->addMinute());
        if ($currentRate > 120) {
            $suspicionScore += 2;
        }

        // Threshold: score of 3 or higher means suspicious
        return $suspicionScore >= 3;
    }

    /**
     * Issue a challenge for AJAX/API requests.
     */
    protected function issueAjaxChallenge(Request $request, string $ip): Response
    {
        $nonce = $this->securityService->generateShieldNonce($ip);

        $sessionId = 'stateless_'.md5($ip.($request->userAgent() ?? ''));
        try {
            if ($request->hasSession()) {
                $sessionId = $request->session()->getId();
            }
        } catch (\Throwable) {
            // Keep stateless ID
        }

        $difficulty = $this->securityService->getShieldDifficulty($ip, $sessionId);

        return response()->json([
            'message' => 'Connection verification required',
            'challenge' => [
                'nonce' => $nonce,
                'difficulty' => $difficulty,
            ],
        ], 429)->withHeaders([
            'X-Shield-Challenge' => $nonce,
            'X-Shield-Difficulty' => $difficulty,
        ]);
    }

    /**
     * Issue a challenge for direct HTML requests.
     */
    protected function issueHtmlChallenge(Request $request, string $ip): Response
    {
        $nonce = $this->securityService->generateShieldNonce($ip);

        $sessionId = 'stateless_'.md5($ip.($request->userAgent() ?? ''));
        try {
            if ($request->hasSession()) {
                $sessionId = $request->session()->getId();
            }
        } catch (\Throwable) {
            // Keep stateless ID
        }

        $difficulty = $this->securityService->getShieldDifficulty($ip, $sessionId);

        // Pass target URL to the view to allow redirection after verification
        // Using strict anti-cache headers to prevent Android built-in browsers from infinity-looping on cached 429s
        return response()->view('errors.security.challenge', [
            'nonce' => $nonce,
            'difficulty' => $difficulty,
            'redirectTo' => $request->fullUrl(),
        ], 429)->withHeaders([
            'Cache-Control' => 'no-cache, no-store, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => 'Fri, 01 Jan 1990 00:00:00 GMT',
        ]);
    }
}
