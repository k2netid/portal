<?php

declare(strict_types=1);

namespace Modules\Intelligence\Providers;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use Modules\Content\Library\Events\TaxonomySearchIndexChanged;
use Modules\Content\Publishing\Contracts\NewsletterSampleDataPortInterface;
use Modules\Content\Publishing\Contracts\NewsletterSubscriberCountPortInterface;
use Modules\Content\Publishing\Events\ContentDeleted;
use Modules\Content\Publishing\Events\ContentPublished;
use Modules\Content\Publishing\Events\ContentUnpublished;
use Modules\Core\Providers\Concerns\RegistersMacroTierResources;
use Modules\Crm\Events\CrmSearchIndexChanged;
use Modules\Intelligence\Ai\Services\AiSubscriptionQuotaService;
use Modules\Intelligence\Ai\Services\AiTaxonomyBatchService;
use Modules\Intelligence\Ai\Services\AiUsageRecorder;
use Modules\Intelligence\Ai\Services\PublishingContentDraftService;
use Modules\Intelligence\Ai\Services\PublishingTaxonomySuggestService;
use Modules\Intelligence\Analytics\Console\Commands\CleanupAnalytics;
use Modules\Intelligence\Analytics\Console\Commands\CleanupSlowQueryLogs;
use Modules\Intelligence\Newsletter\Services\NewsletterSampleDataPortAdapter;
use Modules\Intelligence\Newsletter\Services\NewsletterSubscriberCountPortAdapter;
use Modules\Intelligence\Search\Console\Commands\ReindexSearch;
use Modules\Intelligence\Search\Console\Commands\SearchIndexHealth;
use Modules\Intelligence\Search\Contracts\SearchIndexerInterface;
use Modules\Intelligence\Search\Listeners\SyncContentSearchIndex;
use Modules\Intelligence\Search\Listeners\SyncCrmSearchIndex;
use Modules\Intelligence\Search\Listeners\SyncTaxonomySearchIndex;
use Modules\Intelligence\Search\Services\SearchIndexHealthService;
use Modules\Intelligence\Search\Services\UnifiedSearchIndexer;

class IntelligenceServiceProvider extends ServiceProvider
{
    use RegistersMacroTierResources;

    protected string $name = 'Intelligence';

    protected string $nameLower = 'intelligence';

    /** @var array<int, string> */
    private const SUBMODULES = ['ai', 'analytics', 'newsletter', 'search'];

    public function boot(): void
    {
        $tierRoot = dirname(__DIR__, 2);

        $this->registerMacroTierConfig($tierRoot, self::SUBMODULES);
        $this->registerMacroTierRoutes($tierRoot, self::SUBMODULES);
        $this->loadMigrationsFrom($tierRoot.'/database/migrations');
        $this->registerMacroTierViews($tierRoot, self::SUBMODULES);

        /** @var Dispatcher $events */
        $events = $this->app->make(Dispatcher::class);
        $searchSync = SyncContentSearchIndex::class;
        $events->listen(ContentPublished::class, [$searchSync, 'handlePublished']);
        $events->listen(ContentUnpublished::class, [$searchSync, 'handleUnpublished']);
        $events->listen(ContentDeleted::class, [$searchSync, 'handleDeleted']);
        $events->listen(TaxonomySearchIndexChanged::class, SyncTaxonomySearchIndex::class);
        $events->listen(CrmSearchIndexChanged::class, SyncCrmSearchIndex::class);

        $this->registerTierConsoleCommands([
            ReindexSearch::class,
            SearchIndexHealth::class,
            CleanupSlowQueryLogs::class,
            CleanupAnalytics::class,
        ]);
    }

    public function register(): void
    {
        $this->app->singleton(SearchIndexerInterface::class, UnifiedSearchIndexer::class);
        $this->app->singleton(SearchIndexHealthService::class);
        $this->app->singleton(PublishingContentDraftService::class);
        $this->app->singleton(PublishingTaxonomySuggestService::class);
        $this->app->singleton(AiTaxonomyBatchService::class);
        $this->app->singleton(AiUsageRecorder::class);
        $this->app->singleton(AiSubscriptionQuotaService::class);
        $this->app->singleton(NewsletterSampleDataPortInterface::class, NewsletterSampleDataPortAdapter::class);
        $this->app->singleton(NewsletterSubscriberCountPortInterface::class, NewsletterSubscriberCountPortAdapter::class);
    }
}
