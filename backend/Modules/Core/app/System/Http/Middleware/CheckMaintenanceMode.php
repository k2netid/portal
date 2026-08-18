<?php

declare(strict_types=1);

namespace Modules\Core\System\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Modules\Core\System\Models\Setting;
use Modules\Core\System\Models\User;
use Modules\Core\System\Traits\MaintenanceBypass;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    use MaintenanceBypass;

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Check if maintenance mode is enabled
        $maintenanceEnabled = filter_var(Setting::get('maintenance_mode', false), FILTER_VALIDATE_BOOLEAN);

        if (! $maintenanceEnabled) {
            return $next($request);
        }

        // 2. Check for scheduling (End Time)
        $endTimeRaw = Setting::get('maintenance_end_time');
        $endTime = is_string($endTimeRaw) ? $endTimeRaw : null;

        if ($endTime) {
            try {
                $endDateTime = Carbon::parse($endTime);
                if ($endDateTime->isPast()) {
                    Setting::set('maintenance_mode', false, 'boolean', 'general');

                    return $next($request);
                }
            } catch (\Exception) {
            }
        }

        // 3. Allow access to specific bypass routes
        if ($this->shouldBypassMaintenance($request)) {
            return $next($request);
        }

        // 4. User Bypass: If logged in as admin in ANY guard (Sanctum or Web)
        if ($this->isAuthorizedAdmin()) {
            return $next($request);
        }

        // 5. Return JSON response
        return response()->json([
            'success' => false,
            'message' => Setting::get('maintenance_message', 'Under Maintenance'),
            'maintenance' => true,
        ], 503);
    }

    /**
     * Check if the current user is an authorized admin using role ranks.
     */
    protected function isAuthorizedAdmin(): bool
    {
        try {
            foreach (['sanctum', 'web'] as $guard) {
                $user = Auth::guard($guard)->user();
                // Bypass maintenance if role rank is 90 or higher (System Admins)
                if ($user instanceof User && $user->getRoleRank() >= 90) {
                    return true;
                }
            }
        } catch (\Exception) {
        }

        return false;
    }
}
