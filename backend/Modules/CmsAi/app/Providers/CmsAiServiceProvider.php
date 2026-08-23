<?php

declare(strict_types=1);

namespace Modules\CmsAi\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Core\System\Facades\Hook;
use Modules\Core\System\Models\Extension;
use Modules\CmsAi\Services\AiSubscriptionQuotaService;
use Modules\CmsAi\Services\AiTaxonomyBatchService;
use Modules\CmsAi\Services\AiUsageRecorder;
use Modules\CmsAi\Services\PublishingContentDraftService;
use Modules\CmsAi\Services\PublishingTaxonomySuggestService;

class CmsAiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PublishingContentDraftService::class);
        $this->app->singleton(PublishingTaxonomySuggestService::class);
        $this->app->singleton(AiTaxonomyBatchService::class);
        $this->app->singleton(AiUsageRecorder::class);
        $this->app->singleton(AiSubscriptionQuotaService::class);
    }

    public function boot(): void
    {
        $moduleRoot = dirname(__DIR__, 2);

        $this->loadMigrationsFrom($moduleRoot.'/database/migrations');

        Route::middleware('api')
            ->prefix('api')
            ->group($moduleRoot.'/routes/api.php');

        Hook::listen('extension_activated', function (Extension $extension): void {
            if ($extension->slug !== 'cms-ai') {
                return;
            }
            // Tables migrate on activate; no dedicated permissions (uses kernel AI settings gate).
        });
    }
}
