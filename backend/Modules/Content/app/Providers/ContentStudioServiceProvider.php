<?php

declare(strict_types=1);

namespace Modules\Content\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Content\Forms\Models\Form;
use Modules\Content\Forms\Models\FormSubmission;
use Modules\Content\Layout\Console\Commands\BackfillThemeJanariParentCommand;
use Modules\Content\Layout\Console\Commands\ThemeBackfillSourceCommand;
use Modules\Content\Layout\Console\Commands\ThemeBuildCommand;
use Modules\Content\Layout\Console\Commands\ThemeChecksumCommand;
use Modules\Content\Layout\Console\Commands\ThemeMake;
use Modules\Content\Layout\Console\Commands\ThemePackageCommand;
use Modules\Content\Layout\Console\Commands\ThemePathsCommand;
use Modules\Content\Layout\Console\Commands\ThemeScanRegisterCommand;
use Modules\Content\Layout\Console\Commands\ThemeStagingUploadedCommand;
use Modules\Content\Layout\Console\Commands\ThemeValidateCommand;
use Modules\Content\Layout\Services\ThemeService;
use Modules\Content\Library\Models\Category;
use Modules\Content\Library\Models\Tag;
use Modules\Content\Library\Observers\CategorySearchObserver;
use Modules\Content\Library\Observers\TagSearchObserver;
use Modules\Core\System\Contracts\LayoutRegistryInterface;
use Modules\Core\System\Services\DashboardRegistry;

/**
 * Consolidated boot for Content studio subdomains: Layout, Forms, Library.
 * Routes/config remain per-submodule via ContentServiceProvider + RegistersMacroTierResources.
 */
class ContentStudioServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerTierConsoleCommands([
            BackfillThemeJanariParentCommand::class,
            ThemeMake::class,
            ThemePathsCommand::class,
            ThemeValidateCommand::class,
            ThemePackageCommand::class,
            ThemeStagingUploadedCommand::class,
            ThemeScanRegisterCommand::class,
            ThemeBackfillSourceCommand::class,
            ThemeBuildCommand::class,
            ThemeChecksumCommand::class,
        ]);

        Category::observe(CategorySearchObserver::class);
        Tag::observe(TagSearchObserver::class);

        $this->registerLayoutRegistryIntegrations();
        $this->registerDashboardStats();
    }

    protected function registerLayoutRegistryIntegrations(): void
    {
        $this->app->booted(function (): void {
            if (! $this->app->bound(LayoutRegistryInterface::class)) {
                return;
            }

            $registry = $this->app->make(LayoutRegistryInterface::class);
            $themeService = $this->app->make(ThemeService::class);

            try {
                $activeTheme = $themeService->getActiveTheme('frontend');
            } catch (\Exception) {
                $activeTheme = null;
            }

            if ($activeTheme) {
                $registry->registerMenuLocations('publishing', $themeService->getMenuLocations($activeTheme));
                $registry->registerWidgetLocations('publishing', $themeService->getWidgetLocations($activeTheme));
            } else {
                $registry->registerMenuLocations('publishing', ['header', 'footer', 'sidebar']);
                $registry->registerWidgetLocations('publishing', ['sidebar', 'footer_top', 'footer_bottom']);
            }
        });
    }

    protected function registerDashboardStats(): void
    {
        $this->app->booted(function (): void {
            $registry = $this->app->make(DashboardRegistry::class);

            $registry->registerStatsProvider('library_taxonomy', fn () => [
                'categories' => Category::count(),
                'tags' => Tag::count(),
            ]);

            $registry->registerStatsProvider('forms', fn () => [
                'forms' => Form::count(),
                'form_submissions' => FormSubmission::count(),
            ]);
        });
    }

    /**
     * @param  array<int, class-string>  $commandClasses
     */
    protected function registerTierConsoleCommands(array $commandClasses): void
    {
        if ($commandClasses === []) {
            return;
        }

        $this->commands($commandClasses);
    }
}
