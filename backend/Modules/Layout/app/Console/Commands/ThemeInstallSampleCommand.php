<?php

declare(strict_types=1);

namespace Modules\Layout\Console\Commands;

use Illuminate\Console\Command;
use Modules\Layout\Models\Theme;
use Modules\Layout\SampleData\ThemeSampleDataInstallOptions;
use Modules\Layout\SampleData\ThemeSampleDataOrchestrator;
use Modules\Layout\SampleData\ThemeSampleDataReader;
use Modules\Layout\Services\ThemeService;

class ThemeInstallSampleCommand extends Command
{
    protected $signature = 'theme:install-sample
                            {slug : Theme slug (janari, sarangenge, layung)}
                            {--force : Overwrite existing non-sample data}
                            {--only= : Comma-separated: menus,settings,pages,forms}';

    protected $description = 'Install theme sample data (menus, settings, CMS page shells) from sample-data/bundle.json';

    public function handle(
        ThemeSampleDataReader $reader,
        ThemeSampleDataOrchestrator $orchestrator,
        ThemeService $themeService,
    ): int {
        $slug = strtolower(trim((string) $this->argument('slug')));
        if ($slug === '') {
            $this->error('Theme slug is required.');

            return self::FAILURE;
        }

        if (! $reader->hasBundle($slug)) {
            $this->error("No sample-data bundle for theme [{$slug}].");

            return self::FAILURE;
        }

        $themeService->scanThemes();
        $theme = Theme::query()->where('slug', $slug)->first();
        if ($theme === null) {
            $this->error("Theme [{$slug}] not found. Run theme:scan-register first.");

            return self::FAILURE;
        }

        $only = $this->option('only');
        $onlyParts = is_string($only) && trim($only) !== ''
            ? array_map('trim', explode(',', strtolower($only)))
            : null;

        $options = new ThemeSampleDataInstallOptions(
            force: (bool) $this->option('force'),
            menus: $onlyParts === null || in_array('menus', $onlyParts, true),
            settings: $onlyParts === null || in_array('settings', $onlyParts, true),
            pages: $onlyParts === null || in_array('pages', $onlyParts, true),
            forms: $onlyParts === null || in_array('forms', $onlyParts, true),
        );

        try {
            $result = $orchestrator->install($theme, $options);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info("Sample data installed for [{$result->themeSlug}].");
        $this->line("  Menus: {$result->menusInstalled}");
        $this->line("  Pages: {$result->pagesInstalled}");
        $this->line("  Posts: {$result->postsInstalled}");
        $this->line("  Settings: {$result->settingsApplied}");

        foreach ($result->messages as $message) {
            $this->line("  • {$message}");
        }
        foreach ($result->warnings as $warning) {
            $this->warn("  ! {$warning}");
        }

        return self::SUCCESS;
    }
}
