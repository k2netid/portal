<?php

declare(strict_types=1);

namespace Modules\Analytics\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Modules\Analytics\Console\Commands\CleanupAnalytics;
use Modules\Analytics\Console\Commands\CleanupSlowQueryLogs;
use Modules\Analytics\Database\Seeders\AnalyticsPermissionSeeder;
use Modules\Core\System\Facades\Hook;
use Modules\Core\System\Models\Extension;
use Modules\Core\System\Models\Permission;

class AnalyticsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $moduleRoot = dirname(__DIR__, 2);

        $this->loadMigrationsFrom($moduleRoot.'/database/migrations');

        Route::middleware('api')
            ->prefix('api')
            ->group($moduleRoot.'/routes/api.php');

        if ($this->app->runningInConsole()) {
            $this->commands([
                CleanupAnalytics::class,
                CleanupSlowQueryLogs::class,
            ]);
        }

        Hook::listen('extension_activated', function (Extension $extension): void {
            if ($extension->slug !== 'analytics') {
                return;
            }
            self::seedPermissions();
        });

        $this->ensurePermissionsIfMissing();
    }

    public static function seedPermissions(): void
    {
        (new AnalyticsPermissionSeeder)->run();
    }

    protected function ensurePermissionsIfMissing(): void
    {
        try {
            if (! Schema::hasTable('permissions')) {
                return;
            }

            $missing = ! Permission::query()
                ->where('name', 'view analytics')
                ->where('guard_name', 'web')
                ->exists();

            if ($missing) {
                self::seedPermissions();
            }
        } catch (\Throwable) {
            // Avoid boot failure if permissions tables are mid-migrate.
        }
    }
}
