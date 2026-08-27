<?php

declare(strict_types=1);

namespace Modules\Search\Providers;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Modules\Core\System\Facades\Hook;
use Modules\Core\System\Models\Extension;
use Modules\Core\System\Models\Permission;
use Modules\Library\Events\TaxonomySearchIndexChanged;
use Modules\Publishing\Events\ContentDeleted;
use Modules\Publishing\Events\ContentPublished;
use Modules\Publishing\Events\ContentUnpublished;
use Modules\Search\Console\Commands\ReindexSearch;
use Modules\Search\Console\Commands\SearchIndexHealth;
use Modules\Search\Contracts\SearchIndexerInterface;
use Modules\Search\Database\Seeders\SearchPermissionSeeder;
use Modules\Search\Listeners\SyncContentSearchIndex;
use Modules\Search\Listeners\SyncTaxonomySearchIndex;
use Modules\Search\Services\SearchIndexHealthService;
use Modules\Search\Services\UnifiedSearchIndexer;

class SearchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SearchIndexerInterface::class, UnifiedSearchIndexer::class);
        $this->app->singleton(SearchIndexHealthService::class);
    }

    public function boot(): void
    {
        $moduleRoot = dirname(__DIR__, 2);

        $this->loadMigrationsFrom($moduleRoot.'/database/migrations');

        Route::middleware('api')
            ->prefix('api')
            ->group($moduleRoot.'/routes/api.php');

        /** @var Dispatcher $events */
        $events = $this->app->make(Dispatcher::class);
        $searchSync = SyncContentSearchIndex::class;
        $events->listen(ContentPublished::class, [$searchSync, 'handlePublished']);
        $events->listen(ContentUnpublished::class, [$searchSync, 'handleUnpublished']);
        $events->listen(ContentDeleted::class, [$searchSync, 'handleDeleted']);
        $events->listen(TaxonomySearchIndexChanged::class, SyncTaxonomySearchIndex::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                ReindexSearch::class,
                SearchIndexHealth::class,
            ]);
        }

        Hook::listen('extension_activated', function (Extension $extension): void {
            if ($extension->slug !== 'search') {
                return;
            }
            self::seedPermissions();
        });

        $this->ensurePermissionsIfMissing();
    }

    public static function seedPermissions(): void
    {
        (new SearchPermissionSeeder)->run();
    }

    protected function ensurePermissionsIfMissing(): void
    {
        try {
            if (! Schema::hasTable('permissions')) {
                return;
            }
            if (! Extension::query()->where('slug', 'search')->where('status', 'active')->exists()) {
                return;
            }

            $missing = ! Permission::query()
                ->where('name', 'manage search')
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
