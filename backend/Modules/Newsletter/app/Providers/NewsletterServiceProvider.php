<?php

declare(strict_types=1);

namespace Modules\Newsletter\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Modules\Core\System\Facades\Hook;
use Modules\Core\System\Models\Extension;
use Modules\Core\System\Models\Permission;
use Modules\Newsletter\Database\Seeders\NewsletterPermissionSeeder;
use Modules\Newsletter\Services\NewsletterSampleDataPortAdapter;
use Modules\Newsletter\Services\NewsletterSubscriberCountPortAdapter;
use Modules\Publishing\Contracts\NewsletterSampleDataPortInterface;
use Modules\Publishing\Contracts\NewsletterSubscriberCountPortInterface;

class NewsletterServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(NewsletterSubscriberCountPortInterface::class, NewsletterSubscriberCountPortAdapter::class);
        $this->app->singleton(NewsletterSampleDataPortInterface::class, NewsletterSampleDataPortAdapter::class);
    }

    public function boot(): void
    {
        $moduleRoot = dirname(__DIR__, 2);

        $this->loadMigrationsFrom($moduleRoot.'/database/migrations');
        $this->loadViewsFrom($moduleRoot.'/resources/views', 'newsletter');

        Route::middleware('api')
            ->prefix('api')
            ->group($moduleRoot.'/routes/api.php');

        Hook::listen('extension_activated', function (Extension $extension): void {
            if ($extension->slug !== 'newsletter') {
                return;
            }
            self::seedPermissions();
        });

        $this->ensurePermissionsIfMissing();
    }

    public static function seedPermissions(): void
    {
        (new NewsletterPermissionSeeder)->run();
    }

    protected function ensurePermissionsIfMissing(): void
    {
        try {
            if (! Schema::hasTable('permissions')) {
                return;
            }
            if (! Extension::query()->where('slug', 'newsletter')->where('status', 'active')->exists()) {
                return;
            }

            $missing = ! Permission::query()
                ->where('name', 'view newsletter')
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
