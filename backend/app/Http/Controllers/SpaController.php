<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\Core\Security\Models\SecurityLog;
use Symfony\Component\HttpFoundation\Response;

class SpaController extends Controller
{
    /**
     * Serve SPA root.
     */
    public function index(): Response
    {
        if (file_exists(public_path('index.html'))) {
            $content = file_get_contents(public_path('index.html'));
            if (is_string($content)) {
                return response($content)->header('Content-Type', 'text/html');
            }
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'Jejakawan Core Engine is running',
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
        if (file_exists(public_path('index.html'))) {
            $content = file_get_contents(public_path('index.html'));
            if (is_string($content)) {
                return response($content)->header('Content-Type', 'text/html');
            }
        }

        return response()->json(['message' => 'Resource not found'], 404);
    }
}
