<?php

declare(strict_types=1);

namespace Modules\Content\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Content\Layout\Services\PluginThemeBlocksValidator;
use Modules\Content\Library\Contracts\TaxonomySearchPortInterface;
use Modules\Content\Library\Services\TaxonomySearchPortAdapter;
use Modules\Content\Media\Providers\MediaServiceProvider;
use Modules\Content\Publishing\Contracts\PublishedContentAnalyticsPortInterface;
use Modules\Content\Publishing\Contracts\PublishingSearchReadPortInterface;
use Modules\Content\Publishing\Providers\PublishingServiceProvider;
use Modules\Content\Publishing\Repositories\EloquentPublishingSearchReadRepository;
use Modules\Content\Publishing\Services\PublishedContentAnalyticsPortAdapter;
use Modules\Core\Providers\Concerns\RegistersMacroTierResources;

class ContentServiceProvider extends ServiceProvider
{
    use RegistersMacroTierResources;

    protected string $name = 'Content';

    protected string $nameLower = 'content';

    /** Studio subdomains (Layout, Forms, Library) — boot via ContentStudioServiceProvider. */
    private const STUDIO_SUBMODULES = ['layout', 'forms', 'library'];

    /** @var array<int, string> */
    private const SUBMODULES = ['publishing', ...self::STUDIO_SUBMODULES, 'media'];

    public function boot(): void
    {
        $tierRoot = dirname(__DIR__, 2);

        $this->registerMacroTierConfig($tierRoot, self::SUBMODULES);
        $this->registerMacroTierRoutes($tierRoot, self::SUBMODULES);
        $this->loadMigrationsFrom($tierRoot.'/database/migrations');
        $this->registerMacroTierViews($tierRoot, self::SUBMODULES);

        $this->app->register(ContentStudioServiceProvider::class);
        $this->app->register(PublishingServiceProvider::class);
        $this->app->register(MediaServiceProvider::class);
    }

    public function register(): void
    {
        $this->app->singleton(PluginThemeBlocksValidator::class);
        $this->app->singleton(TaxonomySearchPortInterface::class, TaxonomySearchPortAdapter::class);
        $this->app->singleton(PublishingSearchReadPortInterface::class, EloquentPublishingSearchReadRepository::class);
        $this->app->singleton(PublishedContentAnalyticsPortInterface::class, PublishedContentAnalyticsPortAdapter::class);
    }
}
