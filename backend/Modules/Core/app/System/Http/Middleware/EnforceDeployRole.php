<?php

declare(strict_types=1);

namespace Modules\Core\System\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Core\System\Models\Setting;
use Symfony\Component\HttpFoundation\Response;

/**
 * Deploy plane guard for split production hosts.
 *
 * - unified: single host (local / pilot) — all routes
 * - ops: jejakawan.com control plane — platform admin, marketing Jejakawan, Jejakawan, license
 * - organization: separate product deploy only (ja-organization) — not used on this hub repo
 */
class EnforceDeployRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $role = $this->deployRole();

        if ($role === 'unified' || $role === 'ops') {
            return $next($request);
        }

        $path = trim($request->path(), '/');

        if ($role === 'organization' && $this->isOpsPlanePath($path)) {
            return $this->deny($request, 'This endpoint is only available on the operations plane (APP_ROLE=ops).');
        }

        if ($role === 'organization' && ! $request->is('api/*') && $this->isTenantWebSpaPath($path)) {
            return $this->deny($request, 'Public organization site is disabled on the operations plane.');
        }

        return $next($request);
    }

    private function deployRole(): string
    {
        $role = config('deploy.role', 'unified');

        return is_string($role) ? strtolower(trim($role)) : 'unified';
    }

    private function isOpsPlanePath(string $path): bool
    {
        $prefixes = [
            'api/v1/manage/platform',
            'api/v1/platform/webhooks',
        ];

        foreach ($prefixes as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix.'/')) {
                return true;
            }
        }

        return $path === 'api/v1/platform';
    }

    private function isTenantWebSpaPath(string $path): bool
    {
        if ($path === '' || $path === '/') {
            return true;
        }

        $allowed = ['install', 'maintenance', 'up'];

        foreach ($allowed as $segment) {
            if ($path === $segment || str_starts_with($path, $segment.'/')) {
                return false;
            }
        }

        try {
            $consoleSlug = Setting::resolveConsoleDashboardSlug();
        } catch (\Throwable) {
            $consoleSlug = 'dash';
        }

        if ($path === $consoleSlug || str_starts_with($path, $consoleSlug.'/')) {
            return false;
        }

        if ($path === 'auth' || str_starts_with($path, 'auth/')) {
            return false;
        }

        if (str_starts_with($path, 'api/') || str_starts_with($path, 'sanctum/')) {
            return false;
        }

        if (str_starts_with($path, 'storage/')) {
            return false;
        }

        return true;
    }

    private function deny(Request $request, string $message): Response
    {
        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'code' => 'DEPLOY_ROLE_FORBIDDEN',
                'deploy_role' => $this->deployRole(),
            ], 403);
        }

        return response($message, 403)->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
