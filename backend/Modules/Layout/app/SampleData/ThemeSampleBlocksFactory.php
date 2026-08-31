<?php

declare(strict_types=1);

namespace Modules\Layout\SampleData;

/**
 * Builds editable Visual Builder trees that mirror theme page sections,
 * so sample installs give beginners real blocks to click and edit.
 */
final class ThemeSampleBlocksFactory
{
    private int $seq = 0;

    private string $prefix = 'sample';

    /**
     * @param  array<string, mixed>  $ctx  brand, hero_title, cta_*, etc.
     * @return list<array<string, mixed>>
     */
    public function forTemplate(string $template, string $themeSlug, string $pageSlug, array $ctx = []): array
    {
        $this->seq = 0;
        $this->prefix = 'sample-'.$themeSlug.'-'.$pageSlug;

        return match ($template) {
            'home' => $this->home($ctx),
            'about' => $this->about($ctx),
            'solusi', 'services', 'products' => $this->features($ctx),
            'pricing' => $this->pricing($ctx),
            'tim', 'team' => $this->team($ctx),
            'blog' => $this->blog($ctx),
            'contact' => $this->contact($ctx),
            'achievement' => $this->achievement($ctx),
            'career', 'careers', 'career_center' => $this->career($ctx),
            default => $this->generic($ctx),
        };
    }

    /**
     * @param  array<string, mixed>  $ctx
     * @return list<array<string, mixed>>
     */
    private function home(array $ctx): array
    {
        $brand = $this->str($ctx, 'brand', 'Jejakawan');
        $heroTitle = $this->str($ctx, 'hero_title', "{$brand} editorial dan control plane untuk publikasi modern");
        $heroSubtitle = $this->str($ctx, 'hero_subtitle', "Platform {$brand} untuk mengelola konten, tema, dan distribusi multi-channel.");
        $ctaPrimary = $this->str($ctx, 'cta_primary_label', 'Jelajahi produk');
        $ctaPrimaryUrl = $this->str($ctx, 'cta_primary_url', '/solusi');
        $ctaSecondary = $this->str($ctx, 'cta_secondary_label', 'Hubungi kami');
        $ctaSecondaryUrl = $this->str($ctx, 'cta_secondary_url', '/contact');

        return [
            $this->node('hero', [
                'eyebrow' => 'PLATFORM '.strtoupper($brand),
                'title' => $heroTitle,
                'subtitle' => $heroSubtitle,
                'layout' => 'centered',
                'buttonText' => $ctaPrimary,
                'buttonUrl' => $ctaPrimaryUrl,
                'button2Text' => $ctaSecondary,
                'button2Url' => $ctaSecondaryUrl,
                'showButton1' => true,
                'showButton2' => true,
                '_label' => 'Hero',
            ]),
            $this->section([
                $this->row('1', [
                    $this->column([
                        $this->node('features', [
                            'title' => 'Produk unggulan',
                            'subtitle' => 'Modul inti '.$brand.' — klik kartu di Visual Builder untuk ubah teks.',
                            'items' => [
                                ['title' => 'Publishing', 'desc' => 'Halaman, berita, revisi, dan SEO dari satu workspace editorial.'],
                                ['title' => 'Layout & Themes', 'desc' => 'Tema frontend Janari plus Visual Builder untuk landing page.'],
                                ['title' => 'Identity', 'desc' => 'Auth, role, dan akses konsol yang terkontrol.'],
                                ['title' => 'Reach / Forms', 'desc' => 'Formulir kontak, survey, dan lead capture multi-channel.'],
                                ['title' => 'Intelligence', 'desc' => 'Insight trafik dan performa konten untuk keputusan editorial.'],
                                ['title' => 'Visual Builder', 'desc' => 'Drag-and-drop section, row, dan blok — cocok untuk pengguna awam.'],
                            ],
                            '_label' => 'Produk',
                        ]),
                    ]),
                ], ['maxWidth' => '1280px', 'margin' => ['left' => 'auto', 'right' => 'auto']]),
            ], [
                'padding' => ['top' => '72', 'bottom' => '48', 'left' => '16', 'right' => '16'],
                '_label' => 'Section produk',
            ]),
            $this->section([
                $this->row('1', [
                    $this->column([
                        $this->node('testimonials', [
                            'title' => 'Dipercaya tim produk',
                            'subtitle' => 'Cuplikan testimoni sample — edit kutipan langsung di builder.',
                            'items' => [
                                [
                                    'quote' => 'Dengan sample blok ini, editor kami langsung paham cara mengubah hero dan CTA tanpa sentuh kode.',
                                    'author' => 'Rina Wijaya',
                                    'role' => 'Content Lead',
                                ],
                                [
                                    'quote' => 'Visual Builder + tema Janari mempercepat go-live landing page kampanye.',
                                    'author' => 'Andi Pratama',
                                    'role' => 'Product Manager',
                                ],
                                [
                                    'quote' => 'Blok sample membantu onboarding kontributor baru dalam hitungan menit.',
                                    'author' => 'Siti Lestari',
                                    'role' => 'Ops Publisher',
                                ],
                            ],
                            '_label' => 'Testimoni',
                        ]),
                    ]),
                ], ['maxWidth' => '1280px', 'margin' => ['left' => 'auto', 'right' => 'auto']]),
            ], [
                'padding' => ['top' => '48', 'bottom' => '48', 'left' => '16', 'right' => '16'],
                'background' => ['type' => 'color', 'color' => 'hsl(var(--muted) / 0.35)'],
                '_label' => 'Section testimoni',
            ]),
            $this->node('cta', [
                'title' => "Mulai dengan {$brand}",
                'content' => 'Edit blok ini, ganti teks/CTA, lalu simpan — perubahan langsung ke halaman publik.',
                'buttonText' => $ctaSecondary,
                'buttonUrl' => $ctaSecondaryUrl,
                'layout' => 'inline',
                '_label' => 'CTA',
            ]),
        ];
    }

