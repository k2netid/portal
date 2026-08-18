<?php

declare(strict_types=1);

namespace Modules\Core\System\Services;

use Illuminate\Support\Facades\Session;
use Modules\Core\System\Models\User;

class SessionManager
{
    /**
     * Set session lifetime based on user role
     *
     * @param  User|null  $user
     */
    public static function setLifetimeForUser($user): void
    {
        if (! $user instanceof User) {
            return;
        }

        // Determine lifetime based on role hierarchy
        $lifetime = $user->isAtLeastRole('admin')
            ? (is_scalar($vAdmin = config('session.admin_lifetime', 120)) ? (int) $vAdmin : 120)  // 2 hours for admins
            : (is_scalar($vUser = config('session.user_lifetime', 480)) ? (int) $vUser : 480);  // 8 hours for regular users

        // Set session lifetime dynamically
        config(['session.lifetime' => $lifetime]);

        // Also set cookie lifetime to match
        Session::put('session_lifetime', $lifetime);
    }

    /**
     * Get current session lifetime
     */
    public static function getLifetime(): int
    {
        $defaultLifetime = is_scalar($vDef = config('session.lifetime')) ? (int) $vDef : 120;
        $sessionLifetime = Session::get('session_lifetime', $defaultLifetime);

        return is_scalar($sessionLifetime) ? (int) $sessionLifetime : $defaultLifetime;
    }
}
