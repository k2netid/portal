<?php

namespace Modules\Content\Layout\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Content\Layout\Models\Redirect;
use Symfony\Component\HttpFoundation\Response;

class ApplyRedirects
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip API routes and critical system files
        if ($request->is('api/*') ||
            $request->is('build/*') ||
            $request->is('storage/*') ||
            $request->is('sitemap.xml*') ||
            $request->is('robots.txt')) {
            return $next($request);
        }

        // Find rewrite for this path
        $path = $request->path();

        $redirect = Redirect::where('source_path', $path)
            ->where('is_active', true)
            ->first();

        if ($redirect) {
            $redirect->increment('hits');
            $redirect->update(['last_hit_at' => now()]);

            return redirect()->to($redirect->target_path, $redirect->status_code);
        }

        return $next($request);
    }
}
