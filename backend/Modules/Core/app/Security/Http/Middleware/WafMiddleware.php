<?php

// Re-save to trigger scan

declare(strict_types=1);

namespace Modules\Core\Security\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\Core\Security\Services\SecurityService;
use Modules\Core\System\Helpers\IpHelper;
use Modules\Core\System\Services\AnomalyDetectionService;
use Modules\Core\System\Services\SecurityMaintenanceService;
use Modules\Core\System\Traits\MaintenanceBypass;
use Symfony\Component\HttpFoundation\Response;

/**
 * Web Application Firewall (WAF) Middleware.
 * Detects and blocks common attack patterns:
 * - SQL Injection
 * - Cross-Site Scripting (XSS)
 * - Path Traversal
 * - Command Injection
 * Whitelists Publishing editor routes that legitimately contain HTML.
 */
class WafMiddleware
{
    use MaintenanceBypass;

    /** @var array<string> Routes that allow HTML content (Publishing editor) */
    private const WHITELISTED_ROUTES = [
        // New Modular Paths
        'api/v1/manage/publishing/contents',
        'api/v1/manage/publishing/content-templates',
        'api/v1/manage/layout/menus',
        'api/v1/manage/layout/widgets',
        'api/v1/manage/system/settings',
        'api/v1/manage/forms',
        'api/v1/manage/ai/generate',
        'api/v1/manage/media',
        // Frontend error journal: stacks often contain substrings the WAF treats as XSS
        // (e.g. window.location, script URLs) — scanning them produces false positives.
        'api/v1/journal/frontend',
    ];

    /** @var array<string> SQL injection patterns */
    private const SQL_PATTERNS = [
        '/(\bunion\b\s+\bselect\b)/i',
        '/(\bselect\b\s+.*\bfrom\b\s+\binformation_schema\b)/i',
        '/(\bor\b\s+1\s*=\s*1)/i',
        '/(\band\b\s+1\s*=\s*1)/i',
        "/('\s*(or|and)\s+'[^']*'\s*=\s*')/i",
        '/(\bdrop\b\s+\btable\b)/i',
        '/(\binsert\b\s+\binto\b)/i',
        '/(\bdelete\b\s+\bfrom\b)/i',
        '/(\bupdate\b\s+\bset\b)/i',
        '/(\/\*.*\*\/)/i',
        '/(\bexec\b\s*\()/i',
        '/(\bxp_cmdshell\b)/i',
        '/(\bbenchmark\b\s*\()/i',
        '/(\bsleep\b\s*\(\s*\d+\s*\))/i',
        '/(\bload_file\b\s*\()/i',
        '/(\boutfile\b\s)/i',
    ];

    /** @var array<string> XSS patterns */
    private const XSS_PATTERNS = [
        '/<script[\s>]/i',
        '/javascript\s*:/i',
        '/on(error|load|click|mouseover|focus|blur|submit|change|keyup|keydown)\s*=/i',
        '/<iframe[\s>]/i',
        '/<object[\s>]/i',
        '/<embed[\s>]/i',
        '/<svg[\s>].*on\w+\s*=/i',
        '/\beval\s*\(/i',
        '/\bexpression\s*\(/i',
        '/\bdocument\s*\.\s*(cookie|domain|write)/i',
        '/\bwindow\s*\.\s*location/i',
        '/data\s*:\s*text\/html/i',
        '/vbscript\s*:/i',
    ];

    /** @var array<string> Path traversal patterns */
    private const TRAVERSAL_PATTERNS = [
        '/\.\.[\/\\\\]/',
        '/%2e%2e[\/\\\\%]/i',
        '/%252e%252e/i',
        '/\.\.%c0%af/i',
        '/\.\.%c1%9c/i',
    ];

    /** @var array<string> Command injection patterns */
    private const COMMAND_PATTERNS = [
        '/[;&|`]\s*(cat|ls|dir|wget|curl|nc|bash|sh|python|php|perl|ruby|node)\b/i',
        '/\$\(.*\)/',
        '/`[^`]+`/',
        '/\|\s*(cat|ls|dir|wget|curl|nc|bash|sh)\b/i',
    ];

