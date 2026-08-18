<?php

namespace Modules\Core\System\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CacheResponse
{
    public function handle(Request $request, Closure $next, int $minutes = 60): Response
    {
        // Only cache GET requests
        if ($request->method() !== 'GET') {
            return $next($request);
        }

        // Skip caching for authenticated users
        if ($request->user()) {
            return $next($request);
        }

        // Skip API routes
        if ($request->is('api/*')) {
            return $next($request);
        }

        $key = 'response_cache_'.md5($request->fullUrl());

        return Cache::remember($key, now()->addMinutes($minutes), fn () => $next($request));
    }
}
