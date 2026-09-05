<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Support\SpaHtmlFavicon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\Core\Security\Models\SecurityLog;
use Modules\Core\System\Models\Extension;
use Modules\Core\System\Models\Setting;
use Symfony\Component\HttpFoundation\Response;

class SpaController extends Controller
{
    /**
     * Apex `/`:
     * - Site on  → public theme SPA (overrides kernel landing)
     * - Site off → kernel landing (console login stays at /auth/console-*)
     */
    public function index(): Response
    {
        if ($this->siteRuntimeActive()) {
            return $this->servePublicSpa();
        }

        return $this->serveLandingSpa();
    }

    /**
     * Legacy `/site/*` → apex public paths when Site is on; 404 when off.
     */
    public function legacySiteRedirect(Request $request, ?string $any = null): Response
    {
        if (! $this->siteRuntimeActive()) {
            abort(404);
        }

        $rest = trim((string) $any, '/');
        $target = $rest === '' ? '/' : '/'.$rest;
        $query = $request->getQueryString();

        return redirect($query ? $target.'?'.$query : $target, 301);
    }

    /**
     * Cloudflare RUM dummy endpoint.
     */
    public function cdnCgi(): Response
    {
        Log::info('Cloudflare RUM hit: '.request()->path());

        return response()->noContent();
    }

    /**
     * Probe Path Sinkhole.
     */
    public function probe(Request $request): Response
    {
        $path = (string) $request->route('path', '');
        $ip = (string) $request->ip();
        $cacheKey = 'security:probe:'.sha1($ip.'|'.$path);

        if (! Cache::has($cacheKey)) {
            SecurityLog::log(
                'path_probe_detected',
                null,
                $ip,
                'Common probe path requested',
                [
                    'path' => $path,
                    'method' => $request->method(),
                    'host' => $request->getHost(),
                ]
            );
            Cache::put($cacheKey, true, now()->addMinutes(5));
        }

        Log::warning('Probe path sinkhole triggered', [
            'path' => $path,
            'ip' => $ip,
            'method' => $request->method(),
        ]);

        if ($request->expectsJson() || $request->is('api/*')) {
            abort(404);
        }

        return redirect('/404');
    }

    /**
     * SPA Fallback: console prefixes stay on console; public owns the rest when Site is on;
     * otherwise kernel landing (not console login).
     */
    public function fallback(Request $request): Response
    {
        if ($this->isConsoleSpaPath($request->path())) {
            return $this->serveConsoleSpa();
        }

        if ($this->siteRuntimeActive()) {
            return $this->servePublicSpa();
        }

        return $this->serveLandingSpa();
    }

    protected function siteRuntimeActive(): bool
    {
        return Extension::isProductActive('site');
    }

    protected function isConsoleSpaPath(string $path): bool
    {
        $path = trim($path, '/');
        if ($path === '') {
            return false;
        }

        $first = strtolower(explode('/', $path)[0] ?? '');
        $slug = strtolower(Setting::resolveConsoleDashboardSlug());

        $reserved = array_values(array_unique(array_filter([
            'auth',
            'install',
            'maintenance',
            'dash',
            'ja-dash',
            $slug,
        ])));

        return in_array($first, $reserved, true);
    }

    protected function serveConsoleSpa(): Response
    {
        if (file_exists(public_path('index.html'))) {
            $content = file_get_contents(public_path('index.html'));
            if (is_string($content)) {
                $html = SpaHtmlFavicon::injectForShell($content, 'console');
                $html = SpaHtmlFavicon::injectNonce($html, request()->cspNonce());

                return response($html)
                    ->header('Content-Type', 'text/html');
            }
        }

        return response()->json([
            'status' => 'ok',
            'shell' => 'console',
            'message' => 'Jejakawan Core Engine is running',
        ]);
    }

    /**
     * Kernel welcome surface when no public CMS theme is product-active.
     * Console login remains at /auth/console-sign-in (not apex).
     */
    protected function serveLandingSpa(): Response
    {
        foreach (['landing.html', 'landing/index.html'] as $file) {
            $path = public_path($file);
            if (is_file($path)) {
                $content = file_get_contents($path);
                if (is_string($content)) {
                    $html = SpaHtmlFavicon::injectForShell($content, 'landing');
                    $html = SpaHtmlFavicon::injectNonce($html, request()->cspNonce());

                    return response($html)
                        ->header('Content-Type', 'text/html');
                }
            }
        }

        // Dev / pre-build fallback — still not the console login form.
        $nonce = htmlspecialchars((string) request()->cspNonce(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        return response(
            '<!DOCTYPE html><html lang="id"><head><meta charset="utf-8">'
            .'<meta name="viewport" content="width=device-width,initial-scale=1">'
            .'<title>Jejakawan</title>'
            .'<script nonce="'.$nonce.'">window.__JA_SHELL__=\'landing\';</script>'
            .'</head><body style="font-family:system-ui,sans-serif;max-width:40rem;margin:4rem auto;padding:0 1.25rem;line-height:1.5">'
            .'<p style="letter-spacing:.04em;text-transform:uppercase;font-size:.75rem;color:#0f766e">Core Engine</p>'
            .'<h1>Siap dijalankan.</h1>'
            .'<p>Situs publik belum aktif. Aktifkan pack <strong>Site</strong> untuk menampilkan theme CMS di apex <code>/</code>.</p>'
            .'<p><a href="/auth/console-sign-in">Buka console</a></p>'
            .'</body></html>',
            200,
            ['Content-Type' => 'text/html; charset=UTF-8']
        );
    }

    protected function servePublicSpa(): Response
    {
        foreach (['public.html', 'public/index.html'] as $file) {
            $path = public_path($file);
            if (is_file($path)) {
                $content = file_get_contents($path);
                if (is_string($content)) {
                    $html = SpaHtmlFavicon::injectForShell($content, 'public');
                    $html = SpaHtmlFavicon::injectNonce($html, request()->cspNonce());

                    return response($html)
                        ->header('Content-Type', 'text/html');
                }
            }
        }

        return response()->json([
            'status' => 'ok',
            'shell' => 'public',
            'message' => 'Public theme runtime — build frontend public.html',
        ]);
    }
}