    public function __construct(
        private readonly SecurityService $securityService,
        private readonly AnomalyDetectionService $anomalyService,
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Bypass WAF for OPTIONS requests (Preflight)
        if ($request->isMethod('OPTIONS')) {
            return $next($request);
        }

        // Bypass WAF during maintenance mode or for whitelisted maintenance routes
        if (app(SecurityMaintenanceService::class)->isModulePaused('waf') || $this->shouldBypassMaintenance($request)) {
            return $next($request);
        }

        // Authenticated console API: light scan (path + query only) — body often large JSON
        if ($this->isAuthenticatedConsoleApi($request)) {
            $violation = $this->scanPath($request->path());
            if ($violation !== null) {
                return $this->blockRequest($request, $violation);
            }
            $violation = $this->scanParams($request->query->all(), 'query');
            if ($violation !== null) {
                return $this->blockRequest($request, $violation);
            }

            return $next($request);
        }

        // Skip for whitelisted routes (Publishing editor, AI generation, etc.)
        if ($this->isWhitelistedRoute($request)) {
            // Even for whitelisted routes, check URL params (not body)
            $violation = $this->scanParams($request->query->all(), 'query');
            if ($violation !== null) {
                return $this->blockRequest($request, $violation);
            }

            return $next($request);
        }

        // Scan URL path
        $violation = $this->scanPath($request->path());
        if ($violation !== null) {
            return $this->blockRequest($request, $violation);
        }

        // Scan query parameters
        $violation = $this->scanParams($request->query->all(), 'query');
        if ($violation !== null) {
            return $this->blockRequest($request, $violation);
        }

        // Scan request body (POST/PUT/PATCH)
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH'], true)) {
            $violation = $this->scanParams($request->all(), 'body');
            if ($violation !== null) {
                return $this->blockRequest($request, $violation);
            }
        }

        // Scan headers for injection attempts
        $violation = $this->scanHeaders($request);
        if ($violation !== null) {
            return $this->blockRequest($request, $violation);
        }

