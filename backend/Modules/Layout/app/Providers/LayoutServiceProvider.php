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
use Modules\Layout\Console\Commands\BackfillThemeJanariParentCommand;
use Modules\Layout\Console\Commands\ThemeBackfillSourceCommand;
use Modules\Layout\Console\Commands\ThemeBuildCommand;
use Modules\Layout\Console\Commands\ThemeChecksumCommand;
use Modules\Layout\Console\Commands\ThemeExportCommand;
use Modules\Layout\Console\Commands\ThemeMake;
use Modules\Layout\Console\Commands\ThemePackageCommand;
use Modules\Layout\Console\Commands\ThemePathsCommand;
use Modules\Layout\Console\Commands\ThemeScanRegisterCommand;
use Modules\Layout\Console\Commands\ThemeStagingUploadedCommand;
use Modules\Layout\Console\Commands\ThemeValidateCommand;
use Modules\Layout\Database\Seeders\LayoutPermissionSeeder;
use Modules\Layout\Services\ThemeService;

class LayoutServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ThemeService::class, ThemeService::class);
    }

    public function boot(): void
    {
        $moduleRoot = dirname(__DIR__, 2);

        $this->loadMigrationsFrom($moduleRoot.'/database/migrations');

        Route::middleware('api')
            ->prefix('api')
            ->group($moduleRoot.'/routes/api.php');

        if ($this->app->runningInConsole()) {
            $this->commands([
                BackfillThemeJanariParentCommand::class,
                ThemeMake::class,
                ThemePathsCommand::class,
                ThemeValidateCommand::class,
                ThemePackageCommand::class,
                ThemeExportCommand::class,
                ThemeStagingUploadedCommand::class,
                ThemeScanRegisterCommand::class,
                ThemeBackfillSourceCommand::class,
                ThemeBuildCommand::class,
                ThemeChecksumCommand::class,
            ]);
        }

        $this->registerLayoutRegistryIntegrations();

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
            if (! Extension::query()->where('slug', 'layout')->where('status', 'active')->exists()) {
                return;
            }

            $missing = ! Permission::query()
                ->where('name', 'manage themes')
                ->where('guard_name', 'web')
                ->exists();

            if ($missing) {
                self::seedPermissions();
            }
        } catch (\Throwable) {
            // Avoid boot failure if permissions tables are mid-migrate.
        }
    }

    protected function registerLayoutRegistryIntegrations(): void
    {
        $this->app->booted(function (): void {
            if (! $this->app->bound(LayoutRegistryInterface::class)) {
                return;
            }

            /** @var LayoutRegistryInterface $registry */
            $registry = $this->app->make(LayoutRegistryInterface::class);
            $themeService = $this->app->make(ThemeService::class);

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

            try {
                $activeTheme = $themeService->getActiveTheme('frontend');
            } catch (\Throwable) {
                $activeTheme = null;
            }

            if ($activeTheme) {
                $registry->registerMenuLocations('publishing', $themeService->getMenuLocations($activeTheme));
                $registry->registerWidgetLocations('publishing', $themeService->getWidgetLocations($activeTheme));

                return;
            }

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
        });
    }
}
