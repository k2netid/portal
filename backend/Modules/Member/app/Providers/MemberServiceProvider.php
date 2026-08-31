<?php

declare(strict_types=1);

namespace Modules\Member\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Modules\Core\System\Facades\Hook;
use Modules\Core\System\Models\Extension;
use Modules\Core\System\Models\Permission;
use Modules\Member\Database\Seeders\MemberPermissionSeeder;
use Modules\Member\Http\Middleware\EnsureMemberEmailVerified;
use Modules\Member\Services\SanctumMemberIdentityPort;
use Modules\Publishing\Contracts\MemberIdentityPort;

class MemberServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MemberIdentityPort::class, SanctumMemberIdentityPort::class);
    }

    public function boot(): void
    {
        $moduleRoot = dirname(__DIR__, 2);

        $this->loadMigrationsFrom($moduleRoot.'/database/migrations');

        Route::aliasMiddleware('member.verified', EnsureMemberEmailVerified::class);

        Route::middleware('api')
            ->prefix('api')
            ->group($moduleRoot.'/routes/api.php');

        Hook::listen('extension_activated', function (Extension $extension): void {
            if ($extension->slug !== 'member') {
                return;
            }
            MemberPermissionSeeder::ensure();
        });

        $this->ensurePermissionsIfMissing();
    }

    protected function ensurePermissionsIfMissing(): void
    {
        try {
            if (! Schema::hasTable('permissions')) {
                return;
            }
            if (! Extension::query()->where('slug', 'member')->where('status', 'active')->exists()) {
                return;
            }

            $missing = ! Permission::query()
                ->where('name', 'view members')
                ->where('guard_name', 'web')
                ->exists();

            if ($missing) {
                MemberPermissionSeeder::ensure();
            }
        } catch (\Throwable) {
            // Avoid boot failure if permissions tables are mid-migrate.
        }
    }
}
