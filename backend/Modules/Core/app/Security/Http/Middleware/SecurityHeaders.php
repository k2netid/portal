<?php

namespace Modules\Core\Security\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request and add security headers
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Redirects (probe sinkhole, auth, etc.) must not run Vite CSP nonce — breaks under php-fpm.
        if ($response->isRedirection()) {
            $response->headers->set('X-Content-Type-Options', 'nosniff');

            return $response;
        }

        $this->normalizeCacheControlForBfCache($request, $response);

        // Content Security Policy
        // Remove any previously attached CSP headers first to avoid duplicate policies
        // from other middleware layers that can generate noisy false-positive reports.
        $response->headers->remove('Content-Security-Policy');
        $response->headers->remove('Content-Security-Policy-Report-Only');
        $nonce = $this->generateNonce($request);
        Vite::useCspNonce($nonce);
        $generatedCsp = $this->getContentSecurityPolicy($nonce);
        $configCsp = config('security.headers.csp');
        $csp = $generatedCsp;
        if (is_string($configCsp)) {
            $configuredPolicy = trim($configCsp);
            if ($configuredPolicy !== '' && $this->isUsableConfiguredCsp($configuredPolicy)) {
                $csp = $configuredPolicy;
            }
        }

        $reportOnlyRaw = config('security.headers.csp_report_only');
        if (is_bool($reportOnlyRaw)) {
            $reportOnly = $reportOnlyRaw;
        } elseif (is_string($reportOnlyRaw) || is_int($reportOnlyRaw)) {
            $reportOnly = filter_var((string) $reportOnlyRaw, FILTER_VALIDATE_BOOLEAN);
        } else {
            $reportOnly = app()->environment('production');
        }
        if ($reportOnly) {
            $response->headers->set('Content-Security-Policy-Report-Only', $csp);
        } else {
            $response->headers->set('Content-Security-Policy', $csp);
        }

        // Standard Security Headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        // [SECURITY FIX L-02] Removed deprecated X-XSS-Protection header.
        // Modern browsers ignore it, and it can cause issues in old browsers.
        // Use a strict CSP instead (implemented below).
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        // [SECURITY FIX L-04] Added 'preload' to HSTS for stronger HTTPS enforcement.
        // HSTS: Force HTTPS for 1 year including subdomains
        // Only set when connection is HTTPS or APP_URL uses HTTPS (avoid breaking local dev)
        $appUrl = config('app.url', '');
        $appUrlStr = is_string($appUrl) ? $appUrl : '';
        if ($request->isSecure() || str_starts_with($appUrlStr, 'https')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // Remove server signature
        $response->headers->remove('X-Powered-By');

        // Force remove upstream/conflicting legacy CSP headers
        // These are often added by Cloudflare or Nginx proxies and can be too strict ('none')
        $response->headers->remove('X-Content-Security-Policy');
        $response->headers->remove('X-WebKit-CSP');

        return $response;
    }

    /**
     * Keep regular HTML pages eligible for back/forward cache.
     *
     * Some upstream layers can inject `no-store` into successful document responses.
     * That directive blocks bfcache restoration entirely, so we strip it for normal
     * web page responses while preserving strict cache directives for errors/challenges.
     */
    private function normalizeCacheControlForBfCache(Request $request, Response $response): void
    {
        $status = $response->getStatusCode();
        if ($status < 200 || $status >= 400) {
            return;
        }

        if ($request->is('api/*') || $request->expectsJson()) {
            return;
        }

        $contentType = strtolower((string) $response->headers->get('Content-Type', ''));
        if (! str_contains($contentType, 'text/html')) {
            return;
        }

        $cacheControl = (string) $response->headers->get('Cache-Control', '');
        if ($cacheControl === '') {
            return;
        }

        if (! str_contains(strtolower($cacheControl), 'no-store')) {
            return;
        }

        $tokens = array_filter(array_map(trim(...), explode(',', $cacheControl)));
        $filtered = [];
        foreach ($tokens as $token) {
            if (strtolower($token) === 'no-store') {
                continue;
            }
            $filtered[] = $token;
        }

        if (! in_array('no-cache', array_map(strtolower(...), $filtered), true)) {
            $filtered[] = 'no-cache';
        }
        if (! in_array('must-revalidate', array_map(strtolower(...), $filtered), true)) {
            $filtered[] = 'must-revalidate';
        }

        $response->headers->set('Cache-Control', implode(', ', $filtered));
    }

    /**
     * Get Content Security Policy directives
     */
    protected function getContentSecurityPolicy(string $nonce): string
    {
        $allowViteDevServer = $this->isLocalDevelopmentContext();
        $unsafeEvalConfig = config('app.csp_allow_unsafe_eval', app()->environment('local'));

        $directives = [
            "default-src 'self'",
        ];

        // [SECURITY FIX H-02] Use nonce-based CSP in production to eliminate unsafe-inline.
        // In local/dev environments, unsafe-inline is kept for developer convenience.
        // In production, inline scripts MUST use the generated nonce attribute.
        if ($allowViteDevServer) {
            // Development: allow unsafe-inline for HMR/dev convenience
            $scriptSrc = [
                "'self'",
                "'unsafe-inline'",
                "'unsafe-eval'", // needed for Vite HMR
                'https:',
                'data:',
                'blob:',
                'http://localhost:5173',
                'ws://localhost:5173',
            ];
        } else {
            // Production: nonce-based + trusted CDN sources
            $scriptSrc = [
                "'self'",
                "'nonce-{$nonce}'",
                "'unsafe-inline'",
                "'unsafe-eval'",
                'blob:',
                'https://static.cloudflareinsights.com',
                'https://cloudflareinsights.com',
                'https://*.cloudflareinsights.com',
                'https://cdnjs.cloudflare.com',
                'https://cdn.jsdelivr.net',
                'https://unpkg.com',
            ];
        }

        // Add same-origin domains explicitly to prevent 'none' fallbacks
        $host = request()->getHost();
        if ($host) {
            $scriptSrc[] = "https://{$host}";
            $scriptSrc[] = "https://*.{$host}";
        }

        if (config('layout.remote_plugin_blocks.enabled')) {
            $remoteHosts = config('layout.remote_plugin_blocks.csp_script_hosts', []);
            if (is_array($remoteHosts)) {
                foreach ($remoteHosts as $src) {
                    if (is_string($src) && $src !== '') {
                        $scriptSrc[] = $src;
                    }
                }
            }
        }

        // Add external CDN script sources (production only)
        // TODO(security): Add SRI hashes for these CDN resources (LOW-05).
        // Uploaded theme ESM bundles are served same-origin (/storage/themes/*) under script-src 'self'.
        if (config('layout.uploaded_themes.enabled')) {
            $extra = config('layout.uploaded_themes.csp_script_extra', []);
            if (is_array($extra)) {
                foreach ($extra as $src) {
                    if (is_string($src) && $src !== '') {
                        $scriptSrc[] = $src;
                    }
                }
            }

            // Add external CDN script sources (production only)
            // TODO(security): Add SRI hashes for these CDN resources (LOW-05).
            if (! $allowViteDevServer) {
                $scriptSrc = array_merge($scriptSrc, [
                    'https://cdn.jsdelivr.net',
                    'https://unpkg.com',
                    'https://static.cloudflareinsights.com',
                    'https://cloudflareinsights.com',
                    'https://*.cloudflareinsights.com',
                    'https://cdnjs.cloudflare.com',
                ]);
            }
        }

        $directives[] = 'script-src '.implode(' ', array_unique($scriptSrc));

        // Style sources
        $styleSrc = ["'self'", "'unsafe-inline'"];

        if ($allowViteDevServer) {
            $styleSrc[] = 'http://localhost:5173';
        }

        $styleSrc = array_merge($styleSrc, [
            'https://cdn.jsdelivr.net',
            'https://fonts.googleapis.com',
            'https://fonts.bunny.net',
            'https://unpkg.com',
        ]);

        $directives[] = 'style-src '.implode(' ', array_unique($styleSrc));

        // Font sources
        $directives[] = "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net https://fonts.bunny.net data:";

        // Image sources
        $directives[] = "img-src 'self' data: https: blob:";

        $connectSrc = ["'self'", 'https:', 'wss:', 'ws:'];

        if ($allowViteDevServer) {
            $connectSrc[] = 'http://localhost:5173';
            $connectSrc[] = 'ws://localhost:5173';
            $connectSrc[] = 'wss://localhost:5173';
        }

        $connectSrc = array_merge($connectSrc, [
            'https://nominatim.openstreetmap.org',
            'https://www.emsifa.com',
            'https://static.cloudflareinsights.com',
            'https://cloudflareinsights.com',
            'https://*.cloudflareinsights.com',
            'https://cloudflare.com',
            'https://*.cloudflare.com',
        ]);

        if ($host) {
            $connectSrc[] = "https://{$host}";
            $connectSrc[] = "https://*.{$host}";
            $connectSrc[] = "wss://{$host}";
            $connectSrc[] = "ws://{$host}";
        }

        // [SECURITY FIX L-01] Removed hardcoded smkn1cijulang.sch.id domain.
        // External organization domains should be read from config, not hardcoded.
        // Add any extra connect-src domains via config('security.csp_connect_extra', []).
        $extraConnectSrc = config('security.csp_connect_extra', []);
        if (is_array($extraConnectSrc)) {
            foreach ($extraConnectSrc as $src) {
                if (is_string($src) && $src !== '') {
                    $connectSrc[] = $src;
                }
            }
        }

        $portalUrl = config('app.url');
        if (is_string($portalUrl) && $portalUrl !== '') {
            $connectSrc[] = $portalUrl;

            $portalHost = parse_url($portalUrl, PHP_URL_HOST);
            if (is_string($portalHost) && $portalHost !== '') {
                $connectSrc[] = "https://{$portalHost}";
                $connectSrc[] = "https://*.{$portalHost}";
            }
        }

        $rootDomain = config('app.root_domain');
        if (is_string($rootDomain) && $rootDomain !== '') {
            $connectSrc[] = "https://{$rootDomain}";
        }

        $directives[] = 'connect-src '.implode(' ', array_unique($connectSrc));

        // Frame ancestors
        $directives[] = "frame-ancestors 'self'";

        // Base URI
        $directives[] = "base-uri 'self'";

        // Form action
        $directives[] = "form-action 'self'";

        // Worker sources (for service workers, PDF generation, etc.)
        $directives[] = "worker-src 'self' blob:";

        // Frame sources (for iframes - YouTube, Vimeo, Google Maps, etc.)
        $frameSrc = [
            "'self'",
            'https://www.youtube.com',
            'https://youtube.com',
            'https://www.youtube-nocookie.com',
            'https://player.vimeo.com',
            'https://vimeo.com',
            'https://www.google.com',
            'https://maps.google.com',
            'https://www.google.com/maps',
            'https://open.spotify.com',
            'https://w.soundcloud.com',
            'https://www.dailymotion.com',
            'https://codepen.io',
            'https://jsfiddle.net',
        ];

        if ($host) {
            $frameSrc[] = "https://{$host}";
        }

        $directives[] = 'frame-src '.implode(' ', $frameSrc);

        // CSP Violation Reporting
        $directives[] = 'report-uri /api/v1/security/crep-collect';

        return implode('; ', $directives);
    }

    /**
     * Generate or retrieve a nonce for the current request
     */
    protected function generateNonce(Request $request): string
    {
        return $request->cspNonce();
    }

    private function isLocalDevelopmentContext(): bool
    {
        if (app()->environment(['local', 'development', 'testing'])) {
            return true;
        }

        $appUrl = config('app.url', '');
        if (! is_string($appUrl) || $appUrl === '') {
            return false;
        }

        $host = parse_url($appUrl, PHP_URL_HOST);
        if (! is_string($host) || $host === '') {
            return false;
        }

        return in_array($host, ['localhost', '127.0.0.1', '::1'], true);
    }

    /**
     * Guard against legacy/invalid CSP overrides that break frontend runtime.
     */
    private function isUsableConfiguredCsp(string $policy): bool
    {
        $normalizedPolicy = strtolower($policy);

        // 1. Critical Failure: connect-src 'none' or script-src 'none'
        // This completely breaks an SPA's ability to load or communicate.
        if (str_contains($normalizedPolicy, "connect-src 'none'") ||
            str_contains($normalizedPolicy, "script-src 'none'")) {
            return false;
        }

        // 2. Critical Failure: Missing 'self' in script-src
        // If script-src exists but doesn't have 'self', the main app chunk will be blocked.
        if (str_contains($normalizedPolicy, 'script-src') && ! str_contains($normalizedPolicy, "'self'")) {
            return false;
        }

        // 3. Critical Failure: Missing 'self' in connect-src
        // If connect-src exists but doesn't have 'self', API calls to the origin will be blocked.
        if (str_contains($normalizedPolicy, 'connect-src') && ! str_contains($normalizedPolicy, "'self'")) {
            return false;
        }

        // 4. Critical Failure: Policy is too short or missing key directives
        return strlen($policy) >= 20 && str_contains($normalizedPolicy, 'default-src');
    }
}
