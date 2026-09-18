<?php

declare(strict_types=1);

namespace Modules\Layout\Database\Seeders\Themes;

use Illuminate\Database\Seeder;
use Modules\Core\System\Models\Setting;
use Modules\Core\System\Services\LicenseService;
use Modules\Layout\Models\Theme;
use Modules\Layout\SampleData\ThemeSampleDataInstallOptions;
use Modules\Layout\SampleData\ThemeSampleDataOrchestrator;
use Modules\Layout\Services\ThemeService;

class SareupnaThemeDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command?->info('Seeding Sareupna Theme (High-Performance Cloud & Developer Platform)...');

        // Guard: skip if current license tier does not allow premium themes (ADR-023 §2.8)
        if (class_exists(LicenseService::class)) {
            /** @var LicenseService $license */
            $license = app(LicenseService::class);
            $quota = $license->getThemeQuota($license->getLicenseTier());
            if ($quota['max_premium_active'] === 0) {
                $this->command?->warn(
                    'Skipping Sareupna demo seed — current license tier does not allow premium themes (max_premium_active=0).'
                );

                return;
            }
        }

        // 1. Scan and activate Sareupna
        $themeService = app(ThemeService::class);
        $themeService->scanThemes();
        $sareupna = Theme::where('slug', 'sareupna')->first();

        if ($sareupna) {
            $sareupna->activate();
            Setting::set('theme_active', 'sareupna', 'string', 'layout');
        }

        $force = (bool) config('layout.theme_seed_force', false);

        // 2. Set generic demo identity (Non-destructive: only sets if missing)
        if ($force) {
            Setting::set('site_name', 'Sareupna Platform', 'string', 'general');
            Setting::set('site_title', 'Sareupna Platform', 'string', 'general');
            Setting::set('site_tagline', 'High-Performance Cloud & Developer Platform', 'string', 'general');
            Setting::set('site_description', 'Platform cloud terdistribusi, engine konten berperforma tinggi, dan ekosistem pengembang modern dalam satu platform terpadu.', 'string', 'general');
        } else {
            Setting::setIfMissing('site_name', 'Sareupna Platform', 'string', 'general');
            Setting::setIfMissing('site_title', 'Sareupna Platform', 'string', 'general');
            Setting::setIfMissing('site_tagline', 'High-Performance Cloud & Developer Platform', 'string', 'general');
            Setting::setIfMissing('site_description', 'Platform cloud terdistribusi, engine konten berperforma tinggi, dan ekosistem pengembang modern dalam satu platform terpadu.', 'string', 'general');
        }

        // 3. Install bundle sample data (menus, pages, settings)
        if ($sareupna) {
            try {
                $orchestrator = app(ThemeSampleDataOrchestrator::class);
                $options = new ThemeSampleDataInstallOptions(
                    force: $force,
                    menus: true,
                    settings: true,
                    pages: true,
                    forms: true
                );
                $orchestrator->install($sareupna, $options);
            } catch (\Throwable $e) {
                $this->command?->warn('Sample data bundle install warning: '.$e->getMessage());
            }
        }

        $this->command?->info('Sareupna Theme seeded and activated successfully!');
    }
}
