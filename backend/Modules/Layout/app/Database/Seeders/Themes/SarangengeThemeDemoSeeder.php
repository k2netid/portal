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

class SarangengeThemeDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command?->info('Seeding Sarangenge Theme (Generic Vocational School / Pusat Keunggulan Portal)...');

        // 1. Scan and activate Sarangenge
        $themeService = app(ThemeService::class);
        $themeService->scanThemes();
        $sarangenge = Theme::where('slug', 'sarangenge')->first();

        if ($sarangenge) {
            $sarangenge->is_active = true;
            $sarangenge->save();
            Setting::set('theme_active', 'sarangenge', 'string', 'layout');
        }

        // 2. Generic Platform Identity for Vocational School
        Setting::set('site_name', 'SMK Pusat Keunggulan Nusantara', 'string', 'general');
        Setting::set('site_title', 'SMK Pusat Keunggulan Nusantara', 'string', 'general');
        Setting::set('site_tagline', 'Maju Mandiri Berkarakter — Sekolah Menengah Kejuruan Pusat Keunggulan', 'string', 'general');
        Setting::set('site_description', 'Portal resmi sekolah menengah kejuruan pusat keunggulan: pendidikan vokasi berstandar industri dengan program keahlian unggulan, fasilitas modern, dan kemitraan DUDI.', 'string', 'general');
        Setting::set('contact_email', 'info@smk-nusantara.sch.id', 'string', 'general');
        Setting::set('admin_email', 'admin@smk-nusantara.sch.id', 'string', 'general');

        // 3. Install bundle sample data (menus, pages, settings)
        if ($sarangenge) {
            try {
                $orchestrator = app(ThemeSampleDataOrchestrator::class);
                $options = new ThemeSampleDataInstallOptions(
                    force: true,
                    menus: true,
                    settings: true,
                    pages: true,
                    forms: true
                );
                $orchestrator->install($sarangenge, $options);
            } catch (\Throwable $e) {
                $this->command?->warn('Sample data bundle install warning: '.$e->getMessage());
            }
        }

        // 4. Ensure Vocational Categories
        $author = User::query()->first();
        if ($author) {
            $catProgram = Category::firstOrCreate(
                ['slug' => 'program-keahlian'],
                [
                    'name' => 'Program Keahlian',
                    'description' => 'Program Keahlian Vokasi & Kompetensi Industri',
                    'is_active' => true,
                    'author_id' => $author->id,
                ]
            );

            $catFasilitas = Category::firstOrCreate(
                ['slug' => 'fasilitas'],
                [
                    'name' => 'Fasilitas & Sarana',
                    'description' => 'Laboratorium, bengkel kerja, dan sarana praktik modern',
                    'is_active' => true,
                    'author_id' => $author->id,
                ]
            );

            // 5. Seed Sample Vocational Majors (Generic)
            $sampleMajors = [
                [
                    'title' => 'Rekayasa Perangkat Lunak (RPL)',
                    'slug' => 'rpl',
                    'excerpt' => 'Program keahlian yang mendalami rekayasa perangkat lunak, pemrograman web, mobile, dan kecerdasan buatan.',
                    'body' => '<p>Rekayasa Perangkat Lunak (RPL) mempersiapkan siswa menjadi pengembang perangkat lunak profesional dengan kompetensi pemrograman web modern, mobile application, database management, dan integrasi cloud computing.</p><ul><li>Web Development (Vue.js, React, Laravel)</li><li>Mobile App Development (Flutter, React Native)</li><li>Database & Cloud Computing</li><li>DevOps & Software Quality Assurance</li></ul>',
                ],
                [
                    'title' => 'Teknik Jaringan Komputer & Telekomunikasi (TJKT)',
                    'slug' => 'tjkt',
                    'excerpt' => 'Membekali peserta didik dengan keahlian perancangan, instalasi, dan administrasi infrastruktur jaringan komputer serta cybersecurity.',
                    'body' => '<p>Teknik Jaringan Komputer dan Telekomunikasi (TJKT) fokus pada penguasaan routing switching, keamanan jaringan, fiber optic, dan administrasi server linux/cloud.</p><ul><li>Routing, Switching & BGP Management</li><li>Fiber Optic Splicing & Testing (OTDR)</li><li>Cybersecurity & Perimeter Defense</li><li>Cloud Infrastructure & Virtualization</li></ul>',
                ],
                [
                    'title' => 'Desain Komunikasi Visual (DKV)',
                    'slug' => 'dkv',
                    'excerpt' => 'Fokus pada kreativitas desain grafis, ilustrasi digital, animasi 2D/3D, fotografi, dan videografi komersial.',
                    'body' => '<p>Desain Komunikasi Visual (DKV) mengembangkan talenta kreatif peserta didik dalam bidang perancangan visual, branding, media interaktif, motion graphics, dan produksi konten digital.</p><ul><li>Brand Identity & Graphic Design</li><li>2D/3D Animation & Motion Graphic</li><li>Digital Photography & Commercial Video</li><li>UI/UX Design for Digital Products</li></ul>',
                ],
                [
                    'title' => 'Teknik Otomasi Industri (TOI)',
                    'slug' => 'toi',
                    'excerpt' => 'Mempersiapkan teknisi handal dalam sistem otomasi industri, PLC, robotika manufaktur, dan sistem IoT.',
                    'body' => '<p>Teknik Otomasi Industri membekali siswa dengan pemahaman sistem kendali elektronik, PLC programming, sensorika, pneumatik, dan robotika industri.</p><ul><li>Programmable Logic Controller (PLC)</li><li>Pneumatics & Hydraulics Control</li><li>Industrial Robotics & SCADA</li><li>Industrial Internet of Things (IIoT)</li></ul>',
                ],
            ];

            foreach ($sampleMajors as $major) {
                Content::firstOrCreate(
                    ['slug' => $major['slug']],
                    [
                        'title' => $major['title'],
                        'excerpt' => $major['excerpt'],
                        'body' => $major['body'],
                        'status' => 'published',
                        'type' => 'post',
                        'author_id' => $author->id,
                        'category_id' => $catProgram->id,
                        'published_at' => now(),
                    ]
                );
            }

            // 6. Seed Sample Facilities (Generic)
            $sampleFacilities = [
                [
                    'title' => 'Laboratorium Komputer & Software Studio',
                    'slug' => 'lab-komputer',
                    'excerpt' => 'Dilengkapi workstation berspesifikasi tinggi, dual-monitor setup, dan koneksi dedicated gigabit untuk pengembangan perangkat lunak.',
                    'body' => '<p>Laboratorium komputer berstandar industri dengan 36 unit workstation berkinerja tinggi, jaringan gigabit, dan software lisensi resmi untuk praktik coding dan pengembangan aplikasi.</p>',
                ],
                [
                    'title' => 'Workshop Jaringan & Fiber Optic Lab',
                    'slug' => 'lab-jaringan',
                    'excerpt' => 'Fasilitas praktik perakitan rack server, router enterprise, fusion splicer fiber optic, dan simulator jaringan BGP.',
                    'body' => '<p>Ruang praktik jaringan dengan rak server berstandar data center, perangkat router enterprise, fusion splicer OTDR, dan simulator penetration testing.</p>',
                ],
                [
                    'title' => 'Studio Animasi & Multimedia Kreatif',
                    'slug' => 'studio-multimedia',
                    'excerpt' => 'Dilengkapi drawing tablet interaktif, green screen studio, lighting kit broadcast, dan workstation rendering 3D.',
                    'body' => '<p>Studio kreatif kedap suara yang dilengkapi kamera cinema 4K, audio recording gear, green screen cyclorama, dan drawing tablet profesional.</p>',
                ],
            ];

            foreach ($sampleFacilities as $facility) {
                Content::firstOrCreate(
                    ['slug' => $facility['slug']],
                    [
                        'title' => $facility['title'],
                        'excerpt' => $facility['excerpt'],
                        'body' => $facility['body'],
                        'status' => 'published',
                        'type' => 'post',
                        'author_id' => $author->id,
                        'category_id' => $catFasilitas->id,
                        'published_at' => now(),
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
        $this->command?->info('Sarangenge Theme demo seed completed successfully.');
    }
}
