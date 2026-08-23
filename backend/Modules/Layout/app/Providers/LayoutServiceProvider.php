<?php

declare(strict_types=1);

namespace Modules\Layout\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Modules\Core\System\Contracts\LayoutRegistryInterface;
use Modules\Core\System\Facades\Hook;
use Modules\Core\System\Models\Extension;
use Modules\Core\System\Models\Permission;
use Modules\Layout\Database\Seeders\LayoutPermissionSeeder;

class LayoutServiceProvider extends ServiceProvider
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

        $this->registerDefaultLayoutLocations();

        Hook::listen('extension_activated', function (Extension $extension): void {
            if ($extension->slug !== 'layout') {
                return;
            }
            self::seedPermissions();
        });

        $this->ensurePermissionsIfMissing();
    }

    public static function seedPermissions(): void
    {
        (new LayoutPermissionSeeder)->run();
    }

    protected function ensurePermissionsIfMissing(): void
    {
        try {
            if (! Schema::hasTable('permissions')) {
                return;
            }

            $missing = ! Permission::query()
                ->where('name', 'view menus')
                ->where('guard_name', 'web')
                ->exists();

            if ($missing) {
                self::seedPermissions();
            }
        } catch (\Throwable) {
            // Avoid boot failure if permissions tables are mid-migrate.
        }
    }

    /**
     * P3-3a: without Themes pack, register sensible publishing location defaults.
     */
    protected function registerDefaultLayoutLocations(): void
    {
        if (! $this->app->bound(LayoutRegistryInterface::class)) {
            return;
        }

        /** @var LayoutRegistryInterface $registry */
        $registry = $this->app->make(LayoutRegistryInterface::class);

        if ($registry->getMenuLocations('publishing') === []) {
            $registry->registerMenuLocations('publishing', [
                'header',
                'header_top',
                'footer',
                'footer_col_1',
                'footer_col_2',
                'sidebar',
            ]);
        }

        if ($registry->getWidgetLocations('publishing') === []) {
            $registry->registerWidgetLocations('publishing', [
                'sidebar',
                'footer_top',
                'footer_bottom',
            ]);
        }

        $registry->registerWidgetTypes('publishing', [
            'html' => 'Custom HTML',
            'content_list' => 'Content List',
            'menu' => 'Navigation Menu',
            'form' => 'Custom Form',
            'text' => 'Text',
            'recent_posts' => 'Recent Posts',
            'categories' => 'Categories',
            'custom' => 'Custom',
        ]);
    }
}
