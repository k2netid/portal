<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Modules\Core\Security\Models\SecurityLog;
use Modules\Core\System\Models\Setting;
use Symfony\Component\HttpFoundation\Response;

Route::get('/', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'Jejakawan Backend API is running',
    ]);
});

// Cloudflare RUM (Real User Monitoring) dummy route to prevent 404s on origin
Route::any('/cdn-cgi/{path?}', function () {
    Log::info('Cloudflare RUM hit: '.request()->path());

    return response()->noContent();
})->where('path', '.*');

/*
|--------------------------------------------------------------------------
| Probe Path Sinkhole
|--------------------------------------------------------------------------
|
| Common reconnaissance URLs should always look like plain 404s.
| We also rate-limit and security-log these hits to support incident response.
|
*/
$probePaths = [
    'admin',
    'admin/*',
    'dashboard',
    'dashboard/*',
    'panel',
    'panel/*',
    'wp-admin',
    'wp-admin/*',
    'wp-login.php',
    'phpmyadmin',
    'phpmyadmin/*',
    'pma',
    'cpanel',
    'administrator',
    'administrator/*',
    'manager',
    'manage',
    'system',
    'system/*',
];

Route::middleware('throttle:probe-paths')->any('/{path}', function (Request $request): Response {
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

    // Browser: unified SPA 404 (NotFound.vue). API/JSON: plain 404 (no HTML shell leak).
    if ($request->expectsJson() || $request->is('api/*')) {
        abort(404);
    }

    return redirect('/404');
})->whereIn('path', $probePaths);

Route::fallback(function () {
    $path = request()->path();
    $segments = explode('/', $path);
    $firstSegment = $segments[0] ?? '';

    $consoleSlugs = ['dash', 'ja-dash'];
    try {
        $consoleSlugs[] = Setting::resolveConsoleDashboardSlug();
    } catch (Throwable $e) {
        // Fallback
    }
    $consoleSlugs = array_values(array_unique(array_filter($consoleSlugs)));

    if (in_array(strtolower($firstSegment), array_map('strtolower', $consoleSlugs), true) || strtolower($firstSegment) === 'auth') {
        if (file_exists(public_path('console.html'))) {
            return file_get_contents(public_path('console.html'));
        }
    }

    if (file_exists(public_path('index.html'))) {
        return file_get_contents(public_path('index.html'));
    }

    return response()->json(['message' => 'Resource not found'], 404);
});
