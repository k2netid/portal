<?php

declare(strict_types=1);

namespace Modules\Publishing\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\System\Models\User;
use Modules\Library\Models\Category;
use Modules\Publishing\Models\Content;

class VocationalProgramsSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::query()->first();
        if (!$author) {
            $this->command?->warn('No user found to set as author.');
            return;
        }

        // 1. Create Category
        $category = Category::firstOrCreate(
            ['slug' => 'program-keahlian'],
            [
                'name' => 'Program Keahlian',
                'description' => 'Program Keahlian Vokasi & Pusat Keunggulan',
                'is_active' => true,
                'author_id' => $author->id,
            ]
        );

        // 2. Define the standard vocational majors
        $majors = [
            [
                'title' => 'Desain Pemodelan dan Informasi Bangunan (DPIB)',
                'slug' => 'dpib',
                'excerpt' => 'Program keahlian yang mempelajari ilmu tentang perencanaan bangunan, pelaksanaan pembuatan gedung, dan perbaikan gedung.',
                'intro' => 'Membekali peserta didik dengan keterampilan desain, pemodelan, dan informasi bangunan berbasis teknologi terkini.',
                'body' => '<p>Desain Pemodelan dan Informasi Bangunan (DPIB) adalah program keahlian yang mempelajari perencanaan, pelaksanaan, dan perbaikan bangunan. Siswa dibekali keterampilan menggunakan perangkat lunak seperti AutoCAD, SketchUp, dan BIM (Building Information Modeling) untuk merancang dan menghitung rencana anggaran biaya (RAB) suatu konstruksi bangunan.</p><ul><li>Menggambar struktur bangunan (2D dan 3D)</li><li>Menghitung Rencana Anggaran Biaya (RAB)</li><li>Pemetaan dan pengukuran tanah</li><li>Pengawasan pelaksanaan konstruksi</li></ul>',
            ],
            [
                'title' => 'Teknik Instalasi Tenaga Listrik (TITL)',
                'slug' => 'titl',
                'excerpt' => 'Program keahlian yang membekali peserta didik dengan keterampilan dalam perencanaan dan pemasangan instalasi penerangan dan tenaga.',
                'intro' => 'Mencetak tenaga terampil di bidang kelistrikan industri dan perumahan.',
                'body' => '<p>Teknik Instalasi Tenaga Listrik (TITL) membekali siswa dengan kompetensi dalam perencanaan, pemasangan, pengujian, dan perbaikan instalasi penerangan dan tenaga listrik baik untuk keperluan perumahan maupun industri.</p><ul><li>Instalasi penerangan jalan umum dan bangunan</li><li>Pemasangan dan perawatan motor listrik</li><li>Sistem kontrol elektromekanik dan elektronik</li><li>Perbaikan peralatan listrik rumah tangga (Home Appliance)</li></ul>',
            ],
            [
                'title' => 'Teknik Pemesinan (TPM)',
                'slug' => 'tpm',
                'excerpt' => 'Program keahlian yang fokus pada penguasaan teknik pengoperasian mesin perkakas, baik konvensional maupun CNC.',
                'intro' => 'Menghasilkan lulusan yang ahli dalam pembuatan komponen mekanik presisi.',
                'body' => '<p>Teknik Pemesinan (TPM) adalah kompetensi keahlian yang mempelajari cara memproduksi atau membuat suatu barang (komponen) dengan menggunakan mesin perkakas. Siswa dilatih untuk mengoperasikan berbagai mesin seperti mesin bubut, frais, gerinda, serta mesin CNC (Computer Numerical Control).</p><ul><li>Pengoperasian mesin bubut dan frais konvensional</li><li>Pemrograman dan pengoperasian mesin CNC</li><li>Gambar teknik manufaktur (CAD)</li><li>Kerja bangku dan pengukuran mekanik presisi</li></ul>',
            ],
            [
                'title' => 'Teknik Kendaraan Ringan Otomotif (TKRO)',
                'slug' => 'tkro',
                'excerpt' => 'Program keahlian yang mempelajari tentang perawatan, perbaikan, dan manajemen bengkel kendaraan bermotor (mobil).',
                'intro' => 'Mencetak mekanik handal yang siap bersaing di industri otomotif modern.',
                'body' => '<p>Teknik Kendaraan Ringan Otomotif (TKRO) membekali siswa dengan ilmu dan keterampilan dalam bidang perawatan dan perbaikan kendaraan bermotor, khususnya mobil. Kompetensi ini mencakup sistem mesin (engine), sistem pemindah tenaga (powertrain), sistem kelistrikan otomotif, hingga sasis dan suspensi.</p><ul><li>Pemeliharaan mesin kendaraan ringan (Engine Tune Up & Overhaul)</li><li>Sistem Injeksi Elektronik (EFI)</li><li>Pemeliharaan sasis dan pemindah tenaga</li><li>Pemeliharaan kelistrikan dan AC kendaraan</li></ul>',
            ],
            [
                'title' => 'Teknik Audio Video (TAV)',
                'slug' => 'tav',
                'excerpt' => 'Program keahlian yang fokus pada sistem elektronika, khususnya peralatan audio dan video.',
                'intro' => 'Membentuk tenaga ahli elektronika yang kompeten dalam sistem audio visual dan mikrokontroler.',
                'body' => '<p>Teknik Audio Video (TAV) merupakan program keahlian yang mempelajari tentang pembuatan, perakitan, dan perbaikan berbagai peralatan elektronika yang berkaitan dengan suara (audio) dan gambar (video). Siswa juga dibekali pengetahuan tentang mikrokontroler dan sistem otomasi elektronik.</p><ul><li>Penerapan rangkaian elektronika</li><li>Perawatan dan perbaikan peralatan audio dan video</li><li>Pembuatan desain PCB (Printed Circuit Board)</li><li>Aplikasi mikrokontroler dan sistem cerdas</li></ul>',
            ],
            [
                'title' => 'Teknik Pengelasan dan Fabrikasi Logam (TFLM)',
                'slug' => 'tflm',
                'excerpt' => 'Program keahlian yang membekali keterampilan teknik pengelasan, fabrikasi logam, dan konstruksi baja.',
                'intro' => 'Menyiapkan lulusan menjadi welder profesional dan ahli fabrikasi yang bersertifikat.',
                'body' => '<p>Teknik Pengelasan dan Fabrikasi Logam (TFLM) berfokus pada teknik penyambungan logam menggunakan berbagai metode pengelasan (SMAW, GMAW, GTAW). Siswa juga mempelajari proses fabrikasi baja, perancangan konstruksi, dan pengujian hasil las (NDT/DT).</p><ul><li>Teknik pengelasan SMAW (Shielded Metal Arc Welding)</li><li>Teknik pengelasan GMAW (Gas Metal Arc Welding)</li><li>Gambar teknik dan desain konstruksi baja</li><li>Proses fabrikasi logam dan pemotongan (cutting)</li></ul>',
            ],
        ];

        foreach ($majors as $major) {
            Content::firstOrCreate(
                ['slug' => $major['slug']],
                [
                    'title' => $major['title'],
                    'excerpt' => $major['excerpt'],
                    'intro' => $major['intro'],
                    'body' => $major['body'],
                    'status' => 'published',
                    'type' => 'page', // Using 'page' so it doesn't clutter the standard 'post' blog feed
                    'author_id' => $author->id,
                    'category_id' => $category->id,
                    'published_at' => now(),
                    'comment_status' => 'closed',
                ]
            );
        }

        $this->call(VocationalFacilitiesSeeder::class);

        $this->command?->info('Vocational programs and facilities successfully seeded!');
    }
}

// Backward compatibility alias for legacy scripts
if (!class_exists('Modules\Publishing\Database\Seeders\Smkn6ContentSeeder', false)) {
    class_alias(VocationalProgramsSeeder::class, 'Modules\Publishing\Database\Seeders\Smkn6ContentSeeder');
}
