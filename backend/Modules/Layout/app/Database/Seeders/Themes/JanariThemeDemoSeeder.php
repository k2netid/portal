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
use Modules\Publishing\Models\Content;

class JanariThemeDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command?->info('Seeding Janari Theme (Generic Public/Government Portal)...');

        // 1. Scan and activate Janari
        $themeService = app(ThemeService::class);
        $themeService->scanThemes();
        $janari = Theme::where('slug', 'janari')->first();

        if ($janari) {
            $janari->is_active = true;
            $janari->save();
            Setting::set('theme_active', 'janari', 'string', 'layout');
        }

        // 2. Platform Identity Defaults
        Setting::set('site_name', 'Portal Resmi Komunitas', 'string', 'general');
        Setting::set('site_title', 'Portal Resmi Komunitas', 'string', 'general');
        Setting::set('site_tagline', 'Inovasi, Kolaborasi, dan Layanan Publik Terpadu', 'string', 'general');
        Setting::set('site_description', 'Portal layanan informasi publik resmi berbasis sistem modular Jejakawan.', 'string', 'general');
        Setting::set('contact_email', 'info@portal-komunitas.id', 'string', 'general');

        // 3. Install bundle sample data
        if ($janari) {
            try {
                $orchestrator = app(ThemeSampleDataOrchestrator::class);
                $options = new ThemeSampleDataInstallOptions(
                    force: true,
                    menus: true,
                    settings: true,
                    pages: true,
                    forms: true
                );
                $orchestrator->install($janari, $options);
            } catch (\Throwable $e) {
                $this->command?->warn('Sample data bundle install warning: '.$e->getMessage());
            }
        }

        // 4. Ensure Standard Categories & Content
        $author = User::query()->first();
        if ($author) {
            $categories = [
                ['name' => 'Berita Terkini', 'slug' => 'berita', 'description' => 'Kumpulan warta dan informasi terbaru'],
                ['name' => 'Pengumuman Resmi', 'slug' => 'pengumuman', 'description' => 'Edaran dan surat keputusan resmi'],
                ['name' => 'Agenda Kegiatan', 'slug' => 'agenda', 'description' => 'Jadwal pertemuan dan kegiatan publik'],
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

        $themeService->clearAllThemeCaches();
        $this->command?->info('Janari Theme demo seed completed successfully.');
    }
}
