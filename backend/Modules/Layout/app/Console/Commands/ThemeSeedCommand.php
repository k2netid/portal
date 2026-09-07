<?php

declare(strict_types=1);

namespace Modules\Layout\Console\Commands;

use Illuminate\Console\Command;
use Modules\Core\System\Models\Setting;
use Modules\Layout\Database\Seeders\Themes\JanariThemeDemoSeeder;
use Modules\Layout\Database\Seeders\Themes\LayungThemeDemoSeeder;
use Modules\Layout\Database\Seeders\Themes\SarangengeThemeDemoSeeder;
use Modules\Layout\Services\ThemeService;

class ThemeSeedCommand extends Command
{
    protected $signature = 'theme:seed
                            {slug? : Theme slug (janari, layung, sarangenge). Defaults to active theme.}
                            {--all : Seed demo data for all supported themes}';

    protected $description = 'Seed generic starter demo data for a theme (janari, layung, sarangenge) or the currently active theme.';

    public function handle(ThemeService $themeService): int
    {
        $themeService->scanThemes();

        if ($this->option('all')) {
            $this->info('Seeding demo data for all official themes...');
            $this->call(JanariThemeDemoSeeder::class);
            $this->call(LayungThemeDemoSeeder::class);
            $this->call(SarangengeThemeDemoSeeder::class);
            $this->info('All theme demo datasets seeded successfully.');

            return self::SUCCESS;
        }

        $slug = (string) ($this->argument('slug') ?: Setting::get('theme_active', 'janari'));
        $slug = strtolower(trim($slug));

        $this->info("Target theme for demo seeding: [{$slug}]");

        return match ($slug) {
            'janari' => $this->call(JanariThemeDemoSeeder::class),
            'layung' => $this->call(LayungThemeDemoSeeder::class),
            'sarangenge' => $this->call(SarangengeThemeDemoSeeder::class),
            default => $this->fallbackGenericSeed($slug),
        };
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
