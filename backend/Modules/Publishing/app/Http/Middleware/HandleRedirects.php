<?php

namespace Modules\Publishing\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpFoundation\Response;

class HandleRedirects
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('api/*') ||
            $request->is('build/*') ||
            $request->is('storage/*') ||
            $request->is('sitemap.xml*') ||
            $request->is('robots.txt')) {
            return $next($request);
        }

        if (! class_exists(\Modules\Layout\Models\Redirect::class)) {
            return $next($request);
        }

        $path = $request->path();

        $redirect = \Modules\Layout\Models\Redirect::where('source_path', $path)
            ->where('is_active', true)
            ->first();

        if ($redirect) {
            $redirect->increment('hits');
            $redirect->update(['last_hit_at' => now()]);

            return Redirect::to($redirect->target_path, $redirect->status_code);
        }

        return $next($request);
    }
}