        return $next($request);
    }

    /**
     * Check if the request route is whitelisted for HTML content.
     * Uses exact path or path-prefix with '/' boundary to avoid over-matching.
     */
    private function isWhitelistedRoute(Request $request): bool
    {
        $path = $request->path();

        foreach (self::WHITELISTED_ROUTES as $route) {
            // Exact match or match with '/' boundary (e.g. "contents" matches "contents/123" but not "contentsSomething")
            if ($path === $route || str_starts_with($path, $route.'/')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Scan URL path for traversal attacks.
     *
     * @return array{type: string, pattern: string, value: string}|null
     */
    private function scanPath(string $path): ?array
    {
        foreach (self::TRAVERSAL_PATTERNS as $pattern) {
            if (preg_match($pattern, $path)) {
                return ['type' => 'path_traversal', 'pattern' => $pattern, 'value' => $path];
            }
        }

        return null;
    }

    /**
     * Scan parameters for attack patterns.
     *
     * @param  array<mixed, mixed>  $params
     * @return array{type: string, pattern: string, value: string}|null
     */
    private function scanParams(array $params, string $source): ?array
    {
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $result = $this->scanParams($value, $source);
                if ($result !== null) {
                    return $result;
                }

                continue;
            }

            $strValue = is_scalar($value) ? (string) $value : '';
            $strKey = (string) $key;

            // Scan key
            $result = $this->matchPatterns($strKey);
            if ($result !== null) {
                return $result;
            }

            // Scan value
            $result = $this->matchPatterns($strValue);
            if ($result !== null) {
                return $result;
            }
        }

        return null;
    }

    /**
     * Scan request headers for injection.
     *
     * @return array{type: string, pattern: string, value: string}|null
     */
    private function scanHeaders(Request $request): ?array
    {
        $headersToCheck = ['Referer', 'X-Forwarded-For', 'X-Forwarded-Host', 'User-Agent'];

        foreach ($headersToCheck as $header) {
            $value = $request->header($header);
            if ($value === null) {
                continue;
            }

            // Only check SQL and command injection in headers (not XSS)
            foreach (self::SQL_PATTERNS as $pattern) {
                if (preg_match($pattern, $value)) {
                    return ['type' => 'sql_injection', 'pattern' => $pattern, 'value' => "Header[$header]: $value"];
                }
            }

            foreach (self::COMMAND_PATTERNS as $pattern) {
                if (preg_match($pattern, $value)) {
                    return ['type' => 'command_injection', 'pattern' => $pattern, 'value' => "Header[$header]: $value"];
                }
            }
        }

        return null;
    }

    /**
     * Match a string against all attack patterns.
     *
     * @return array{type: string, pattern: string, value: string}|null
     */
    private function matchPatterns(string $input): ?array
    {
        if (strlen($input) < 3) {
            return null;
        }

        foreach (self::SQL_PATTERNS as $pattern) {
            if (preg_match($pattern, $input)) {
                return ['type' => 'sql_injection', 'pattern' => $pattern, 'value' => mb_substr($input, 0, 200)];
            }
        }

        foreach (self::XSS_PATTERNS as $pattern) {
            if (preg_match($pattern, $input)) {
                return ['type' => 'xss', 'pattern' => $pattern, 'value' => mb_substr($input, 0, 200)];
            }
        }

        foreach (self::TRAVERSAL_PATTERNS as $pattern) {
            if (preg_match($pattern, $input)) {
                return ['type' => 'path_traversal', 'pattern' => $pattern, 'value' => mb_substr($input, 0, 200)];
            }
        }

        foreach (self::COMMAND_PATTERNS as $pattern) {
            if (preg_match($pattern, $input)) {
                return ['type' => 'command_injection', 'pattern' => $pattern, 'value' => mb_substr($input, 0, 200)];
            }
        }

        return null;
    }

    /**
     * Block the request and record the violation.
     *
     * @param  array{type: string, pattern: string, value: string}  $violation
     */
    private function blockRequest(Request $request, array $violation): Response
    {
        $ip = IpHelper::getClientIp($request);

        Log::channel('security')->warning('WAF blocked request', [
            'ip' => $ip,
            'path' => $request->path(),
            'method' => $request->method(),
            'type' => $violation['type'],
            'value' => $violation['value'],
            'user_agent' => $request->userAgent(),
        ]);

        // Track in anomaly score
        $this->anomalyService->trackEvent('waf_violation', $ip, session()->getId());

        // Record in security journal for admin UI
        $this->securityService->recordSuspiciousActivity(
            "WAF blocked {$violation['type']}: {$violation['value']}",
            null,
            [
                'path' => $request->path(),
                'method' => $request->method(),
                'violation_type' => $violation['type'],
            ],
            'waf_'.$violation['type']
        );

        // Auto-escalate: track WAF violations per IP, permablock repeat offenders
        $this->escalateIfRepeatOffender($ip, $violation['type']);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => 'Request blocked by security policy.',
            ], 403);
        }

        return response('Forbidden', 403);
    }

    /**
     * Track WAF violations per IP and auto-block repeat offenders.
     */
    private function escalateIfRepeatOffender(string $ip, string $violationType): void
    {
        $cacheKey = "waf_violations:{$ip}";
        $raw = Cache::get($cacheKey, 0);
        $count = is_numeric($raw) ? (int) $raw : 0;
        $count++;

        Cache::put($cacheKey, $count, now()->addHour());

        // 5 WAF violations in 1 hour → permanent block
        if ($count >= 5) {
            $reasonRaw = json_encode([
                'key' => 'features.security.reasons.wafViolation',
                'params' => ['rule' => $violationType], // Assuming $violationType is intended for 'rule'
            ]);
            $reason = is_string($reasonRaw) ? $reasonRaw : 'WAF violation';

            $this->securityService->blockIpPermanently(
                $ip,
                $reason
            );

            Log::channel('security')->warning('WAF auto-blocked repeat offender', [
                'ip' => $ip,
                'violations' => $count,
                'last_type' => $violationType,
            ]);

            Cache::forget($cacheKey);
        }
    }

    private function isAuthenticatedConsoleApi(Request $request): bool
    {
        if (! str_starts_with($request->path(), 'api/v1/manage/')) {
            return false;
        }

        return $request->user() !== null;
    }
}
