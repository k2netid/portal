<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\System\Models\Setting;
use Modules\Layout\Database\Seeders\Themes\SarangengeThemeDemoSeeder;
use Modules\Layout\Models\Theme;
use Modules\Layout\Services\ThemeCacheService;
use Modules\Publishing\Database\Seeders\VocationalContentSeeder;
use Modules\Publishing\Database\Seeders\VocationalFacilitiesSeeder;
use Modules\Publishing\Database\Seeders\VocationalProgramsSeeder;

class Smkn6DeploymentSeeder extends Seeder
{
    /**
     * Seed official SMKN 6 Bandung deployment identity and vocational content.
     * Builds upon the Sarangenge theme foundation and specializes for SMKN 6.
     */
    public function run(): void
    {
        $this->command?->info('Seeding SMKN 6 Bandung deployment identity configuration...');

        // 1. Run baseline Sarangenge Theme demo seeder
        $this->call(SarangengeThemeDemoSeeder::class);

        // 2. Override with official SMKN 6 Bandung System Identity
        Setting::set('site_name', 'SMK Negeri 6 Bandung', 'string', 'general');
        Setting::set('site_title', 'SMK Negeri 6 Bandung', 'string', 'general');
        Setting::set('site_tagline', 'Maju Mandiri Berkarakter — Pusat Keunggulan Vokasi Jawa Barat', 'string', 'general');
        Setting::set('site_description', 'Portal resmi SMK Negeri 6 Bandung: Pendidikan vokasi berstandar industri dengan 5 kompetensi keahlian unggulan (DPIB, TITL, TPM, TKR, TO).', 'string', 'general');
        Setting::set('contact_email', 'info@smkn6bandung.sch.id', 'string', 'general');
        Setting::set('admin_email', 'ict@smkn6bandung.sch.id', 'string', 'general');

        // 3. Active Theme Configuration (Sarangenge with SMKN 6 specifics)
        $sarangenge = Theme::where('slug', 'sarangenge')->first();

        if ($sarangenge) {
            $currentSettings = is_array($sarangenge->settings) ? $sarangenge->settings : [];

            $smkn6ThemeSettings = [
                'site_title' => 'SMK Negeri 6 Bandung',
                'school_name' => 'SMK Negeri 6 Bandung',
                'contact_email' => 'info@smkn6bandung.sch.id',
                'contact_address' => 'Jl. Soekarno-Hatta / Riung Bandung No. 1, Kota Bandung, Jawa Barat 40292',
                'contact_phone' => '022-7563200',
                'ppdb_is_open' => true,
            ];

            $sarangenge->settings = array_merge($currentSettings, $smkn6ThemeSettings);
            $sarangenge->is_active = true;
            $sarangenge->save();

            // Set Sarangenge as primary active theme in settings
            Setting::set('theme_active', 'sarangenge', 'string', 'layout');

            if (class_exists(ThemeCacheService::class)) {
                try {
                    app(ThemeCacheService::class)->clear();
                } catch (\Throwable $e) {
                    // Ignore cache clear error
                }
            }
        }

        // 4. Seed official SMKN 6 Vocational Programs, Facilities, and Content
        if (class_exists(VocationalProgramsSeeder::class)) {
            $this->call(VocationalProgramsSeeder::class);
        }

        if (class_exists(VocationalFacilitiesSeeder::class)) {
            $this->call(VocationalFacilitiesSeeder::class);
        }

        if (class_exists(VocationalContentSeeder::class)) {
            $this->call(VocationalContentSeeder::class);
        }

        $this->command?->info('SMKN 6 Bandung deployment configuration seeded successfully.');
    }
}
