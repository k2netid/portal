<?php

namespace Modules\Core\Security\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\Core\Security\Services\SecurityService;
use Modules\Core\System\Helpers\IpHelper;
use Modules\Core\System\Models\Setting;
use Modules\Core\System\Services\AnomalyDetectionService;
use Modules\Core\System\Services\SecurityMaintenanceService;
use Modules\Core\System\Traits\MaintenanceBypass;
use Symfony\Component\HttpFoundation\Response;

class BlockMaliciousBots
{
    use MaintenanceBypass;

    public function __construct(protected SecurityService $securityService, protected AnomalyDetectionService $anomalyService) {}

    /** Browser probes should look like SPA 404 (aligned with routes/web.php sinkhole). */
    private function blockedScannerResponse(Request $request): Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            abort(404);
        }

        return redirect('/404');
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function logSecurityWarning(string $message, array $context = []): void
    {
        try {
            Log::channel('security')->warning($message, $context);
        } catch (\Throwable $e) {
            Log::warning($message, array_merge($context, ['security_log_error' => $e->getMessage()]));
        }
    }

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = IpHelper::getClientIp($request);

        // Bypass for OPTIONS requests (Preflight)
        if ($request->isMethod('OPTIONS')) {
            return $next($request);
        }

        // Fast-path: if IP is already blocked, reject immediately
        if ($this->securityService->isIpBlocked($ip)) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $seconds = $this->securityService->getRemainingBlockTime($ip);
                $minutes = max(1, (int) ceil($seconds / 60));

                return response()->json([
                    'success' => false,
                    'message' => "Your IP address has been temporarily blocked. Please try again in {$minutes} minute(s).",
                    'retry_after' => $seconds,
                ], 429);
            }

            return response('Forbidden', 403);
        }

        // Bypass scanner detection during maintenance mode or for whitelisted maintenance routes
        if (app(SecurityMaintenanceService::class)->isModulePaused('scanner') || $this->shouldBypassMaintenance($request)) {
            return $next($request);
        }

        $path = $request->path();

        // 1. Get hardcoded blocked paths
        $blockedPaths = [
            'wp-login.php',
            'wp-admin',
            'xmlrpc.php',
            '.env',
            'actuator/health',
            '_profiler',
            'phpinfo',
            'config.json',
            'composer.json',
            'composer.lock',
            'package.json',
            'yarn.lock',
            'telescope',
            'debugbar',
            'storage/logs',
            '/.git',
        ];

        // 2. Merge with learned paths from database/cache
        $learnedPaths = Setting::get('security_learned_scanner_paths', []);
        if (is_array($learnedPaths) && $learnedPaths !== []) {
            /** @var array<int, string> $learnedPathStrings */
            $learnedPathStrings = [];
            foreach ($learnedPaths as $learnedPath) {
                if (is_string($learnedPath) && $learnedPath !== '') {
                    $learnedPathStrings[] = $learnedPath;
                }
            }
            $blockedPaths = array_unique(array_merge($blockedPaths, $learnedPathStrings));
        }

        foreach ($blockedPaths as $blockedPath) {
            if (str_contains($path, $blockedPath)) {
                $this->logSecurityWarning('Blocked malicious scanner', [
                    'ip' => $ip,
                    'path' => $path,
                    'user_agent' => $request->userAgent(),
                    'type' => 'blocked_path',
                    'is_learned' => is_array($learnedPaths) && in_array($blockedPath, $learnedPaths, true),
                ]);

                // Record in database for UI
                $this->securityService->recordSuspiciousActivity('Blocked malicious scanner path: '.$path, null, [
                    'path' => $path,
                ], 'malicious_scanner_blocked');

                // Track in anomaly score
                $safeSessionId = 'stateless_'.md5($ip.($request->userAgent() ?? ''));
                try {
                    if ($request->hasSession()) {
                        $safeSessionId = $request->session()->getId();
                    }
                } catch (\Throwable) {
                }
                $this->anomalyService->trackEvent('sensitive_path', $ip, $safeSessionId);

                $this->autoBlockIfThresholdReached($ip, $path, 'path');

                return $this->blockedScannerResponse($request);
            }
        }

        // Block specific file extensions often probed
        // Exempt the system journal API which legitimately serves .log files
        if (preg_match('/\.(sql|bak|old|swp|zip|tar|gz|rar|env|git|ini|log|sh)$/i', $path, $matches)) {
            $extension = $matches[1];
            // Allow legitimate log viewing via API
            $rawWhitelist = Cache::get('security:white_listed_ips', []);
            $whitelist = is_array($rawWhitelist) ? $rawWhitelist : [];
            if (in_array($ip, $whitelist, true)) {
                return $next($request);
            }
            if (str_starts_with($path, 'api/v1/admin/core/system-journal/') || str_starts_with($path, 'api/v1/manage/system-journal/')) {
                return $next($request);
            }

            Log::channel('security')->warning('Blocked malicious extension', [
                'ip' => $ip,
                'path' => $path,
                'user_agent' => $request->userAgent(),
                'type' => 'blocked_extension',
            ]);

            // Record in database for UI
            $this->securityService->recordSuspiciousActivity('Blocked malicious file extension: '.$path, null, [
                'path' => $path,
            ], 'malicious_extension_blocked');

            // Track in anomaly score
            $this->anomalyService->trackEvent('sensitive_path', $ip, session()->getId());

            $this->autoBlockIfThresholdReached($ip, $path, 'extension', $extension);

            return response('Forbidden', 403);
        }

        return $next($request);
    }

    /**
     * Auto-block IP permanently if it exceeds the scanner hit threshold.
     * Tracks hits per IP using cache with a 1-hour window.
     */
    protected function autoBlockIfThresholdReached(string $ip, string $path, string $type, ?string $value = null): void
    {
        $threshold = 10; // hits within the window
        $windowMinutes = 60; // 1 hour window
        $cacheKey = "scanner_hits:{$ip}";

        $rawHits = Cache::get($cacheKey, 0);
        $hits = is_numeric($rawHits) ? (int) $rawHits : 0;
        $hits++;

        Cache::put($cacheKey, $hits, now()->addMinutes($windowMinutes));

        if ($hits >= $threshold) {
            // Auto-block permanently
            if ($type === 'extension') {
                $reasonRaw = json_encode([
                    'key' => 'features.security.reasons.maliciousExtension',
                    'params' => ['ext' => $value],
                ]);
                $reason = is_string($reasonRaw) ? $reasonRaw : 'Malicious extension';
            } else {
                $reasonRaw = json_encode([
                    'key' => 'features.security.reasons.maliciousScanner',
                    'params' => ['path' => $path],
                ]);
                $reason = is_string($reasonRaw) ? $reasonRaw : 'Malicious scanner';
            }

            $this->securityService->blockIpPermanently($ip, $reason);

            Log::channel('security')->warning('Auto-blocked repeat scanner IP', [
                'ip' => $ip,
                'hits' => $hits,
                'last_path' => $path,
            ]);

            // Clear the counter since IP is now permanently blocked
            Cache::forget($cacheKey);
        }
    }
}