    /**
     * @param  array<string, mixed>  $ctx
     * @return list<array<string, mixed>>
     */
    private function about(array $ctx): array
    {
        $brand = $this->str($ctx, 'brand', 'Jejakawan');

        return [
            $this->section([
                $this->row('1-1', [
                    $this->column([
                        $this->text('<span style="color:hsl(var(--primary));font-weight:700;text-transform:uppercase;font-size:12px;letter-spacing:0.1em;">Profil</span>'),
                        $this->heading("Tentang {$brand}", 'h1', ['fontSize' => '40px', 'margin' => ['top' => '16', 'bottom' => '24']]),
                        $this->text('<p style="font-size:18px;line-height:1.7;color:hsl(var(--muted-foreground));">Kami membangun control plane publikasi modern — editorial, tema, formulir, dan layanan platform dalam satu aplikasi.</p><p style="line-height:1.7;color:hsl(var(--muted-foreground));">Klik judul atau paragraf di builder untuk mengganti copy halaman Tentang.</p>'),
                    ]),
                    $this->column([
                        $this->node('image', [
                            'src' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=900&q=80',
                            'alt' => "Tim {$brand}",
                            'borderRadius' => '16px',
                        ]),
                    ]),
                ], ['maxWidth' => '1280px', 'margin' => ['left' => 'auto', 'right' => 'auto'], 'gutterWidth' => '48']),
            ], [
                'padding' => ['top' => '80', 'bottom' => '80'],
                '_label' => 'About split',
            ]),
            $this->section([
                $this->row('1-1-1', [
                    $this->blurbColumn('Target', 'Visi', 'Platform publikasi yang andal untuk tim editorial dan operator produk.'),
                    $this->blurbColumn('Compass', 'Misi', 'Menyatukan CMS, tema, dan distribusi multi-channel tanpa silo.'),
                    $this->blurbColumn('Sparkles', 'Nilai', 'Kecepatan rilis, keamanan, dan pengalaman editor yang ramah awam.'),
                ], ['maxWidth' => '1280px', 'margin' => ['left' => 'auto', 'right' => 'auto'], 'gutterWidth' => '24']),
            ], [
                'padding' => ['top' => '48', 'bottom' => '80'],
                '_label' => 'Nilai',
            ]),
            $this->node('cta', [
                'title' => 'Ingin mengenal tim kami?',
                'content' => 'Lihat halaman Tim atau hubungi sales untuk demo.',
                'buttonText' => 'Lihat Tim',
                'buttonUrl' => '/tim',
                'layout' => 'inline',
                '_label' => 'CTA',
            ]),
        ];
    }

