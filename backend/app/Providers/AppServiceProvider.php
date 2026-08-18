<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Modules\Core\System\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $forceHttps = config('features.force_https_urls');
        if ($forceHttps === null) {
            $forceHttps = ! app()->isLocal();
        } else {
            $forceHttps = filter_var($forceHttps, FILTER_VALIDATE_BOOLEAN);
        }

        if ($forceHttps) {
            URL::forceScheme('https');
        }

        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(5000)->by($request->user()?->id ?: $request->ip()));

        /**
         * Login: shared NAT (offices) would hit a plain per-IP cap quickly.
         * Keep a high per-IP ceiling and a tighter per-email+IP bucket for credential stuffing.
         */
        RateLimiter::for('login', function (Request $request): array {
            $limits = [
                // Shared public IPv4 on customer networks — keep generous; brute-force still handled in AuthController / SecurityService
                Limit::perMinute(400)->by('login-ip|'.$request->ip()),
            ];

            $emailRaw = $request->input('email');
            if (is_string($emailRaw)) {
                $normalized = strtolower(trim($emailRaw));
                if ($normalized !== '') {
                    $limits[] = Limit::perMinutes(5, 60)->by('login-email|'.sha1($normalized).'|'.$request->ip());
                }
            }

            return $limits;
        });

        /** Authenticated CMS media: one POST per file — default 30/min is too low for gallery batches. */
        RateLimiter::for('media-upload', function (Request $request) {
            $authId = $request->user()?->getAuthIdentifier();
            $uid = is_scalar($authId) ? (string) $authId : null;

            return Limit::perMinute(240)->by('media-upload|'.($uid !== null ? 'u:'.$uid : 'ip:'.$request->ip()));
        });

        RateLimiter::for('media-upload-multiple', function (Request $request) {
            $authId = $request->user()?->getAuthIdentifier();
            $uid = is_scalar($authId) ? (string) $authId : null;

            return Limit::perMinute(60)->by('media-upload-multi|'.($uid !== null ? 'u:'.$uid : 'ip:'.$request->ip()));
        });

        /** Whole admin CMS JSON surface (SPA + uploads); per authenticated user to avoid one IP starving many staff. */
        RateLimiter::for('manage-publishing', function (Request $request) {
            $authId = $request->user()?->getAuthIdentifier();
            $uid = is_scalar($authId) ? (string) $authId : null;

            return Limit::perMinute(900)->by('manage-publishing|'.($uid !== null ? 'u:'.$uid : 'ip:'.$request->ip()));
        });

        /** 2FA code step: same shared-IP issue as login when many staff authenticate. */
        RateLimiter::for('two-factor-verify', fn (Request $request) => Limit::perMinute(60)->by('2fa-verify|'.$request->ip()));

        RateLimiter::for('admin', fn (Request $request) => Limit::perMinute(2000)->by($request->user()?->id ?: $request->ip()));

        RateLimiter::for('probe-paths', function (Request $request) {
            $fingerprint = $request->ip().'|'.substr((string) $request->userAgent(), 0, 120);

            return Limit::perMinute(20)->by($fingerprint);
        });

        /**
         * Public live-search: frontend usually calls suggestions + full search on each debounced keystroke.
         * Use a per-IP+UA bucket so shared public IPs are less likely to starve each other.
         */
        RateLimiter::for('search-public', function (Request $request): array {
            $fingerprint = 'search-public|'.$request->ip().'|'.substr(sha1((string) $request->userAgent()), 0, 16);

            return [
                // Short burst allowance for fast typing.
                Limit::perMinute(180)->by($fingerprint),
                // Sustained protection window.
                Limit::perMinutes(10, 1200)->by($fingerprint),
            ];
        });

        RateLimiter::for('search-suggestions', function (Request $request): array {
            $fingerprint = 'search-suggest|'.$request->ip().'|'.substr(sha1((string) $request->userAgent()), 0, 16);

            return [
                Limit::perMinute(240)->by($fingerprint),
                Limit::perMinutes(10, 1500)->by($fingerprint),
            ];
        });

        /**
         * Public analytics visit tracking from SPA route changes.
         * Count by IP+UA to avoid false 429 on shared public networks.
         */
        RateLimiter::for('analytics-visit', function (Request $request): array {
            $fingerprint = 'analytics-visit|'.$request->ip().'|'.substr(sha1((string) $request->userAgent()), 0, 16);

            return [
                Limit::perMinute(300)->by($fingerprint),
                Limit::perMinutes(10, 1800)->by($fingerprint),
            ];
        });

        /**
         * Public form definition fetch (e.g. contact page mount/re-mount).
         */
        RateLimiter::for('forms-public', function (Request $request): array {
            $fingerprint = 'forms-public|'.$request->ip().'|'.substr(sha1((string) $request->userAgent()), 0, 16);

            return [
                Limit::perMinute(180)->by($fingerprint),
                Limit::perMinutes(10, 1000)->by($fingerprint),
            ];
        });

        /**
         * Public form engagement tracking (view/start events from frontend forms).
         */
        RateLimiter::for('forms-track', function (Request $request): array {
            // Reduce false 429s for legitimate users behind shared IP/proxy.
            $clientIp = (string) ($request->header('CF-Connecting-IP') ?: $request->ip());
            $shieldCookie = $request->cookie('shield_trust');
            $csrfCookie = $request->cookie('XSRF-TOKEN');
            $sessionHint = is_string($shieldCookie) && $shieldCookie !== ''
                ? $shieldCookie
                : (is_string($csrfCookie) ? $csrfCookie : '');
            $formRouteParam = $request->route('form');
            $formSlug = is_object($formRouteParam) && isset($formRouteParam->slug) && is_scalar($formRouteParam->slug)
                ? (string) $formRouteParam->slug
                : '';
            $fingerprint = 'forms-track|'.$clientIp.'|'.substr(
                sha1($request->userAgent().'|'.$sessionHint.'|'.$formSlug),
                0,
                24
            );

            return [
                Limit::perMinute(360)->by($fingerprint),
                Limit::perMinutes(10, 3000)->by($fingerprint),
            ];
        });

        /**
         * Admin journal clear actions (system/access/activity) can be retried rapidly from UI.
         * Use a generous per-user/per-IP limiter to prevent false 429 while preserving abuse protection.
         */
        RateLimiter::for('admin-journal-clear', function (Request $request): array {
            $authId = $request->user()?->getAuthIdentifier();
            $uid = is_scalar($authId) ? (string) $authId : null;
            $key = $uid !== null ? 'u:'.$uid : 'ip:'.$request->ip();

            return [
                Limit::perMinute(180)->by('admin-journal-clear|'.$key),
                Limit::perMinutes(10, 800)->by('admin-journal-clear-10m|'.$key),
            ];
        });

        // Backward compatibility for any route still referencing old key.
        RateLimiter::for('system-journal-clear', function (Request $request): array {
            $authId = $request->user()?->getAuthIdentifier();
            $uid = is_scalar($authId) ? (string) $authId : null;
            $key = $uid !== null ? 'u:'.$uid : 'ip:'.$request->ip();

            return [
                Limit::perMinute(180)->by('admin-journal-clear|'.$key),
                Limit::perMinutes(10, 800)->by('admin-journal-clear-10m|'.$key),
            ];
        });

        Gate::define('viewApiDocs', fn (User $user) => $user->isAtLeastRole('admin'));
    }
}
