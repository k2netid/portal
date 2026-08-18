<?php

declare(strict_types=1);

namespace Modules\Core\System\Traits;

use Illuminate\Http\Request;

/**
 * Trait to share maintenance mode bypass logic across multiple middlewares.
 * Used by CheckMaintenanceMode, WafMiddleware, and VerifyConnection.
 */
trait MaintenanceBypass
{
    /**
     * Determine if the request should bypass security/maintenance blocks.
     */
    protected function shouldBypassMaintenance(Request $request): bool
    {
        $bypassPaths = [
            'api/v1/test-connectivity',
            'admin*',
            'api/admin*',
            'api/v1/admin*',
            'api/v1/dashboard*',     // Allow dashboard requests
            'api/v1/public*',        // Existing
            'api/v1/ja*',            // Broaden to all ja/ (menus, contents, languages, themes)
            'api/v1/publishing*',           // Allow Publishing specific public data
            'api/v1/analytics*',     // Existing
            'api/v1/captcha*',
            'api/v1/login*',
            'api/v1/logout*',
            'api/v1/profile*',       // User profile
            'api/v1/journal*',       // Frontend logging
            'api/v1/system*',        // Health checks
            'api/v1/user*',          // Critical for SPA auth check to distinguish admin vs guest
            'sanctum/csrf-cookie',
            'up',
        ];

        foreach ($bypassPaths as $pattern) {
            if ($request->is($pattern)) {
                return true;
            }

            // If pattern doesn't start with api/, try matching with api/v1/ prefix
            if (! str_starts_with($pattern, 'api/') && $request->is('api/v1/'.$pattern)) {
                return true;
            }
        }

        return false;
    }
}
