<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckIfInstalled
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $isInstalled = config('app.installed', false);
        $isInstallRoute = $request->is('api/v1/install*') || $request->is('install*');

        if (! $isInstalled && ! $isInstallRoute) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Application not installed.',
                    'action' => 'install_required',
                    'install_url' => url('/install'),
                ], 426); // 426 Upgrade Required is a good semantic fit
            }

            return redirect('/install');
        }

        // If installed but trying to access install route
        if ($isInstalled && $isInstallRoute) {
            // Allow status check and post-reset setup even if installed
            if ($request->is('api/v1/install/status') || $request->is('api/v1/install/setup-admin')) {
                return $next($request);
            }

            return $request->expectsJson()
                ? response()->json(['message' => 'Already installed.'], 403)
                : redirect('/');
        }

        return $next($request);
    }
}