    /**
     * @param  array<string, mixed>  $ctx
     * @return list<array<string, mixed>>
     */
    private function features(array $ctx): array
    {
        $brand = $this->str($ctx, 'brand', 'Jejakawan');

        return [
            $this->node('hero', [
                'eyebrow' => 'PRODUK & SOLUSI',
                'title' => "Modul {$brand} siap pakai",
                'subtitle' => 'Publishing, Layout, Identity, Analytics — klik blok untuk ubah deskripsi produk.',
                'layout' => 'centered',
                'buttonText' => 'Minta demo',
                'buttonUrl' => '/contact',
                'showButton1' => true,
                'showButton2' => false,
                '_label' => 'Hero produk',
            ]),
            $this->section([
                $this->row('1', [
                    $this->column([
                        $this->node('features', [
                            'title' => 'Katalog modul',
                            'subtitle' => 'Enam modul inti yang sama dengan section produk di tema Vue.',
                            'items' => [
                                ['title' => 'Publishing', 'desc' => 'Halaman, berita, revisi, dan SEO dari satu workspace.'],
                                ['title' => 'Layout & Themes', 'desc' => 'Tema frontend + Visual Builder untuk landing page.'],
                                ['title' => 'Identity', 'desc' => 'Auth, role, dan akses konsol yang terkontrol.'],
                                ['title' => 'Analytics', 'desc' => 'Insight trafik dan performa konten.'],
                                ['title' => 'Integrasi API', 'desc' => 'Endpoint manage & public untuk otomasi.'],
                                ['title' => 'AI Assist', 'desc' => 'Bantuan draft dan generate blok (opsional).'],
                            ],
                            '_label' => 'Grid produk',
                        ]),
                    ]),
                ], ['maxWidth' => '1280px', 'margin' => ['left' => 'auto', 'right' => 'auto']]),
            ], [
                'padding' => ['top' => '48', 'bottom' => '72', 'left' => '16', 'right' => '16'],
                '_label' => 'Section produk',
            ]),
        ];
    }

    /**
     * @param  array<string, mixed>  $ctx
     * @return list<array<string, mixed>>
     */
    private function pricing(array $ctx): array
    {
        return [
            $this->section([
                $this->row('1', [
                    $this->column([
                        $this->heading('Harga & paket', 'h1', ['alignment' => 'center', 'fontSize' => '40px']),
                        $this->text('<p style="text-align:center;color:hsl(var(--muted-foreground));max-width:560px;margin:16px auto 0;">Ubah harga, fitur, dan CTA langsung di blok Pricing Tables.</p>'),
                    ], ['textAlign' => 'center']),
                ], ['maxWidth' => '800px', 'margin' => ['left' => 'auto', 'right' => 'auto']]),
            ], [
                'padding' => ['top' => '72', 'bottom' => '24'],
                '_label' => 'Header harga',
            ]),
            $this->node('pricingtable', [
                'items' => [
                    [
                        'title' => 'Starter',
                        'price' => '0',
                        'currency' => 'Rp',
                        'period' => '/bln',
                        'buttonText' => 'Mulai gratis',
                        'buttonUrl' => '/contact',
                        'features' => "1 situs\nTema dasar\nDukungan komunitas",
                        'isFeatured' => false,
                    ],
                    [
                        'title' => 'Professional',
                        'price' => '1.5jt',
                        'currency' => 'Rp',
                        'period' => '/bln',
                        'buttonText' => 'Pilih Pro',
                        'buttonUrl' => '/contact',
                        'features' => "Multi-tema\nVisual Builder\nDukungan prioritas\nFormulir & SEO",
                        'isFeatured' => true,
                    ],
                    [
                        'title' => 'Enterprise',
                        'price' => 'Custom',
                        'currency' => '',
                        'period' => '',
                        'buttonText' => 'Hubungi sales',
                        'buttonUrl' => '/contact',
                        'features' => "SLA 24/7\nOn-prem / private cloud\nSSO & audit\nDedicated CSM",
                        'isFeatured' => false,
                    ],
                ],
                'columns' => 3,
                '_label' => 'Tabel harga',
            ]),
            $this->node('faq', [
                'items' => [
                    ['question' => 'Bisakah saya ubah harga di sample ini?', 'answer' => 'Ya. Buka Visual Builder, pilih blok Pricing Tables, lalu edit field Plans.'],
                    ['question' => 'Apakah paket bisa ditambah?', 'answer' => 'Tambahkan item di repeater Plans pada pengaturan blok.'],
                    ['question' => 'Sample ini menimpa tema Vue?', 'answer' => 'Ya — selama builder_blocks terisi, halaman publik merender blok. Hapus semua blok untuk kembali ke template tema.'],
                ],
                '_label' => 'FAQ harga',
            ]),
        ];
    }

