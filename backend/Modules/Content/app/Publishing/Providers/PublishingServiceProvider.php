<?php

namespace Modules\Content\Publishing\Providers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Modules\Content\Library\Models\Tag;
use Modules\Content\Publishing\Console\Commands\PublishScheduledContentCommand;
use Modules\Content\Publishing\Contracts\NewsletterSubscriberCountPortInterface;
use Modules\Content\Publishing\Models\Comment;
use Modules\Content\Publishing\Models\Content;
use Modules\Content\Publishing\Services\PublishingCacheService;
use Modules\Core\System\Models\EmailTemplate;
use Modules\Core\System\Models\User;
use Modules\Core\System\Services\CacheService;
use Modules\Core\System\Services\CacheWarmingService;
use Modules\Core\System\Services\DashboardRegistry;

class PublishingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                PublishScheduledContentCommand::class,
            ]);
        }

        $this->registerUserModuleIntegrations();
        $this->registerDashboardStats();
        $this->registerModelRelations();
        $this->registerCacheIntegrations();
    }

    protected function registerCacheIntegrations(): void
    {
        CacheService::registerClearer('publishing', function (): void {
            app(PublishingCacheService::class)->clearAll();
        });

        CacheWarmingService::registerWarmer('publishing', fn () => app(PublishingCacheService::class)->warmUp());
    }

    protected function registerModelRelations(): void
    {
        Tag::resolveRelationUsing('contents', fn ($tagModel) => $tagModel->belongsToMany(Content::class, 'content_tag'));
    }

    protected function registerDashboardStats(): void
    {
        $this->app->booted(function (): void {
            $registry = $this->app->make(DashboardRegistry::class);

            $registry->registerStatsProvider('contents', fn () => [
                'total' => Content::count(),
                'published' => Content::where('status', 'published')->count(),
                'draft' => Content::where('status', 'draft')->count(),
                'pending' => Content::where('status', 'pending')->count(),
                'archived' => Content::where('status', 'archived')->count(),
            ]);

            $registry->registerStatsProvider('publishing_info', fn () => [
                'comments' => Comment::count(),
                'total_email_templates' => EmailTemplate::count(),
                'newsletter_subscribers' => app(NewsletterSubscriberCountPortInterface::class)->subscribedCount(),
                'email' => [
                    'templates' => EmailTemplate::count(),
                    'subscribers' => app(NewsletterSubscriberCountPortInterface::class)->subscribedCount(),
                    'smtp_status' => Cache::get('email_smtp_status', 'unknown'),
                ],
            ]);

            $registry->registerChartProvider('contentByStatus', fn () => Content::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get());

            $registry->registerStatsProvider('viewer', fn () => Content::where('status', 'published')
                ->latest()
                ->take(5)
                ->select('id', 'title', 'slug', 'created_at')
                ->get());
        });
    }

    protected function registerUserModuleIntegrations(): void
    {
        User::registerRoleRanks([
            'editor' => 60,
            'author' => 40,
        ]);
    }

    public function register(): void
    {
        // Custom registrations
    }
}
