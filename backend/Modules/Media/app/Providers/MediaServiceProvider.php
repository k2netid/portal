<?php

declare(strict_types=1);

namespace Modules\Media\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Core\System\Facades\Hook;
use Modules\Core\System\Models\Extension;
use Modules\Media\Console\Commands\CleanupTempMedia;
use Modules\Media\Console\Commands\GenerateThumbnails;
use Modules\Media\Console\Commands\SyncMediaFromDisk;
use Modules\Media\Console\MigrateLegacyMedia;
use Modules\Media\Contracts\MediaServiceInterface;
use Modules\Media\Database\Seeders\MediaPermissionSeeder;
use Modules\Media\Models\File;
use Modules\Media\Policies\FilePolicy;
use Modules\Media\Services\MediaService;

class MediaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MediaService::class, MediaService::class);
        $this->app->singleton(MediaServiceInterface::class, fn ($app) => $app->make(MediaService::class));

        $moduleRoot = dirname(__DIR__, 2);
        if (is_file($moduleRoot.'/config/media.php')) {
            $this->mergeConfigFrom($moduleRoot.'/config/media.php', 'media');
        }
    }

    public function boot(): void
    {
        $moduleRoot = dirname(__DIR__, 2);

        $this->loadMigrationsFrom($moduleRoot.'/database/migrations');

        Route::middleware('api')
            ->prefix('api')
            ->group($moduleRoot.'/routes/api.php');

        Gate::policy(File::class, FilePolicy::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                MigrateLegacyMedia::class,
                CleanupTempMedia::class,
                SyncMediaFromDisk::class,
                GenerateThumbnails::class,
            ]);
        }

        Hook::listen('extension_activated', function (Extension $extension): void {
            if ($extension->slug !== 'media') {
                return;
            }
            self::seedPermissions();
        });
    }

    public static function seedPermissions(): void
    {
        (new MediaPermissionSeeder)->run();
    }
}
