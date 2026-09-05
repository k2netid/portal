<?php

declare(strict_types=1);

namespace Modules\Publishing\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\System\Models\User;
use Modules\Library\Models\Category;
use Modules\Publishing\Models\Content;

class VocationalFacilitiesSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::query()->first();
        if (!$author) {
            $this->command?->warn('No user found to set as author.');
            return;
        }

        // 1. Create or retrieve Facilities Category
        $category = Category::firstOrCreate(
            ['slug' => 'fasilitas'],
            [
                'name' => 'Fasilitas & Sarana',
                'description' => 'Fasilitas dan Sarana Prasarana Praktik Kejuruan Standar Industri',
                'is_active' => true,
                'author_id' => $author->id,
            ]
        );

        // 2. Define standard vocational facilities
        $facilities = [
            [
                'title' => 'Studio Desain & BIM (DPIB)',
                'slug' => 'studio-desain-bim-dpib',
                'excerpt' => 'Laboratorium komputer arsitektur berstandar industri dengan perangkat lunak AutoCAD, SketchUp, dan Building Information Modeling (BIM).',
                'intro' => 'Fasilitas perancangan dan pemodelan konstruksi digital berbasis teknologi industri konstruksi 4.0.',
                'body' => '<p>Studio Desain Pemodelan dan Informasi Bangunan (DPIB) dilengkapi dengan workstation berperforma tinggi dan perangkat lunak standar industri seperti AutoCAD, SketchUp, Revit, dan aplikasi BIM lainnya. Fasilitas ini mendukung siswa dalam merancang bangunan 2D/3D, analisis struktur, dan penyusunan Rencana Anggaran Biaya (RAB).</p><ul><li>Workstation PC High-End untuk rendering 3D</li><li>Software resmi Autodesk AutoCAD dan Building Information Modeling (BIM)</li><li>Plotter cetak format besar A0/A1 untuk gambar kerja teknik</li><li>Alat ukur tanah digital Total Station dan Theodolite</li></ul>',
            ],
            [
                'title' => 'Bengkel CNC & Mesin Bubut Presisi (TPM)',
                'slug' => 'bengkel-cnc-mesin-presisi-tpm',
                'excerpt' => 'Bengkel manufaktur presisi yang dilengkapi mesin perkakas konvensional serta mesin perkakas Computer Numerical Control (CNC) modern.',
                'intro' => 'Pusat pelatihan manufaktur mekanik presisi dan permesinan modern sesuai standar industri manufaktur.',
                'body' => '<p>Bengkel Teknik Pemesinan (TPM) menyediakan sarana produksi lengkap untuk mencetak teknisi permesinan andal. Fasilitas mencakup deretan mesin bubut, mesin frais vertikal/horizontal, mesin gerinda datar/silindris, serta mesin CNC Milling dan CNC Lathe dengan kontroler industri.</p><ul><li>Mesin CNC Milling dan CNC Lathe standar industri</li><li>Mesin bubut dan mesin frais konvensional presisi</li><li>Alat ukur presisi digital (Micrometer, Vernier Caliper, Dial Gauge)</li><li>Area kerja bangku dan perlengkapan safety K3 manufaktur</li></ul>',
            ],
            [
                'title' => 'Laboratorium Listrik & Otomasi Industri (TITL)',
                'slug' => 'laboratorium-listrik-otomasi-titl',
                'excerpt' => 'Bengkel praktik instalasi penerangan, instalasi tenaga, panel distribusi daya, dan kontrol motor berbasis PLC.',
                'intro' => 'Ruang praktik otomasi dan sistem kelistrikan terpadu untuk kebutuhan industri dan gedung bertingkat.',
                'body' => '<p>Laboratorium Teknik Instalasi Tenaga Listrik (TITL) dirancang untuk memberikan pengalaman nyata dalam perakitan panel tenaga, sistem kendali elektromagnetik, dan sistem kontrol berbasis PLC (Programmable Logic Controller) serta SCADA.</p><ul><li>Trainer panel distribusi listrik tegangan rendah</li><li>Trainer kontrol motor listrik inverter & soft-starter</li><li>Modul Programmable Logic Controller (PLC) dan sensor industri</li><li>Peralatan keselamatan kerja kelistrikan berstandar SNI/IEC</li></ul>',
            ],
            [
                'title' => 'Bengkel Servis & Otomotif Kendaraan Ringan (TKRO)',
                'slug' => 'bengkel-otomotif-tkro',
                'excerpt' => 'Fasilitas praktik otomotif roda empat yang mencakup engine scanner EFI, spooring balancing, dan engine stand uji emisi.',
                'intro' => 'Bengkel servis modern berstandar bengkel resmi Agen Tunggal Pemegang Merek (ATPM).',
                'body' => '<p>Bengkel Teknik Kendaraan Ringan Otomotif (TKRO) disiapkan menyerupai alur kerja bengkel resmi modern (Teaching Factory). Dilengkapi dengan two-post lift, four-post lift, scanner diagnostik injeksi komputer (OBD-II), mesin tyre changer, dan gas analyzer emisi gas buang.</p><ul><li>Hydraulic Car Lift (Two-post & Four-post)</li><li>Diagnostic Scan Tool untuk sistem Electronic Fuel Injection (EFI)</li><li>Mesin Spooring 3D dan Wheel Balancing digital</li><li>Unit kendaraan uji praktik standar industri otomotif terkini</li></ul>',
            ],
            [
                'title' => 'Laboratorium Mikroelektronik & IoT (TAV)',
                'slug' => 'laboratorium-mikroelektronik-tav',
                'excerpt' => 'Ruang praktik perakitan sistem audio-video, desain PCB, serta pengembangan mikrokontroler dan otomasi cerdas (IoT).',
                'intro' => 'Pusat riset dan praktik elektronika terapan, audio profesional, dan piranti pintar Internet of Things.',
                'body' => '<p>Laboratorium Teknik Audio Video (TAV) menunjang kompetensi siswa dalam elektronika analog, digital, sistem pemrosesan sinyal audio-video, serta integrasi sistem sensor cerdas berbasis mikrokontroler (Arduino, ESP32, Raspberry Pi).</p><ul><li>Oscilloscope digital dan function generator presisi</li><li>Stasiun solder suhu terkendali dan desoldering station</li><li>Perangkat pembuatan dan etsa PCB mandiri</li><li>Trainer akustik ruangan dan sound engineering studio</li></ul>',
            ],
            [
                'title' => 'Bengkel Fabrikasi Logam & Pengelasan (TFLM)',
                'slug' => 'bengkel-fabrikasi-pengelasan-tflm',
                'excerpt' => 'Area fabrikasi logam dan pengelasan profesional (Welding) dengan sistem ventilasi aman untuk metode SMAW, GMAW, dan GTAW.',
                'intro' => 'Bengkel pengelasan berstandar sertifikasi BNSP dan uji tanpa rusak (Non-Destructive Testing).',
                'body' => '<p>Bengkel Teknik Pengelasan dan Fabrikasi Logam (TFLM) memiliki bilik-bilik las individual yang dilengkapi exhaust fan terisolasi. Siswa dilatih menguasai teknik pengelasan Shielded Metal Arc Welding (SMAW), Gas Metal Arc Welding (GMAW/MIG-MAG), dan Gas Tungsten Arc Welding (GTAW/TIG) pada berbagai posisi pengelasan.</p><ul><li>Bilik pengelasan individual dengan ventilasi sirkulasi udara aman</li><li>Mesin las inverter multifungsi (SMAW, GMAW, GTAW)</li><li>Mesin pemotong plat hidrolik (Shearing) dan mesin bending plat</li><li>Peralatan uji ketahanan dan pengujian hasil las</li></ul>',
            ],
            [
                'title' => 'Gedung Center of Excellence (CoE) & Smart Classroom',
                'slug' => 'gedung-center-of-excellence-coe',
                'excerpt' => 'Ruang pembelajaran kolaboratif modern dengan layar interaktif, audio konferensi, dan konektivitas internet berkecepatan tinggi.',
                'intro' => 'Gedung representatif program SMK Pusat Keunggulan untuk seminar industri dan kolaborasi pembelajaran digital.',
                'body' => '<p>Gedung Center of Excellence (CoE) merupakan sarana representatif sebagai SMK Pusat Keunggulan. Dilengkapi dengan smart board interaktif, sistem video conference untuk kuliah tamu praktisi industri, serta tata ruang fleksibel untuk pembelajaran berbasis proyek (Project-Based Learning).</p><ul><li>Smart Interactive Display Touchscreen 86 inci</li><li>High-Speed Gigabit WiFi & LAN connectivity</li><li>Ruang seminar akustik berkapasitas 150 peserta</li><li>Area kolaborasi Teaching Factory bersama mitra dunia usaha & industri</li></ul>',
            ],
            [
                'title' => 'Perpustakaan Digital & Learning Commons',
                'slug' => 'perpustakaan-digital-learning-commons',
                'excerpt' => 'Pusat sumber belajar dengan ribuan koleksi e-book kejuruan, jurnal industri, dan bilik riset mandiri bagi siswa dan guru.',
                'intro' => 'Layanan literasi digital dan ruang belajar mandiri yang nyaman dengan fasilitas komputasi modern.',
                'body' => '<p>Perpustakaan vokasi memadukan koleksi literatur cetak dan akses repositori digital (e-library). Tersedia komputer terminal riset, akses jurnal teknik, ruang baca ber-AC, dan area diskusi yang mendukung literasi vokasi berkelanjutan.</p><ul><li>Katalog digital (OPAC) dan platform e-book vokasi</li><li>Bilik komputer riset internet mandiri untuk siswa</li><li>Koleksi buku referensi teknik, standar industri, dan umum</li><li>Ruang baca tenang ber-AC dengan pencahayaan ramah mata</li></ul>',
            ],
        ];

        foreach ($facilities as $facility) {
            Content::updateOrCreate(
                ['slug' => $facility['slug']],
                [
                    'title' => $facility['title'],
                    'excerpt' => $facility['excerpt'],
                    'intro' => $facility['intro'],
                    'body' => $facility['body'],
                    'status' => 'published',
                    'type' => 'page', // Using 'page' so it integrates with clean CMS routing
                    'author_id' => $author->id,
                    'category_id' => $category->id,
                    'published_at' => now(),
                    'comment_status' => 'closed',
                    'meta' => [
                        'category_slug' => 'fasilitas',
                        'sample_theme' => 'sarangenge',
                    ],
                ]
            );
        }

        $this->command?->info('Vocational facilities category and 8 content items successfully seeded!');
    }
}

// Backward compatibility alias for legacy scripts
if (!class_exists('Modules\Publishing\Database\Seeders\Smkn6FacilitiesSeeder', false)) {
    class_alias(VocationalFacilitiesSeeder::class, 'Modules\Publishing\Database\Seeders\Smkn6FacilitiesSeeder');
}
