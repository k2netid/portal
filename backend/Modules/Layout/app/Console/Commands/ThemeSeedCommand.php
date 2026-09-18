<?php

declare(strict_types=1);

namespace Modules\Layout\Console\Commands;

use Illuminate\Console\Command;
use Modules\Core\System\Models\Setting;
use Modules\Core\System\Services\LicenseService;
use Modules\Layout\Database\Seeders\Themes\JanariThemeDemoSeeder;
use Modules\Layout\Database\Seeders\Themes\LayungThemeDemoSeeder;
use Modules\Layout\Database\Seeders\Themes\SarangengeThemeDemoSeeder;
use Modules\Layout\Database\Seeders\Themes\SareupnaThemeDemoSeeder;
use Modules\Layout\Services\ThemeService;

class ThemeSeedCommand extends Command
{
    protected $signature = 'theme:seed
                            {slug? : Theme slug (janari, layung, sarangenge, sareupna). Defaults to active theme.}
                            {--all : Seed demo data for all supported themes}';

    protected $description = 'Seed generic starter demo data for a theme (janari, layung, sarangenge, sareupna) or the currently active theme.';

    public function handle(ThemeService $themeService): int
    {
        $themeService->scanThemes();

        if ($this->option('all')) {
            $this->info('Seeding demo data for all official themes...');

            // Check premium entitlement (ADR-023 §2.8) — warn but let each seeder guard itself
            $premiumEntitled = true;
            if (class_exists(LicenseService::class)) {
                /** @var LicenseService $license */
                $license = app(LicenseService::class);
                $quota = $license->getThemeQuota($license->getLicenseTier());
                if ($quota['max_premium_active'] === 0) {
                    $this->warn('Current license tier does not allow premium themes. Only Janari will be seeded.');
                    $premiumEntitled = false;
                }
            }

            $this->call('db:seed', ['--class' => JanariThemeDemoSeeder::class]);

            if ($premiumEntitled) {
                $this->call('db:seed', ['--class' => LayungThemeDemoSeeder::class]);
                $this->call('db:seed', ['--class' => SarangengeThemeDemoSeeder::class]);
                $this->call('db:seed', ['--class' => SareupnaThemeDemoSeeder::class]);
            }

            $this->info('Theme demo dataset seeding completed.');

            return self::SUCCESS;
        }

        $slugArg = $this->argument('slug');
        $slugRaw = is_string($slugArg) && $slugArg !== '' ? $slugArg : Setting::get('theme_active', 'janari');
        $slug = strtolower(trim(is_string($slugRaw) ? $slugRaw : 'janari'));

        $this->info("Target theme for demo seeding: [{$slug}]");

        match ($slug) {
            'janari' => $this->call('db:seed', ['--class' => JanariThemeDemoSeeder::class]),
            'layung' => $this->call('db:seed', ['--class' => LayungThemeDemoSeeder::class]),
            'sarangenge' => $this->call('db:seed', ['--class' => SarangengeThemeDemoSeeder::class]),
            'sareupna' => $this->call('db:seed', ['--class' => SareupnaThemeDemoSeeder::class]),
            default => $this->fallbackGenericSeed($slug),
        };

        return self::SUCCESS;
    }

    private function fallbackGenericSeed(string $slug): int
    {
        $this->warn("Theme [{$slug}] does not have a dedicated demo seeder class. Falling back to theme:install-sample...");

        return $this->call('theme:install-sample', [
            'slug' => $slug,
            '--force' => true,
        ]);
    }
}