    /**
     * @param  array<string, mixed>  $ctx
     * @return list<array<string, mixed>>
     */
    private function team(array $ctx): array
    {
        $brand = $this->str($ctx, 'brand', 'Jejakawan');

        return [
            $this->section([
                $this->row('1', [
                    $this->column([
                        $this->heading("Tim {$brand}", 'h1', ['alignment' => 'center', 'fontSize' => '40px']),
                        $this->text('<p style="text-align:center;color:hsl(var(--muted-foreground));">Klik kartu anggota untuk ganti nama, jabatan, atau bio.</p>'),
                    ], ['textAlign' => 'center']),
                ], ['maxWidth' => '800px', 'margin' => ['left' => 'auto', 'right' => 'auto']]),
            ], ['padding' => ['top' => '72', 'bottom' => '32'], '_label' => 'Header tim']),
            $this->section([
                $this->row('1-1-1-1', [
                    $this->teamColumn('Ari Nurcahya', 'Lead Engineer'),
                    $this->teamColumn('Sarah Amira', 'Frontend Engineer'),
                    $this->teamColumn('Budi Santoso', 'Backend Engineer'),
                    $this->teamColumn('Maya Putri', 'Product Designer'),
                ], ['maxWidth' => '1280px', 'margin' => ['left' => 'auto', 'right' => 'auto'], 'gutterWidth' => '20']),
            ], ['padding' => ['top' => '24', 'bottom' => '80'], '_label' => 'Grid tim']),
        ];
    }

    /**
     * @param  array<string, mixed>  $ctx
     * @return list<array<string, mixed>>
     */
    private function blog(array $ctx): array
    {
        return [
            $this->section([
                $this->row('1', [
                    $this->column([
                        $this->text('<span style="color:hsl(var(--primary));font-weight:700;text-transform:uppercase;font-size:12px;letter-spacing:0.1em;">Berita</span>'),
                        $this->heading('Warta & update', 'h1', ['alignment' => 'center', 'fontSize' => '44px', 'margin' => ['top' => '16', 'bottom' => '16']]),
                        $this->text('<p style="text-align:center;color:hsl(var(--muted-foreground));">Header ini editable. Grid di bawah menarik artikel dari modul Publishing.</p>'),
                    ], ['textAlign' => 'center']),
                ], ['maxWidth' => '800px', 'margin' => ['left' => 'auto', 'right' => 'auto']]),
            ], [
                'padding' => ['top' => '72', 'bottom' => '40'],
                'background' => [
                    'type' => 'gradient',
                    'gradient' => 'linear-gradient(180deg, hsl(var(--primary) / 0.1) 0%, hsl(var(--background)) 100%)',
                ],
                '_label' => 'Blog header',
            ]),
            $this->section([
                $this->row('1', [
                    $this->column([
                        $this->node('blog', [
                            'layout' => 'grid',
                            'columns' => 3,
                            'postsPerPage' => 6,
                            'showFeaturedImage' => true,
                            'showExcerpt' => true,
                            '_label' => 'Daftar artikel',
                        ]),
                    ]),
                ], ['maxWidth' => '1280px', 'margin' => ['left' => 'auto', 'right' => 'auto']]),
            ], ['padding' => ['top' => '40', 'bottom' => '80'], '_label' => 'Blog grid']),
        ];
    }

