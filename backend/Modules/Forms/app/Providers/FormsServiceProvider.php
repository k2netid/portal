<?php

declare(strict_types=1);

namespace Modules\Forms\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Modules\Core\System\Facades\Hook;
use Modules\Core\System\Models\Extension;
use Modules\Core\System\Models\Permission;
use Modules\Forms\Database\Seeders\ContactFormSeeder;
use Modules\Forms\Database\Seeders\FormsPermissionSeeder;

class FormsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $moduleRoot = dirname(__DIR__, 2);

        $this->loadMigrationsFrom($moduleRoot.'/database/migrations');

        Route::middleware('api')
            ->prefix('api')
            ->group($moduleRoot.'/routes/api.php');

        Hook::listen('extension_activated', function (Extension $extension): void {
            if ($extension->slug !== 'forms') {
                return;
            }
            self::seedPermissions();
            ContactFormSeeder::ensure();
        });

        $this->ensurePermissionsIfMissing();
        $this->ensureContactFormIfPackActive();
    }

    public static function seedPermissions(): void
    {
        (new FormsPermissionSeeder)->run();
    }

    protected function ensurePermissionsIfMissing(): void
    {
        try {
            if (! Schema::hasTable('permissions')) {
                return;
            }
            if (! Extension::query()->where('slug', 'forms')->where('status', 'active')->exists()) {
                return;
            }

            $missing = ! Permission::query()
                ->where('name', 'view forms')
                ->where('guard_name', 'web')
                ->exists();

            if ($missing) {
                self::seedPermissions();
            }
        } catch (\Throwable) {
            // Avoid boot failure if permissions tables are mid-migrate.
        }
    }

    protected function ensureContactFormIfPackActive(): void
    {
        try {
            if (! Extension::query()->where('slug', 'forms')->where('status', 'active')->exists()) {
                return;
            }
            ContactFormSeeder::ensure();
        } catch (\Throwable) {
            // Skip while forms tables are mid-migrate.
        }
    }
}
