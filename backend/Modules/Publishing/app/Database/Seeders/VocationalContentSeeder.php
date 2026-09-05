<?php

declare(strict_types=1);

namespace Modules\Publishing\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\System\Models\User;
use Modules\Library\Models\Category;
use Modules\Publishing\Models\Content;

class VocationalContentSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::query()->first();
        if (!$author) {
            $this->command?->warn('No user found to set as author.');
            return;
        }

        // ==========================================
        // 1. KATEGORI & KONTEN: PRESTASI SEKOLAH
        // ==========================================
        $catPrestasi = Category::firstOrCreate(
            ['slug' => 'prestasi'],
            [
                'name' => 'Prestasi & Penghargaan',
                'description' => 'Dokumentasi prestasi, penghargaan, dan kejuaraan siswa serta sekolah di tingkat regional, nasional, dan internasional.',
                'is_active' => true,
                'author_id' => $author->id,
            ]
        );

        $prestasiItems = [
            [
                'title' => 'Medali Emas LKS Tingkat Nasional Bidang CAD Building',
                'slug' => 'medali-emas-lks-nasional-cad-building',
                'excerpt' => 'Menyabet medali emas dengan skor tertinggi dalam desain permodelan konstruksi 3D dan Building Information Modeling.',
                'intro' => 'Capaian gemilang kontingen kejuruan dalam ajang bergengsi Lomba Kompetensi Siswa (LKS) Tingkat Nasional.',
                'body' => '<p>Prestasi membanggakan kembali ditorehkan oleh siswa dalam ajang Lomba Kompetensi Siswa (LKS) Tingkat Nasional Bidang Mechanical Engineering CAD / CAD Building. Melalui persiapan intensif bersama guru pembina dan instruktur industri, inovasi gambar kerja teknik berstandar internasional berhasil meraih penilaian sempurna dari dewan juri industri manufaktur dan konstruksi.</p><p>Keberhasilan ini membuktikan efektivitas pembelajaran berbasis Teaching Factory dan kemitraan kurikulum selaras DUDI yang diterapkan secara konsisten di sekolah.</p>',
                'meta' => [
                    'year' => '2026',
                    'level' => 'Tingkat Nasional',
                    'category' => 'sains',
                    'category_name' => 'LKS SMK Nasional',
                    'winner' => 'Ahmad Fadhil Prasetya',
                    'student' => 'Ahmad Fadhil Prasetya',
                ],
            ],
            [
                'title' => 'Juara 1 National Autonomous Robotics & AI Championship',
                'slug' => 'juara-1-national-robotics-ai-championship',
                'excerpt' => 'Inovasi robot penyortir logistik cerdas dengan integrasi Computer Vision dan algoritma machine learning.',
                'intro' => 'Tim robotika sekolah berhasil mengungguli puluhan perwakilan sekolah dan politeknik dari seluruh penjuru Indonesia.',
                'body' => '<p>Tim Robotika sekolah menorehkan prestasi gemilang dengan meraih Juara 1 pada National Autonomous Robotics & AI Championship 2026. Robot cerdas beroda Omni-wheel yang dirancang mampu melakukan pemilahan palet barang secara presisi dengan panduan kamera sensor AI berkecepatan tinggi.</p><p>Proyek ini merupakan kolaborasi lintas program keahlian elektronika, ketenagalistrikan, dan pemesinan presisi.</p>',
                'meta' => [
                    'year' => '2026',
                    'level' => 'Tingkat Nasional',
                    'category' => 'robotika',
                    'category_name' => 'Kontes Robotika Nasional',
                    'winner' => 'Tim Robotika Alpha',
                    'student' => 'Tim Robotika Alpha',
                ],
            ],
            [
                'title' => 'Overall Best Speaker National Schools Debating Championship',
                'slug' => 'overall-best-speaker-nsdc-debating',
                'excerpt' => 'Menjuarai kompetisi debat parlemen bahasa Inggris tingkat nasional mewakili kontingen provinsi Jawa Barat.',
                'intro' => 'Kemampuan komunikasi verbal internasional dan argumentasi analitis kritis siswa diakui di level tertinggi kompetisi debat nasional.',
                'body' => '<p>Perwakilan klub English Debate sekolah sukses menyabet predikat Overall Best Speaker pada ajang National Schools Debating Championship (NSDC) 2026. Mosi debat mencakup isu transisi energi hijau global, kecerdasan buatan, dan ketahanan ekonomi regional.</p><p>Prestasi ini mencerminkan program penguatan bahasa asing komunikatif yang diterapkan sebagai bagian dari kurikulum kejuruan global.</p>',
                'meta' => [
                    'year' => '2026',
                    'level' => 'Tingkat Nasional',
                    'category' => 'bahasa',
                    'category_name' => 'Debat Bahasa Inggris (NSDC)',
                    'winner' => 'Nadia Putri Anindita',
                    'student' => 'Nadia Putri Anindita',
                ],
            ],
            [
                'title' => 'Juara 1 DBL Basketball League Jawa Barat',
                'slug' => 'juara-1-dbl-basketball-league-jabar',
                'excerpt' => 'Tim basket sekolah berhasil mempertahankan gelar juara dengan rekor tak terkalahkan sepanjang babak penyisihan hingga final.',
                'intro' => 'Semangat sportivitas, daya juang tinggi, dan kekompakan tim mengantarkan piala DBL kembali ke almamater tercinta.',
                'body' => '<p>Tim Basket Putra berhasil menorehkan sejarah baru dalam kompetisi basket antarpelajar terbesar DBL Seri Jawa Barat dengan keluar sebagai Juara 1 Utama. Di partai pamungkas, tim tampil solid dengan strategi transisi bertahan dan menyerang yang sangat dinamis.</p><p>Dukungan fasilitas sport hall standar nasional di sekolah menjadi modal utama latihan intensif para atlet muda kita.</p>',
                'meta' => [
                    'year' => '2025',
                    'level' => 'Tingkat Provinsi',
                    'category' => 'olahraga',
                    'category_name' => 'Kejuaraan Basket Antarpelajar',
                    'winner' => 'Tim Basket Putra',
                    'student' => 'Tim Basket Putra',
                ],
            ],
            [
                'title' => 'Medali Perak OSN Bidang Astronomi & Fisika Terapan',
                'slug' => 'medali-perak-osn-fisika-terapan',
                'excerpt' => 'Kompetisi sains bergengsi yang diselenggarakan oleh Pusat Prestasi Nasional Kemendikbudristek RI.',
                'intro' => 'Penguasaan sains fundamental dan fisika analitis siswa vokasi mampu bersaing dengan sekolah umum terkemuka se-Indonesia.',
                'body' => '<p>Dalam ajang Olimpiade Sains Nasional (OSN) Bidang Fisika dan Astronomi Terapan, siswa sekolah berhasil mengamankan medali perak setelah menyelesaikan soal eksperimental komputasi dan analisis gerak mekanika antariksa.</p><p>Prestasi ini membuktikan bahwa siswa pendidikan kejuruan memiliki basis penguasaan sains kuantitatif yang kokoh untuk menunjang teknologi industri lanjutan.</p>',
                'meta' => [
                    'year' => '2025',
                    'level' => 'Tingkat Nasional',
                    'category' => 'sains',
                    'category_name' => 'Olimpiade Sains (OSN)',
                    'winner' => 'Muhammad Rizky Ramadhan',
                    'student' => 'Muhammad Rizky Ramadhan',
                ],
            ],
            [
                'title' => 'Gold Diploma International Choir Championship Singapura',
                'slug' => 'gold-diploma-international-choir-singapura',
                'excerpt' => 'Paduan suara sekolah mempersembahkan aransemen musik vokal daerah kontemporer nusantara di kancah internasional.',
                'intro' => 'Apresiasi tertinggi juri mancanegara atas harmoni vokal, dinamika paduan suara, dan pelestarian budaya tradisional nusantara.',
                'body' => '<p>Paduan Suara Gita Pelajar sukses mengharumkan nama bangsa dengan menyabet Gold Diploma pada festival paduan suara internasional di Esplanade Singapura. Tim membawakan repertoar lagu daerah yang dipadukan dengan teknik vokal klasik modern.</p><p>Kemenangan ini menjadi bukti bahwa pembinaan minat seni budaya di sekolah kejuruan berkembang dengan sangat subur dan berkelas dunia.</p>',
                'meta' => [
                    'year' => '2025',
                    'level' => 'Tingkat Internasional',
                    'category' => 'bahasa',
                    'category_name' => 'International Choir Festival',
                    'winner' => 'Paduan Suara Gita Pelajar',
                    'student' => 'Paduan Suara Gita Pelajar',
                ],
            ],
        ];

        foreach ($prestasiItems as $item) {
            Content::updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'title' => $item['title'],
                    'excerpt' => $item['excerpt'],
                    'intro' => $item['intro'],
                    'body' => $item['body'],
                    'status' => 'published',
                    'type' => 'page',
                    'author_id' => $author->id,
                    'category_id' => $catPrestasi->id,
                    'published_at' => now(),
                    'comment_status' => 'closed',
                    'meta' => $item['meta'],
                ]
            );
        }

        // ==========================================
        // 2. KATEGORI & KONTEN: DEWAN GURU & STAF
        // ==========================================
        $catGuru = Category::firstOrCreate(
            ['slug' => 'guru-staf'],
            [
                'name' => 'Dewan Guru & Tenaga Kependidikan',
                'description' => 'Profil dewan guru pendidik, instruktur kejuruan, dan tenaga kependidikan berdedikasi.',
                'is_active' => true,
                'author_id' => $author->id,
            ]
        );

        $guruItems = [
            [
                'title' => 'Drs. H. Rahmat Sudrajat, M.Pd.',
                'slug' => 'drs-h-rahmat-sudrajat-mpd',
                'excerpt' => 'Kepala Sekolah · Manajemen Pendidikan Kejuruan Vokasi',
                'intro' => 'Pemimpin visioner pengembang ekosistem SMK Pusat Keunggulan dan kemitraan strategis DUDI nasional.',
                'body' => '<p>Drs. H. Rahmat Sudrajat, M.Pd. mengemban amanah sebagai Kepala Sekolah dengan pengalaman lebih dari 25 tahun di dunia pendidikan kejuruan. Beliau memimpin transformasi digital tata kelola sekolah dan penyelarasan kurikulum berbasis kebutuhan nyata dunia industri.</p>',
                'meta' => [
                    'role' => 'Kepala Sekolah',
                    'subject' => 'Manajemen Pendidikan & Kepemimpinan Vokasi',
                    'education' => 'S2 Manajemen Pendidikan UPI',
                    'nip' => '196803151993031004',
                ],
            ],
            [
                'title' => 'Dra. Hj. Nurul Hidayati, M.Si.',
                'slug' => 'dra-hj-nurul-hidayati-msi',
                'excerpt' => 'Wakil Kepala Sekolah Bidang Kurikulum & Penjaminan Mutu',
                'intro' => 'Pendidik berprestasi di bidang kurikulum berbasis capaian pembelajaran kompetensi keahlian.',
                'body' => '<p>Dra. Hj. Nurul Hidayati, M.Si. bertanggung jawab atas perancangan struktur kurikulum merdeka, sinkronisasi kompetensi industri, dan monitoring asesmen kompetensi siswa berstandar BNSP.</p>',
                'meta' => [
                    'role' => 'Wakil Kepala Sekolah Bidang Kurikulum',
                    'subject' => 'Kurikulum & Sains Terapan',
                    'education' => 'S2 Ilmu Kimia ITB',
                    'nip' => '197108221997022001',
                ],
            ],
            [
                'title' => 'Bambang Irawan, S.Kom., M.T.',
                'slug' => 'bambang-irawan-skom-mt',
                'excerpt' => 'Koordinator STEM, Bengkel Digital & Laboratorium AI',
                'intro' => 'Praktisi teknologi informasi dan instruktur robotika otonom tingkat kejuaraan nasional.',
                'body' => '<p>Bambang Irawan membina divisi riset teknologi cerdas, sistem otomasi industri, dan komputasi awan. Beliau aktif memfasilitasi sertifikasi internasional bagi siswa kejuruan teknologi rekayasa.</p>',
                'meta' => [
                    'role' => 'Koordinator STEM & Lab AI',
                    'subject' => 'Informatika, IoT & Robotika Terapan',
                    'education' => 'S2 Teknik Elektro ITB',
                    'nip' => '198205102008011007',
                ],
            ],
            [
                'title' => 'Sarah Jenkins, B.Ed., M.A.',
                'slug' => 'sarah-jenkins-bed-ma',
                'excerpt' => 'Koordinator Bahasa Internasional & Cambridge Assessment Specialist',
                'intro' => 'Instruktur bahasa Inggris kejuruan dan pembimbing komunikasi bisnis internasional siswa.',
                'body' => '<p>Sarah Jenkins memimpin penguatan literasi bahasa asing untuk membekali calon lulusan menghadapi magang industri multinasional dan beasiswa internasional.</p>',
                'meta' => [
                    'role' => 'Cambridge Coordinator',
                    'subject' => 'English for Vocational & Technical Communication',
                    'education' => 'University of Cambridge (UK)',
                    'nip' => '198711042015042001',
                ],
            ],
            [
                'title' => 'Ustadz Ahmad Fauzi, Lc., M.Ag.',
                'slug' => 'ustadz-ahmad-fauzi-lc-mag',
                'excerpt' => 'Koordinator Pembinaan Karakter & Keagamaan',
                'intro' => 'Pembina integritas budi pekerti, akhlak mulia, dan etika profesi kerja bagi seluruh civitas.',
                'body' => '<p>Ustadz Ahmad Fauzi membimbing program pembentukan karakter Profil Pelajar Pancasila, kedisiplinan ibadah, serta konseling kerohanian bagi para peserta didik.</p>',
                'meta' => [
                    'role' => 'Koordinator Keagamaan & Karakter',
                    'subject' => 'Pendidikan Agama & Etika Profesi',
                    'education' => 'Universitas Al-Azhar Kairo',
                    'nip' => '197904122005011003',
                ],
            ],
            [
                'title' => 'Dewi Sartika, S.Pd., Gr.',
                'slug' => 'dewi-sartika-spd-gr',
                'excerpt' => 'Guru Pembina Olimpiade Sains & Matematika Rekayasa',
                'intro' => 'Pendidik profesional bersertifikasi yang melahirkan juara-juara olimpiade sains terapan.',
                'body' => '<p>Dewi Sartika mengampu mata pelajaran matematika teknik terapan, melatih siswa dalam pemecahan masalah analitis, kalkulus terapan manufaktur, dan statistik data industri.</p>',
                'meta' => [
                    'role' => 'Guru Pembina Olimpiade',
                    'subject' => 'Matematika Terapan & Logika Rekayasa',
                    'education' => 'S1 Pendidikan Matematika UPI',
                    'nip' => '198909142014022002',
                ],
            ],
            [
                'title' => 'Rudi Hartono, S.Or.',
                'slug' => 'rudi-hartono-sor',
                'excerpt' => 'Wakil Kepala Sekolah Bidang Kesiswaan & Ketahanan Fisik',
                'intro' => 'Pembina ketarunaan, kedisiplinan siswa, dan koordinator prestasi ekstrakurikuler olahraga.',
                'body' => '<p>Rudi Hartono mengarahkan program ketarunaan kejuruan, membentuk ketahanan fisik, kepemimpinan, dan integritas calon teknisi yang tangguh menghadapi atmosfer kerja industri.</p>',
                'meta' => [
                    'role' => 'Wakil Kepala Sekolah Bidang Kesiswaan',
                    'subject' => 'Pendidikan Jasmani, Olahraga & Kesehatan',
                    'education' => 'S1 Ilmu Keolahragaan UNJ',
                    'nip' => '198302192009031005',
                ],
            ],
            [
                'title' => 'Laksmi Paramita, S.Psi., M.Psi.',
                'slug' => 'laksmi-paramita-spsi-mpsi',
                'excerpt' => 'Koordinator Bimbingan Konseling & Bursa Kerja Khusus (BKK)',
                'intro' => 'Psikolog pendidikan dan fasilitator kesiapan karir industri serta studi lanjut alumni.',
                'body' => '<p>Laksmi Paramita mendampingi proses adaptasi psikologis siswa, bimbingan penjurusan, pemetaan bakat minat, serta asesmen kesiapan wawancara kerja kejuruan bersama mitra industri.</p>',
                'meta' => [
                    'role' => 'Koordinator BK & Karir BKK',
                    'subject' => 'Bimbingan Karir & Psikologi Industri',
                    'education' => 'S2 Psikologi Profesi UNPAD',
                    'nip' => '198606282011012009',
                ],
            ],
        ];

        foreach ($guruItems as $item) {
            Content::updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'title' => $item['title'],
                    'excerpt' => $item['excerpt'],
                    'intro' => $item['intro'],
                    'body' => $item['body'],
                    'status' => 'published',
                    'type' => 'page',
                    'author_id' => $author->id,
                    'category_id' => $catGuru->id,
                    'published_at' => now(),
                    'comment_status' => 'closed',
                    'meta' => $item['meta'],
                ]
            );
        }

        // ==========================================
        // 3. KATEGORI & KONTEN: BKK & KARIR ALUMNI
        // ==========================================
        $catKarir = Category::firstOrCreate(
            ['slug' => 'karir-alumni'],
            [
                'name' => 'Bursa Kerja Khusus (BKK) & Karir Alumni',
                'description' => 'Jejaring alumni kejuruan, kisah sukses karir di industri manufaktur & teknologi, serta studi lanjut.',
                'is_active' => true,
                'author_id' => $author->id,
            ]
        );

        $karirItems = [
            [
                'title' => 'dr. Farhan Maulana — Mengabdi dari Pondasi Sains Vokasi',
                'slug' => 'farhan-maulana-karir-alumni',
                'excerpt' => 'Alumni 2018 · Fakultas Kedokteran UI · Dokter Residen & Peneliti Medis',
                'intro' => 'Kisah sukses alumni yang berhasil menembus fakultas kedokteran favorit berkat kedisiplinan riset laboratorium sejak bangku sekolah.',
                'body' => '<p>Disiplin riset laboratorium, analisis data eksperimen, dan bimbingan guru yang suportif meletakkan pondasi kokoh bagi perjalanan karir akademis hingga menjadi dokter residen di rumah sakit rujukan nasional.</p>',
                'meta' => [
                    'name' => 'dr. Farhan Maulana',
                    'grad' => 'Alumni 2018',
                    'campus' => 'Fakultas Kedokteran UI',
                    'role' => 'Dokter Residen & Peneliti Medis',
                    'story' => 'Pendidikan disiplin riset dan laboratorium meletakkan pondasi kuat bagi karir profesional saya hingga di dunia medis.',
                ],
            ],
            [
                'title' => 'Annisa Larasati, S.T., M.Sc. — AI Engineer di Industri Teknologi Global',
                'slug' => 'annisa-larasati-ai-engineer-alumni',
                'excerpt' => 'Alumni 2019 · TU Delft Belanda · AI Engineer di Perusahaan Otomasi Internasional',
                'intro' => 'Berangkat dari klub coding dan kejuruan rekayasa sekolah, meraih beasiswa penuh master di Eropa.',
                'body' => '<p>Annisa membuktikan bahwa lulusan pendidikan vokasi memiliki daya saing komputasi global. Dukungan laboratorium komputer modern dan mentoring guru mengantarkannya meraih beasiswa di Delft University of Technology.</p>',
                'meta' => [
                    'name' => 'Annisa Larasati, S.T., M.Sc.',
                    'grad' => 'Alumni 2019',
                    'campus' => 'TU Delft (Belanda)',
                    'role' => 'AI Engineer di Perusahaan Teknologi Global',
                    'story' => 'Dukungan klub coding sekolah dan bimbingan guru bahasa mempermudah langkah saya meraih beasiswa master di Eropa.',
                ],
            ],
            [
                'title' => 'Dimas Wicaksono, S.E. — Pelopor Startup Agritech Nasional',
                'slug' => 'dimas-wicaksono-founder-agritech',
                'excerpt' => 'Alumni 2020 · Fakultas Ekonomika dan Bisnis UGM · Co-Founder Startup Agritech',
                'intro' => 'Menghubungkan teknologi sensor IoT pertanian dengan rantai pasok pangan modern nusantara.',
                'body' => '<p>Jiwa kewirausahaan dan kepekaan memecahkan masalah industri yang ditanamkan selama program Teaching Factory menginspirasi Dimas mendirikan platform agritech yang kini bermitra dengan ribuan petani lokal.</p>',
                'meta' => [
                    'name' => 'Dimas Wicaksono, S.E.',
                    'grad' => 'Alumni 2020',
                    'campus' => 'Fakultas Ekonomika dan Bisnis UGM',
                    'role' => 'Co-Founder Startup Agritech',
                    'story' => 'Jiwa kepemimpinan dan empati sosial yang ditanamkan selama bersekolah menjadi kompas utama dalam mendirikan usaha mandiri.',
                ],
            ],
            [
                'title' => 'Reza Aditya, A.Md.T. — Supervisor Manufaktur Presisi PT Astra Otoparts',
                'slug' => 'reza-aditya-lead-cnc-manufaktur',
                'excerpt' => 'Alumni 2021 · Politeknik Manufaktur Astra · Supervisor Produksi & CNC Lead',
                'intro' => 'Direkrut langsung sebelum wisuda berkat sertifikasi kompetensi mesin CNC berstandar industri.',
                'body' => '<p>Reza direkrut melalui bursa kerja khusus (BKK) sekolah pada semester akhir. Pengalaman memprogram mesin bubut CNC dan standar K3 yang telah mendarah daging membuatnya cepat dipercaya memimpin tim lini produksi otomotif.</p>',
                'meta' => [
                    'name' => 'Reza Aditya, A.Md.T.',
                    'grad' => 'Alumni 2021',
                    'campus' => 'PT Astra Otoparts Tbk',
                    'role' => 'Supervisor Produksi CNC & Manufaktur',
                    'story' => 'Fasilitas bengkel CNC di sekolah benar-benar identik dengan mesin di pabrik, sehingga transisi kerja saya sangat mulus tanpa kendala.',
                ],
            ],
        ];

        foreach ($karirItems as $item) {
            Content::updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'title' => $item['title'],
                    'excerpt' => $item['excerpt'],
                    'intro' => $item['intro'],
                    'body' => $item['body'],
                    'status' => 'published',
                    'type' => 'page',
                    'author_id' => $author->id,
                    'category_id' => $catKarir->id,
                    'published_at' => now(),
                    'comment_status' => 'closed',
                    'meta' => $item['meta'],
                ]
            );
        }

        // ==========================================
        // 4. KATEGORI & KONTEN: EKSTRAKURIKULER
        // ==========================================
        $catEkskul = Category::firstOrCreate(
            ['slug' => 'ekstrakurikuler'],
            [
                'name' => 'Ekstrakurikuler & Pengembangan Diri',
                'description' => 'Wadah minat, bakat, kepemimpinan, riset teknologi cerdas, seni budaya, dan olahraga siswa.',
                'is_active' => true,
                'author_id' => $author->id,
            ]
        );

        $ekskulItems = [
            [
                'title' => 'Klub Robotika & AI Vokasi',
                'slug' => 'klub-robotika-ai-vokasi',
                'excerpt' => 'Sains & Teknologi · Perancangan robot otonom dan sistem sensor cerdas berbasis mikroprosesor.',
                'intro' => 'Mempelajari pemrograman mikrokontroler, computer vision, dan perancangan mekanik presisi robotika.',
                'body' => '<p>Klub Robotika & AI membekali anggota dengan kemampuan merancang robot cerdas otonom dan IoT. Kegiatan rutin mencakup perancangan hardware, coding C++/Python, serta simulasi arena kompetisi nasional.</p>',
                'meta' => [
                    'category' => 'Sains & Teknologi',
                    'icon' => 'Bot',
                ],
            ],
            [
                'title' => 'English Debate & MUN Society',
                'slug' => 'english-debate-mun-society',
                'excerpt' => 'Bahasa & Diplomasi · Melatih keterampilan public speaking, debat parlemen, dan negosiasi internasional.',
                'intro' => 'Wadah pembentukan generasi komunikator global yang berwawasan luas dan berfikir kritis.',
                'body' => '<p>Klub ini mempersiapkan siswa berkompetisi dalam National Schools Debating Championship (NSDC) serta Model United Nations (MUN) dengan penguasaan isu-isu sosial dan teknologi global terkini.</p>',
                'meta' => [
                    'category' => 'Bahasa & Diplomasi',
                    'icon' => 'Globe2',
                ],
            ],
            [
                'title' => 'Pramuka Garuda Kejuruan',
                'slug' => 'pramuka-garuda-kejuruan',
                'excerpt' => 'Kepemimpinan & Karakter · Pembentukan ketangguhan moral, kepanduan, dan kepedulian sosial.',
                'intro' => 'Ekstrakurikuler wajib pembentuk karakter satya darma yang siap mengabdi bagi masyarakat.',
                'body' => '<p>Ambalan Pramuka sekolah menanamkan kemandirian, pertolongan pertama lapangan (PMR/SAR), survival alam terbuka, serta bakti sosial kemasyarakatan berkelanjutan.</p>',
                'meta' => [
                    'category' => 'Kepemimpinan',
                    'icon' => 'ShieldAlert',
                ],
            ],
            [
                'title' => 'Paskibra Satuan Utama',
                'slug' => 'paskibra-satuan-utama',
                'excerpt' => 'Kedisiplinan & Baris-Berbaris · Menanamkan jiwa patriotisme, baris-berbaris presisi, dan integritas tinggi.',
                'intro' => 'Satuan pengibar bendera pusaka yang mengutamakan kedisiplinan militer halus dan kekompakan tim.',
                'body' => '<p>Paskibra melatih postur, ketahanan fisik prima, tata upacara bendera kenegaraan, serta rutin mengutus perwakilan di tingkat kota, provinsi, hingga istana negara.</p>',
                'meta' => [
                    'category' => 'Kedisiplinan',
                    'icon' => 'Award',
                ],
            ],
            [
                'title' => 'Orkestra & Paduan Suara Gita',
                'slug' => 'orkestra-paduan-suara-gita',
                'excerpt' => 'Seni Musik & Vokal · Eksplorasi musikalitas harmoni vokal nusantara dan instrumen orkestra.',
                'intro' => 'Menumbuhkan kepekaan estetika dan harmoni melalui aransemen musik vokal dan instrumen.',
                'body' => '<p>Klub seni musik rutin tampil dalam upacara resmi kenegaraan sekolah, festival paduan suara internasional, serta pementasan karya orkestra kolaboratif.</p>',
                'meta' => [
                    'category' => 'Seni Musik',
                    'icon' => 'Music',
                ],
            ],
            [
                'title' => 'Fotografi & Jurnalistik Multimedia',
                'slug' => 'fotografi-jurnalistik-multimedia',
                'excerpt' => 'Media Kreatif · Dokumentasi visual, sinematografi, liputan berita sekolah, dan desain konten publik.',
                'intro' => 'Melatih insan kreatif muda menguasai teknik kamera profesional dan etika jurnalistik era digital.',
                'body' => '<p>Anggota bertanggung jawab atas penerbitan majalah dinding digital sekolah, liputan video kegiatan tahunan, podcast kejuruan, dan pameran fotografi karya siswa.</p>',
                'meta' => [
                    'category' => 'Media & Kreatif',
                    'icon' => 'Camera',
                ],
            ],
        ];

        foreach ($ekskulItems as $item) {
            Content::updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'title' => $item['title'],
                    'excerpt' => $item['excerpt'],
                    'intro' => $item['intro'],
                    'body' => $item['body'],
                    'status' => 'published',
                    'type' => 'page',
                    'author_id' => $author->id,
                    'category_id' => $catEkskul->id,
                    'published_at' => now(),
                    'comment_status' => 'closed',
                    'meta' => $item['meta'],
                ]
            );
        }

        // ==========================================
        // 5. KATEGORI & KONTEN: AGENDA & PENGUMUMAN
        // ==========================================
        $catAgenda = Category::firstOrCreate(
            ['slug' => 'agenda'],
            [
                'name' => 'Agenda & Pengumuman Sekolah',
                'description' => 'Kalender akademik, jadwal asesmen kompetensi, pameran inovasi, dan siaran pers resmi sekolah.',
                'is_active' => true,
                'author_id' => $author->id,
            ]
        );

        $agendaItems = [
            [
                'title' => 'Pembukaan Pendaftaran Siswa Baru (PPDB) Jalur Prestasi & Uji Kompetensi',
                'slug' => 'pembukaan-ppdb-jalur-prestasi-vokasi',
                'excerpt' => 'Pendaftaran daring resmi melalui portal sekolah terintegrasi dinas pendidikan provinsi dengan fasilitas beasiswa kejuruan.',
                'intro' => 'Penerimaan peserta didik baru tahun ajaran 2026/2027 telah dibuka bagi lulusan SMP/MTs berprestasi.',
                'body' => '<p>SMK Pusat Keunggulan membuka penerimaan peserta didik baru jalur prestasi akademik, non-akademik, afirmasi, dan tes kompetensi kejuruan. Calon pendaftar dapat memilih 6 program keahlian unggulan berstandar industri.</p>',
                'meta' => [
                    'day' => '15',
                    'month' => 'Okt',
                    'year' => '2026',
                    'badge' => 'PPDB 2026',
                    'subtitle' => 'Gelombang 1 Dibuka',
                    'venue' => 'Aula Utama & Online',
                    'link_text' => 'Info Syarat →',
                    'link_url' => '/contact',
                ],
            ],
            [
                'title' => 'Gelar Karya Inovasi Vokasi P5 Expo & Demo Teknologi 4.0',
                'slug' => 'gelar-karya-inovasi-p5-expo-vokasi',
                'excerpt' => 'Unjuk gelar proyek penelitian sains, demo robotika autonomous, permesinan CNC presisi, dan pameran desain arsitektur BIM.',
                'intro' => 'Pameran akbar karya inovatif siswa kolaborasi bersama mitra dunia usaha dan industri (DUDI).',
                'body' => '<p>P5 Expo menghadirkan ratusan produk inovatif buatan siswa dari seluruh program keahlian: mulai dari purwarupa mobil listrik hemat energi, modul IoT hidroponik, panel otomasi industri cerdas, hingga maket gedung ramah lingkungan.</p>',
                'meta' => [
                    'day' => '28',
                    'month' => 'Okt',
                    'year' => '2026',
                    'badge' => 'Pameran Karya',
                    'subtitle' => 'P5 Expo 2026',
                    'venue' => 'Gedung CoE & Sport Hall',
                    'link_text' => 'Jadwal Acara →',
                    'link_url' => '/blog',
                ],
            ],
            [
                'title' => 'Parent-Teacher Synergy: Kemitraan Orang Tua & Sekolah Menyongsong Karir Era Digital',
                'slug' => 'parent-teacher-synergy-era-digital',
                'excerpt' => 'Diskusi interaktif bersama pakar psikologi pendidikan tentang ketahanan mental dan fokus belajar generasi muda di era AI.',
                'intro' => 'Membangun sinergi harmonis antara keluarga dan lingkungan sekolah demi kesuksesan tumbuh kembang peserta didik.',
                'body' => '<p>Seminar parenting tahunan mengundang seluruh wali murid untuk berdialog langsung dengan dewan guru dan psikolog industri mengenai bimbingan karir, etika pemanfaatan teknologi kecerdasan buatan, dan peluang studi vokasi internasional.</p>',
                'meta' => [
                    'day' => '10',
                    'month' => 'Nov',
                    'year' => '2026',
                    'badge' => 'Parenting',
                    'subtitle' => 'Seminar Orang Tua Siswa',
                    'venue' => 'Auditorium & Zoom Meeting',
                    'link_text' => 'Registrasi →',
                    'link_url' => '/blog',
                ],
            ],
            [
                'title' => 'Bursa Kerja Khusus (BKK) Job Fair & Walk-in Interview Bersama 30+ Industri',
                'slug' => 'job-fair-vokasi-industri-mitra-2026',
                'excerpt' => 'Rekrutmen langsung calon teknisi dan tenaga ahli muda oleh puluhan perusahaan mitra manufaktur, otomotif, dan rekayasa konstruksi.',
                'intro' => 'Ajang penyerapan tenaga kerja kejuruan dan penandatanganan kontrak kerja sebelum wisuda kelulusan.',
                'body' => '<p>BKK Job Fair memfasilitasi wawancara kerja langsung, psikotes on-site, dan seleksi magang industri ke luar negeri. Terbuka bagi siswa kelas XII yang akan lulus dan para alumni dari seluruh angkatan.</p>',
                'meta' => [
                    'day' => '25',
                    'month' => 'Nov',
                    'year' => '2026',
                    'badge' => 'Job Fair BKK',
                    'subtitle' => 'Rekrutmen Industri',
                    'venue' => 'Plaza Bengkel Praktik',
                    'link_text' => 'Daftar Hadir →',
                    'link_url' => '/contact',
                ],
            ],
        ];

        foreach ($agendaItems as $item) {
            Content::updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'title' => $item['title'],
                    'excerpt' => $item['excerpt'],
                    'intro' => $item['intro'],
                    'body' => $item['body'],
                    'status' => 'published',
                    'type' => 'page',
                    'author_id' => $author->id,
                    'category_id' => $catAgenda->id,
                    'published_at' => now(),
                    'comment_status' => 'closed',
                    'meta' => $item['meta'],
                ]
            );
        }

        // ==========================================
        // 6. KATEGORI & KONTEN: TESTIMONI
        // ==========================================
        $catTesti = Category::firstOrCreate(
            ['slug' => 'testimoni'],
            [
                'name' => 'Testimoni Sivitas & Mitra Industri',
                'description' => 'Apresiasi dan testimoni pengalaman nyata dari siswa, orang tua, alumni, dan mitra dunia usaha dunia industri (DUDI).',
                'is_active' => true,
                'author_id' => $author->id,
            ]
        );

        $testiItems = [
            [
                'title' => 'Ir. Hendra Kusuma — Orang Tua Siswa Kelas X',
                'slug' => 'testimoni-ir-hendra-kusuma',
                'excerpt' => 'Orang Tua Siswa Kelas X',
                'intro' => 'Apresiasi orang tua terhadap transparansi komunikasi dan pembinaan adab sekolah vokasi.',
                'body' => '<p>Sekolah tidak hanya mengajarkan akademik tinggi dan keahlian teknik modern, tapi sangat memperhatikan adab dan mental anak. Komunikasi guru dengan orang tua sangat transparan, hangat, dan solutif.</p>',
                'meta' => [
                    'name' => 'Ir. Hendra Kusuma',
                    'role' => 'Orang Tua Siswa Kelas X',
                    'quote' => 'Sekolah tidak hanya mengajarkan keahlian teknik tinggi, tapi sangat memperhatikan adab dan mental anak. Komunikasi guru dengan orang tua sangat transparan dan hangat.',
                ],
            ],
            [
                'title' => 'Siti Sarah Nurhaliza — Alumni 2024 & Mahasiswi Kedokteran',
                'slug' => 'testimoni-siti-sarah-nurhaliza',
                'excerpt' => 'Alumni 2024 · Mahasiswi Kedokteran UI',
                'intro' => 'Pengalaman alumni berprestasi mengenai pondasi disiplin riset sains di sekolah.',
                'body' => '<p>Bimbingan riset sains dan pembinaan guru menjadi modal paling berharga saat saya menempuh seleksi masuk perguruan tinggi negeri dan adaptasi dunia kedokteran.</p>',
                'meta' => [
                    'name' => 'Siti Sarah Nurhaliza',
                    'role' => 'Alumni 2024 · Mahasiswi Kedokteran UI',
                    'quote' => 'Bimbingan riset sains dan pembinaan guru menjadi modal paling berharga saat saya menempuh seleksi masuk perguruan tinggi dan beradaptasi dengan dunia klinis.',
                ],
            ],
            [
                'title' => 'Rian Pratama — Ketua OSIS Periode 2025/2026',
                'slug' => 'testimoni-rian-pratama',
                'excerpt' => 'Ketua OSIS Periode 2025/2026',
                'intro' => 'Kesan perwakilan pengurus kesiswaan terhadap kebebasan berinovasi dan fasilitas praktik lengkap.',
                'body' => '<p>Fasilitas bengkel modern, AI lab, dan ruang diskusi di sekolah membuat kami bebas berinovasi dan percaya diri bersaing di kancah nasional.</p>',
                'meta' => [
                    'name' => 'Rian Pratama',
                    'role' => 'Ketua OSIS Periode 2025/2026',
                    'quote' => 'Fasilitas bengkel modern, AI lab, dan ruang diskusi di sekolah membuat kami bebas berinovasi dan percaya diri bersaing di kancah nasional.',
                ],
            ],
            [
                'title' => 'Ir. Budi Hartanto, M.M. — PT Astra Otoparts Tbk (Mitra Industri DUDI)',
                'slug' => 'testimoni-pt-astra-mitra-dudi',
                'excerpt' => 'Head of People & Talent Acquisition · Mitra Industri DUDI',
                'intro' => 'Pengakuan dari pihak industri manufaktur nasional atas kesiapan kerja dan kedisiplinan lulusan.',
                'body' => '<p>Lulusan sekolah ini memiliki etos kerja luar biasa, penguasaan SOP K3 yang matang, serta kemampuan adaptasi mesin berteknologi tinggi yang memuaskan lini produksi kami.</p>',
                'meta' => [
                    'name' => 'Ir. Budi Hartanto, M.M.',
                    'role' => 'Head of Talent · PT Astra Otoparts Tbk (Mitra DUDI)',
                    'quote' => 'Lulusan sekolah ini memiliki etos kerja luar biasa, penguasaan SOP K3 yang matang, serta kemampuan adaptasi mesin manufaktur berteknologi tinggi yang memuaskan.',
                ],
            ],
        ];

        foreach ($testiItems as $item) {
            Content::updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'title' => $item['title'],
                    'excerpt' => $item['excerpt'],
                    'intro' => $item['intro'],
                    'body' => $item['body'],
                    'status' => 'published',
                    'type' => 'page',
                    'author_id' => $author->id,
                    'category_id' => $catTesti->id,
                    'published_at' => now(),
                    'comment_status' => 'closed',
                    'meta' => $item['meta'],
                ]
            );
        }

        $this->command?->info('Vocational 6 categories and all rich dynamic content successfully seeded!');
    }
}