    /**
     * @param  array<string, mixed>  $ctx
     * @return list<array<string, mixed>>
     */
    private function contact(array $ctx): array
    {
        $brand = $this->str($ctx, 'brand', 'Jejakawan');

        return [
            $this->section([
                $this->row('1-1', [
                    $this->column([
                        $this->heading('Kirim pesan', 'h2', ['fontSize' => '28px', 'margin' => ['bottom' => '24']]),
                        $this->node('contactform', [
                            'submitText' => 'Kirim pesan',
                            'successMessage' => 'Terima kasih! Tim kami akan menghubungi Anda.',
                            'fields' => ['name', 'email', 'subject', 'message'],
                            '_label' => 'Form kontak',
                        ]),
                    ]),
                    $this->column([
                        $this->heading("Hubungi {$brand}", 'h2', ['fontSize' => '28px', 'margin' => ['bottom' => '24']]),
                        $this->node('iconlist', [
                            'items' => [
                                ['icon' => 'Mail', 'text' => 'hello@jejakawan.com'],
                                ['icon' => 'Phone', 'text' => '+62 21 0000 0000'],
                                ['icon' => 'MapPin', 'text' => 'Jakarta, Indonesia'],
                            ],
                            'iconColor' => 'hsl(var(--primary))',
                            'gap' => '16',
                        ]),
                        $this->heading('Ikuti kami', 'h3', ['fontSize' => '18px', 'margin' => ['top' => '32', 'bottom' => '12']]),
                        $this->node('sociallinks', [
                            'links' => [
                                ['platform' => 'linkedin', 'url' => '#'],
                                ['platform' => 'twitter', 'url' => '#'],
                                ['platform' => 'instagram', 'url' => '#'],
                            ],
                        ]),
                    ]),
                ], ['maxWidth' => '1280px', 'margin' => ['left' => 'auto', 'right' => 'auto'], 'gutterWidth' => '48']),
            ], ['padding' => ['top' => '72', 'bottom' => '48'], '_label' => 'Kontak']),
            $this->node('faq', [
                'items' => [
                    ['question' => 'Berapa lama respons?', 'answer' => 'Biasanya dalam 1–2 hari kerja untuk pertanyaan umum.'],
                    ['question' => 'Apakah form ini terhubung Contact module?', 'answer' => 'Blok contactform memakai konfigurasi form aktif di sistem.'],
                ],
                '_label' => 'FAQ kontak',
            ]),
        ];
    }

    /**
     * @param  array<string, mixed>  $ctx
     * @return list<array<string, mixed>>
     */
    private function achievement(array $ctx): array
    {
        $brand = $this->str($ctx, 'brand', 'Jejakawan');

        return [
            $this->section([
                $this->row('1', [
                    $this->column([
                        $this->heading("Prestasi {$brand}", 'h1', ['alignment' => 'center', 'fontSize' => '40px']),
                        $this->text('<p style="text-align:center;color:hsl(var(--muted-foreground));">Sorotan milestone — edit angka dan deskripsi di kartu blurb.</p>'),
                    ], ['textAlign' => 'center']),
                ], ['maxWidth' => '720px', 'margin' => ['left' => 'auto', 'right' => 'auto']]),
            ], ['padding' => ['top' => '72', 'bottom' => '32']]),
            $this->section([
                $this->row('1-1-1', [
                    $this->blurbColumn('Trophy', '50+ rilis', 'Milestone produk dan patch keamanan berkala.'),
                    $this->blurbColumn('Award', 'ISO-ready', 'Praktik keamanan dan audit akses konsol.'),
                    $this->blurbColumn('Users', 'Tim dilayani', 'Publisher dan operator di berbagai industri.'),
                ], ['maxWidth' => '1100px', 'margin' => ['left' => 'auto', 'right' => 'auto'], 'gutterWidth' => '24']),
            ], ['padding' => ['top' => '24', 'bottom' => '80']]),
        ];
    }

    /**
     * @param  array<string, mixed>  $ctx
     * @return list<array<string, mixed>>
     */
    private function career(array $ctx): array
    {
        $brand = $this->str($ctx, 'brand', 'Jejakawan');

        return [
            $this->node('hero', [
                'eyebrow' => 'KARIER',
                'title' => "Bergabung dengan {$brand}",
                'subtitle' => 'Remote-friendly, fokus open source, dan engineering culture yang terbuka.',
                'buttonText' => 'Kirim CV',
                'buttonUrl' => '/contact',
                'showButton1' => true,
                'showButton2' => false,
                'minHeight' => 420,
                'gradientStart' => '#0f172a',
                'gradientEnd' => '#14532d',
                '_label' => 'Hero karier',
            ]),
            $this->section([
                $this->row('1-1-1', [
                    $this->blurbColumn('Code', 'Frontend Engineer', 'Vue 3, TypeScript, design system.'),
                    $this->blurbColumn('Server', 'Backend Engineer', 'Laravel, API, multi-tenant.'),
                    $this->blurbColumn('Headphones', 'Customer Success', 'Onboarding publisher dan SLA.'),
                ], ['maxWidth' => '1100px', 'margin' => ['left' => 'auto', 'right' => 'auto'], 'gutterWidth' => '24']),
            ], ['padding' => ['top' => '64', 'bottom' => '80'], '_label' => 'Lowongan']),
        ];
    }

