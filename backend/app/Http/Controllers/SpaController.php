<?php

declare(strict_types=1);

namespace App\Http\Controllers;

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
     * Apex `/`: console login when Site pack is off; public web when Site is product-active.
     */
    public function index(): Response
    {
        if ($this->siteRuntimeActive()) {
            return $this->servePublicSpa();
        }

        return $this->serveConsoleSpa();
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
     * SPA Fallback: console prefixes stay on console; public owns the rest when Site is on.
     */
    public function fallback(Request $request): Response
    {
        if ($this->isConsoleSpaPath($request->path())) {
            return $this->serveConsoleSpa();
        }

        if ($this->siteRuntimeActive()) {
            return $this->servePublicSpa();
        }

        return $this->serveConsoleSpa();
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
                return response($content)->header('Content-Type', 'text/html');
            }
        }

        return response()->json([
            'status' => 'ok',
            'shell' => 'console',
            'message' => 'Jejakawan Core Engine is running',
        ]);
    }

    protected function servePublicSpa(): Response
    {
        foreach (['public.html', 'public/index.html'] as $file) {
            $path = public_path($file);
            if (is_file($path)) {
                $content = file_get_contents($path);
                if (is_string($content)) {
                    return response($content)->header('Content-Type', 'text/html');
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
