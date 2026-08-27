<?php

declare(strict_types=1);

namespace Modules\Library\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Library\Contracts\TaxonomySearchPortInterface;
use Modules\Library\Models\Category;
use Modules\Library\Models\Tag;
use Modules\Core\System\Models\Extension;
use Modules\Library\Observers\CategorySearchObserver;
use Modules\Library\Observers\TagSearchObserver;
use Modules\Library\Services\TaxonomySearchPortAdapter;

class LibraryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TaxonomySearchPortInterface::class, TaxonomySearchPortAdapter::class);
    }

    public function boot(): void
    {
        $moduleRoot = dirname(__DIR__, 2);

        $this->loadMigrationsFrom($moduleRoot.'/database/migrations');

        Route::middleware('api')
            ->prefix('api')
            ->group($moduleRoot.'/routes/api.php');

        if (Extension::isProductActive('library')) {
            Category::observe(CategorySearchObserver::class);
            Tag::observe(TagSearchObserver::class);
        }

        if ($this->app->runningInConsole()) {
            // Permissions seeded from Publishing pack (shared CMS perms).
        }
    }
}