    /**
     * @param  array<string, mixed>  $ctx
     * @return list<array<string, mixed>>
     */
    private function generic(array $ctx): array
    {
        $title = $this->str($ctx, 'title', 'Halaman sample');
        $excerpt = $this->str($ctx, 'excerpt', 'Edit konten halaman ini lewat Visual Builder.');

        return [
            $this->section([
                $this->row('1', [
                    $this->column([
                        $this->heading($title, 'h1', ['fontSize' => '40px', 'margin' => ['bottom' => '16']]),
                        $this->text('<p style="font-size:18px;color:hsl(var(--muted-foreground));">'.e($excerpt).'</p>'),
                        $this->text('<p>Ini halaman sample dengan blok siap edit. Tambah section, ubah teks, atau sisipkan CTA dari panel Insert.</p>'),
                    ]),
                ], ['maxWidth' => '800px', 'margin' => ['left' => 'auto', 'right' => 'auto']]),
            ], ['padding' => ['top' => '80', 'bottom' => '80']]),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $children
     * @param  array<string, mixed>  $settings
     * @return array<string, mixed>
     */
    private function section(array $children, array $settings = []): array
    {
        return $this->node('section', $settings, $children);
    }

    /**
     * @param  list<array<string, mixed>>  $children
     * @param  array<string, mixed>  $settings
     * @return array<string, mixed>
     */
    private function row(string $columns, array $children, array $settings = []): array
    {
        return $this->node('row', array_merge([
            'columns' => $columns,
            'maxWidth' => '1280px',
            'margin' => ['left' => 'auto', 'right' => 'auto'],
        ], $settings), $children);
    }

    /**
     * @param  list<array<string, mixed>>  $children
     * @param  array<string, mixed>  $settings
     * @return array<string, mixed>
     */
    private function column(array $children, array $settings = []): array
    {
        return $this->node('column', array_merge([
            'padding' => ['left' => '16', 'right' => '16'],
        ], $settings), $children);
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private function heading(string $text, string $tag = 'h2', array $extra = []): array
    {
        return $this->node('heading', array_merge([
            'text' => $text,
            'tag' => $tag,
            'fontWeight' => '700',
        ], $extra));
    }

    /**
     * @return array<string, mixed>
     */
    private function text(string $html): array
    {
        return $this->node('text', ['content' => $html]);
    }

    /**
     * @return array<string, mixed>
     */
    private function blurbColumn(string $icon, string $title, string $content): array
    {
        return $this->column([
            $this->node('blurb', [
                'icon' => $icon,
                'title' => $title,
                'content' => $content,
                'iconSize' => '28',
                'iconBgColor' => 'hsl(var(--primary) / 0.15)',
                'iconColor' => 'hsl(var(--primary))',
                'titleSize' => '18px',
                'titleWeight' => '700',
                'padding' => ['top' => '28', 'right' => '28', 'bottom' => '28', 'left' => '28'],
                'background' => 'hsl(var(--card))',
                'borderRadius' => '16px',
                'border' => '1px solid hsl(var(--border))',
            ]),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function testimonialColumn(string $quote, string $name, string $title): array
    {
        return $this->column([
            $this->node('testimonial', [
                'content' => $quote,
                'authorName' => $name,
                'authorTitle' => $title,
                'layout' => 'card',
                'padding' => ['top' => '32', 'bottom' => '32', 'left' => '24', 'right' => '24'],
            ]),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function teamColumn(string $name, string $position): array
    {
        return $this->column([
            $this->node('teammember', [
                'name' => $name,
                'position' => $position,
                'bio' => 'Klik untuk mengedit bio anggota tim sample.',
                'layout' => 'stacked',
                'alignment' => 'center',
                'imageSize' => 120,
                'imageBorderRadius' => 50,
            ]),
        ], ['textAlign' => 'center']);
    }

    /**
     * @param  array<string, mixed>  $settings
     * @param  list<array<string, mixed>>|null  $children
     * @return array<string, mixed>
     */
    private function node(string $type, array $settings = [], ?array $children = null): array
    {
        $this->seq++;
        $node = [
            'id' => $this->prefix.'-'.$this->seq,
            'type' => $type,
            'settings' => $settings,
        ];
        if ($children !== null) {
            $node['children'] = $children;
        }

        return $node;
    }

    /**
     * @param  array<string, mixed>  $ctx
     */
    private function str(array $ctx, string $key, string $default): string
    {
        $value = $ctx[$key] ?? null;

        return is_string($value) && trim($value) !== '' ? $value : $default;
    }
}
