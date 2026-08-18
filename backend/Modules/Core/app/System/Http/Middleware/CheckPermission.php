<?php

namespace Modules\Core\System\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Core\Security\Models\SecurityLog;
use Modules\Core\System\Helpers\IpHelper;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (! $request->user()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $permissions = explode('|', $permission);
        foreach ($permissions as $perm) {
            if ($request->user()->can($perm)) {
                return $next($request);
            }
        }

        // Audit denied access attempts for anomaly detection and incident response.
        SecurityLog::log(
            'permission_denied',
            $request->user(),
            IpHelper::getClientIp($request),
            'Permission denied on protected route',
            [
                'required_permissions' => $permissions,
                'method' => $request->method(),
                'path' => $request->path(),
            ]
        );

        return response()->json(['message' => 'Unauthorized'], 403);
    }
}
