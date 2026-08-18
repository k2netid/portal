<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\Core\Security\Models\SecurityLog;
use Modules\Core\System\Models\Setting;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class SpaController extends Controller
{
    /**
     * Serve Public SPA root.
     */
    public function index(): Response
    {
        if (file_exists(public_path('index.html'))) {
            return response(file_get_contents(public_path('index.html')))->header('Content-Type', 'text/html');
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'Jejakawan CMS is running',
        ]);
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
     * SPA Fallback handler.
     */
    public function fallback(Request $request): Response
    {
        $path = $request->path();
        $segments = explode('/', $path);
        $firstSegment = $segments[0] ?? '';

        $consoleSlugs = ['dash', 'ja-dash'];
        try {
            $consoleSlugs[] = Setting::resolveConsoleDashboardSlug();
        } catch (Throwable) {
            // Fallback
        }
        $consoleSlugs = array_values(array_unique(array_filter($consoleSlugs)));

        if (
            in_array(strtolower($firstSegment), array_map('strtolower', $consoleSlugs), true)
            || in_array(strtolower($firstSegment), ['auth', 'login', 'register'], true)
        ) {
            if (file_exists(public_path('console.html'))) {
                return response(file_get_contents(public_path('console.html')))->header('Content-Type', 'text/html');
            }
        }

        if (file_exists(public_path('index.html'))) {
            return response(file_get_contents(public_path('index.html')))->header('Content-Type', 'text/html');
        }

        return response()->json(['message' => 'Resource not found'], 404);
    }
}
