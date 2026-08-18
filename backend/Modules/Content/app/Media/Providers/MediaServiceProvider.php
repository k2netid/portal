<?php

declare(strict_types=1);

namespace Modules\Content\Media\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\Content\Media\Console\Commands\CleanupTempMedia;
use Modules\Content\Media\Console\Commands\GenerateThumbnails;
use Modules\Content\Media\Console\Commands\SyncMediaFromDisk;
use Modules\Content\Media\Console\MigrateLegacyMedia;
use Modules\Content\Media\Contracts\MediaServiceInterface;
use Modules\Content\Media\Models\File;
use Modules\Content\Media\Policies\FilePolicy;
use Modules\Content\Media\Services\MediaService;

class MediaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MediaServiceInterface::class, MediaService::class);
    }

    public function boot(): void
    {
        Gate::policy(File::class, FilePolicy::class);

        // Register commands globally so they can be executed by ScheduledTaskController
        $this->commands([
            MigrateLegacyMedia::class,
            CleanupTempMedia::class,
            SyncMediaFromDisk::class,
            GenerateThumbnails::class,
        ]);
    }
}
