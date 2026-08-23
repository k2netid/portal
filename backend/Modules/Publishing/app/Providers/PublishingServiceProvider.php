<?php

declare(strict_types=1);

namespace Modules\Publishing\Providers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Modules\Core\System\Facades\Hook;
use Modules\Core\System\Models\EmailTemplate;
use Modules\Core\System\Models\Extension;
use Modules\Core\System\Models\Permission;
use Modules\Core\System\Models\User;
use Modules\Core\System\Services\CacheService;
use Modules\Core\System\Services\CacheWarmingService;
use Modules\Core\System\Services\DashboardRegistry;
use Modules\Library\Models\Tag;
use Modules\Publishing\Console\Commands\PublishScheduledContentCommand;
use Modules\Publishing\Contracts\NewsletterSubscriberCountPortInterface;
use Modules\Publishing\Contracts\PublishedContentAnalyticsPortInterface;
use Modules\Publishing\Contracts\PublishingSearchReadPortInterface;
use Modules\Publishing\Database\Seeders\PublishingPermissionSeeder;
use Modules\Publishing\Models\Comment;
use Modules\Publishing\Models\Content;
use Modules\Publishing\Repositories\EloquentPublishingSearchReadRepository;
use Modules\Publishing\Services\NullNewsletterSubscriberCountPort;
use Modules\Publishing\Services\PublishedContentAnalyticsPortAdapter;
use Modules\Publishing\Services\PublishingCacheService;

class PublishingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            NewsletterSubscriberCountPortInterface::class,
            NullNewsletterSubscriberCountPort::class
        );
        $this->app->singleton(
            PublishedContentAnalyticsPortInterface::class,
            PublishedContentAnalyticsPortAdapter::class
        );
        $this->app->singleton(
            PublishingSearchReadPortInterface::class,
            EloquentPublishingSearchReadRepository::class
        );
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
                PublishScheduledContentCommand::class,
            ]);
        }

        $this->registerUserModuleIntegrations();
        $this->registerDashboardStats();
        $this->registerModelRelations();
        $this->registerCacheIntegrations();
        $this->registerActivationHooks();
        $this->ensurePermissionsIfMissing();
    }

    /**
     * Seed CMS permissions (also runs on library/publishing activate via Hook).
     */
    public static function seedPermissions(): void
    {
        (new PublishingPermissionSeeder)->run();
    }

    /**
     * Heal installs where packs were marked active without extension_activated
     * (or seeder never ran) so create/edit routes and APIs have real permissions.
     */
    protected function ensurePermissionsIfMissing(): void
    {
        try {
            if (! Schema::hasTable('permissions')) {
                return;
            }

            $missing = ! Permission::query()
                ->where('name', 'create content')
                ->where('guard_name', 'web')
                ->exists();

            if ($missing) {
                self::seedPermissions();
            }
        } catch (\Throwable) {
            // Avoid boot failure if permissions tables are mid-migrate.
        }
    }

    protected function registerActivationHooks(): void
    {
        Hook::listen('extension_activated', function (Extension $extension): void {
            if (! in_array($extension->slug, ['publishing', 'library'], true)) {
                return;
            }
            self::seedPermissions();
        });
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
}
