<?php

declare(strict_types=1);

namespace Modules\Layout\Database\Seeders\Themes;

use Illuminate\Database\Seeder;
use Modules\Core\System\Models\Setting;
use Modules\Core\System\Models\User;
use Modules\Layout\Models\Theme;
use Modules\Layout\SampleData\ThemeSampleDataInstallOptions;
use Modules\Layout\SampleData\ThemeSampleDataOrchestrator;
use Modules\Layout\Services\ThemeService;
use Modules\Library\Models\Category;

class LayungThemeDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command?->info('Seeding Layung Theme (Generic ISP & Managed Service Provider Portal)...');

        // 1. Scan and activate Layung
        $themeService = app(ThemeService::class);
        $themeService->scanThemes();
        $layung = Theme::where('slug', 'layung')->first();

        if ($layung) {
            $layung->is_active = true;
            $layung->save();
            Setting::set('theme_active', 'layung', 'string', 'layout');
        }

        // 2. Generic Platform Identity for ISP/MSP
        Setting::set('site_name', 'Portal ISP Nusantara', 'string', 'general');
        Setting::set('site_title', 'Portal ISP Nusantara', 'string', 'general');
        Setting::set('site_tagline', 'Internet Service Provider & Managed Service Provider', 'string', 'general');
        Setting::set('site_description', 'Penyedia layanan internet berkecepatan tinggi, dedicated fiber optic, dan solusi managed IT terpadu untuk korporasi dan institusi.', 'string', 'general');
        Setting::set('contact_email', 'info@portal-isp.id', 'string', 'general');
        Setting::set('admin_email', 'admin@portal-isp.id', 'string', 'general');

        // 3. Install bundle sample data (menus, pages, theme settings)
        if ($layung) {
            try {
                $orchestrator = app(ThemeSampleDataOrchestrator::class);
                $options = new ThemeSampleDataInstallOptions(
                    force: true,
                    menus: true,
                    settings: true,
                    pages: true,
                    forms: true
                );
                $orchestrator->install($layung, $options);
            } catch (\Throwable $e) {
                $this->command?->warn('Sample data bundle install warning: '.$e->getMessage());
            }
        }

        // 4. Ensure ISP/MSP Categories
        $author = User::query()->first();
        if ($author) {
            $categories = [
                ['name' => 'Layanan Internet', 'slug' => 'layanan-internet', 'description' => 'Paket dedicated internet dan broadband bisnis'],
                ['name' => 'Managed Services', 'slug' => 'managed-services', 'description' => 'Solusi IT terkelola, monitoring jaringan, dan keamanan siber'],
                ['name' => 'Infrastruktur & Jaringan', 'slug' => 'infrastruktur', 'description' => 'Teknologi backbone, data center, dan fiber optic'],
            ];

            foreach ($categories as $cat) {
                Category::firstOrCreate(
                    ['slug' => $cat['slug']],
                    [
                        'name' => $cat['name'],
                        'description' => $cat['description'],
                        'is_active' => true,
                        'author_id' => $author->id,
                    ]
                );
            }
        }

        if (class_exists(\Modules\Layout\Services\ThemeCacheService::class)) {
            try {
                app(\Modules\Layout\Services\ThemeCacheService::class)->clearAll();
            } catch (\Throwable $e) {
                // Ignore cache clear error
            }
        }
        $this->command?->info('Layung Theme demo seed completed successfully.');
    }
}
