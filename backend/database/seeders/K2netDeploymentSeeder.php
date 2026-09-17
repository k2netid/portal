<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\System\Models\Setting;
use Modules\Layout\Database\Seeders\Themes\LayungThemeDemoSeeder;
use Modules\Layout\Models\Theme;
use Modules\Layout\Services\ThemeCacheService;

class K2netDeploymentSeeder extends Seeder
{
    /**
     * Seed K2NET deployment identity settings into the database.
     * Builds upon the Layung theme foundation and applies official K2Net branding.
     */
    public function run(): void
    {
        $this->command?->info('Seeding K2NET deployment identity configuration...');

        // 1. Run baseline Layung Theme demo seeder
        $this->call(LayungThemeDemoSeeder::class);

        // 2. Override with official K2NET System Identity
        Setting::set('site_name', 'K2NET', 'string', 'general');
        Setting::set('site_title', 'K2NET', 'string', 'general');
        Setting::set('site_tagline', 'Internet Service Provider & Managed Service Provider — Bandung, Jawa Barat', 'string', 'general');
        Setting::set('site_description', 'K2NET menyediakan layanan konektivitas internet cepat berkecepatan tinggi dan solusi managed service andal untuk korporasi dan bisnis.', 'string', 'general');
        Setting::set('contact_email', 'info@k2net.id', 'string', 'general');
        Setting::set('admin_email', 'admin@k2net.id', 'string', 'general');
        Setting::set('brand_logo', '/logofull_k2net.png', 'image', 'brand');

        // 3. Active Theme Configuration (Layung with K2Net corporate details)
        $layung = Theme::where('slug', 'layung')->first();

        if ($layung) {
            $currentSettings = is_array($layung->settings) ? $layung->settings : [];

            $k2netThemeSettings = [
                'site_title' => 'K2NET',
                'company_legal_name' => 'PT Kirana Karina Network',
                'company_as_name' => 'IDNIC-K2NET-ID',
                'contact_email' => 'info@k2net.id',
                'cs_email' => 'cs@k2net.id',
                'sales_email' => 'sales@k2net.id',
                'billing_email' => 'billing@k2net.id',
                'brand_logo' => '/logofull_k2net.png',
                'tokopedia_url' => 'https://tokopedia.link/k2net',
                'shopee_url' => 'https://shopee.co.id/k2net',
                'company_phone' => '022-87309999',
                'company_whatsapp' => '6281122334455',
                'default_theme_mode' => 'dark',
                'default_site_locale' => 'id',
            ];

            $layung->settings = array_merge($currentSettings, $k2netThemeSettings);
            $layung->is_active = true;
            $layung->save();

            // Set Layung as primary active theme in settings
            Setting::set('theme_active', 'layung', 'string', 'layout');

            if (class_exists(ThemeCacheService::class)) {
                try {
                    app(ThemeCacheService::class)->clear();
                } catch (\Throwable $e) {
                    // Ignore cache clear error
                }
            }
        }

        // 4. Ensure K2NET Categories and categorize all posts and pages
        $author = \Modules\Core\System\Models\User::query()->first();
        if ($author) {
            $categories = [
                // Post categories (matching Layung theme blog navigation)
                [
                    'name' => 'Infrastruktur & Internet',
                    'slug' => 'infrastruktur',
                    'description' => 'Teknologi jaringan fiber optic backbone, peering, dan routing AS153992.',
                    'color' => '#0284c7',
                    'sort_order' => 1,
                ],
                [
                    'name' => 'Operasional & Security',
                    'slug' => 'security',
                    'description' => 'Dukungan Network Operations Center (NOC) 24/7, mitigasi insiden, dan keamanan jaringan.',
                    'color' => '#059669',
                    'sort_order' => 2,
                ],
                [
                    'name' => 'Managed Services & Solusi IT',
                    'slug' => 'cloud',
                    'description' => 'Solusi IT terkelola, monitoring jaringan, server, dan operasional lingkungan kerja.',
                    'color' => '#7c3aed',
                    'sort_order' => 3,
                ],
                [
                    'name' => 'Pemeliharaan & Jaringan',
                    'slug' => 'maintenance',
                    'description' => 'Jadwal pemeliharaan backbone terjadwal, upgrade kapasitas, dan info operasional.',
                    'color' => '#d97706',
                    'sort_order' => 4,
                ],
                [
                    'name' => 'Promo & Penawaran',
                    'slug' => 'promo',
                    'description' => 'Promo paket dedicated internet, diskon aktivasi, dan penawaran korporat.',
                    'color' => '#e11d48',
                    'sort_order' => 5,
                ],
                // Page categories
                [
                    'name' => 'Layanan & Solusi',
                    'slug' => 'layanan',
                    'description' => 'Paket konektivitas internet (ISP), managed services (MSP), dan skema harga.',
                    'color' => '#0284c7',
                    'sort_order' => 10,
                ],
                [
                    'name' => 'Profil Perusahaan',
                    'slug' => 'profil',
                    'description' => 'Profil organisasi PT Kirana Karina Network, tim teknis, SLA, dan karir.',
                    'color' => '#475569',
                    'sort_order' => 11,
                ],
                [
                    'name' => 'Portal & Informasi',
                    'slug' => 'informasi',
                    'description' => 'Halaman beranda utama, kontak NOC, dan indeks warta informasi.',
                    'color' => '#64748b',
                    'sort_order' => 12,
                ],
            ];

            $catMap = [];
            foreach ($categories as $cat) {
                $category = \Modules\Library\Models\Category::withTrashed()->where('slug', $cat['slug'])->first();
                if ($category) {
                    $category->restore();
                    $category->update([
                        'name' => $cat['name'],
                        'description' => $cat['description'],
                        'color' => $cat['color'],
                        'sort_order' => $cat['sort_order'],
                        'is_active' => true,
                        'author_id' => $author->id,
                    ]);
                } else {
                    $category = \Modules\Library\Models\Category::create([
                        'name' => $cat['name'],
                        'slug' => $cat['slug'],
                        'description' => $cat['description'],
                        'color' => $cat['color'],
                        'sort_order' => $cat['sort_order'],
                        'is_active' => true,
                        'author_id' => $author->id,
                    ]);
                }
                $catMap[$cat['slug']] = $category->id;
            }

            // Categorize Posts
            $postCategoryMapping = [
                'k2net-coverage-bandung' => 'infrastruktur',
                'k2net-asn-idnic' => 'infrastruktur',
                'k2net-msp-layanan-terkelola' => 'cloud',
                'k2net-noc-dukungan' => 'security',
                'k2net-maintenance-window' => 'maintenance',
                'k2net-promo-dedicated-internet' => 'promo',
                'portal-isp-coverage-bandung' => 'infrastruktur',
                'portal-isp-asn-idnic' => 'infrastruktur',
                'portal-isp-msp-layanan-terkelola' => 'cloud',
                'portal-isp-noc-dukungan' => 'security',
                'portal-isp-maintenance-window' => 'maintenance',
                'portal-isp-promo-dedicated-internet' => 'promo',
            ];

            foreach ($postCategoryMapping as $postSlug => $catSlug) {
                if (isset($catMap[$catSlug])) {
                    \Modules\Publishing\Models\Content::where('slug', $postSlug)->update([
                        'category_id' => $catMap[$catSlug],
                    ]);
                }
            }

            // Clean up old solusi page shell and rename tim to team
            \Modules\Publishing\Models\Content::where('slug', 'solusi')->delete();
            \Modules\Publishing\Models\Content::where('slug', 'tim')->update(['slug' => 'team']);

            // Categorize Pages
            $pageCategoryMapping = [
                'services' => 'layanan',
                'pricing' => 'layanan',
                'pricing-isp' => 'layanan',
                'pricing-msp' => 'layanan',
                'about' => 'profil',
                'team' => 'profil',
                'achievement' => 'profil',
                'careers' => 'profil',
                'home' => 'informasi',
                'contact' => 'informasi',
                'blog' => 'informasi',
            ];

            foreach ($pageCategoryMapping as $pageSlug => $catSlug) {
                if (isset($catMap[$catSlug])) {
                    \Modules\Publishing\Models\Content::where('slug', $pageSlug)->update([
                        'category_id' => $catMap[$catSlug],
                    ]);
                }
            }

            // Remove unused generic demo categories
            \Modules\Library\Models\Category::whereIn('slug', ['layanan-internet', 'managed-services'])->delete();

            // Recalculate content_count for all categories
            foreach ($catMap as $catId) {
                $count = \Modules\Publishing\Models\Content::where('category_id', $catId)
                    ->whereNull('deleted_at')
                    ->count();
                \Modules\Library\Models\Category::where('id', $catId)->update(['content_count' => $count]);
            }

            // 5. Ensure K2NET Tags and tag all posts and pages
            $tags = [
                ['name' => 'Dedicated Internet', 'slug' => 'dedicated-internet', 'description' => 'Layanan internet dedicated 1:1 tanpa pembagian bandwidth.'],
                ['name' => 'Broadband', 'slug' => 'broadband', 'description' => 'Koneksi internet broadband berkecepatan tinggi untuk bisnis.'],
                ['name' => 'Fiber Optic', 'slug' => 'fiber-optic', 'description' => 'Infrastruktur kabel serat optik dengan latensi rendah.'],
                ['name' => 'BGP Routing', 'slug' => 'bgp-routing', 'description' => 'Protokol routing dinamis antar Autonomous System.'],
                ['name' => 'AS153992', 'slug' => 'as153992', 'description' => 'Nomor Autonomous System resmi K2NET di IDNIC.'],
                ['name' => 'IDNIC', 'slug' => 'idnic', 'description' => 'Indonesia Network Information Center.'],
                ['name' => 'NOC 24/7', 'slug' => 'noc-247', 'description' => 'Layanan Network Operations Center siaga 24 jam sehari.'],
                ['name' => 'SLA 99.999%', 'slug' => 'sla', 'description' => 'Jaminan ketersediaan layanan jaringan berbasis Service Level Agreement.'],
                ['name' => 'Managed Service', 'slug' => 'managed-service', 'description' => 'Layanan pengelolaan IT dan pemeliharaan infrastruktur.'],
                ['name' => 'IT Support', 'slug' => 'it-support', 'description' => 'Dukungan teknis dan troubleshooting sistem kerja harian.'],
                ['name' => 'Maintenance', 'slug' => 'maintenance', 'description' => 'Aktivitas pemeliharaan preventif dan peningkatan jaringan.'],
                ['name' => 'Backbone', 'slug' => 'backbone', 'description' => 'Jaringan tulang punggung transmisi data berkapasitas besar.'],
                ['name' => 'Promo Bisnis', 'slug' => 'promo-bisnis', 'description' => 'Promo khusus instalasi dan paket bundling internet bisnis.'],
                ['name' => 'Bandung Raya', 'slug' => 'bandung-raya', 'description' => 'Cakupan area operasional Bandung, Cimahi, dan sekitarnya.'],
            ];

            $tagMap = [];
            foreach ($tags as $t) {
                $tag = \Modules\Library\Models\Tag::withTrashed()->where('slug', $t['slug'])->first();
                if ($tag) {
                    $tag->restore();
                    $tag->update([
                        'name' => $t['name'],
                        'description' => $t['description'],
                        'type' => 'content',
                        'author_id' => null,
                    ]);
                } else {
                    $tag = \Modules\Library\Models\Tag::create([
                        'name' => $t['name'],
                        'slug' => $t['slug'],
                        'description' => $t['description'],
                        'type' => 'content',
                        'author_id' => null,
                        'usage_count' => 0,
                    ]);
                }
                $tagMap[$t['slug']] = $tag;
            }

            // Tag mapping for posts and pages
            $contentTags = [
                // Posts
                'k2net-coverage-bandung' => ['bandung-raya', 'fiber-optic', 'broadband', 'dedicated-internet'],
                'portal-isp-coverage-bandung' => ['bandung-raya', 'fiber-optic', 'broadband', 'dedicated-internet'],
                'k2net-asn-idnic' => ['as153992', 'idnic', 'bgp-routing', 'backbone'],
                'portal-isp-asn-idnic' => ['as153992', 'idnic', 'bgp-routing', 'backbone'],
                'k2net-msp-layanan-terkelola' => ['managed-service', 'it-support', 'noc-247'],
                'portal-isp-msp-layanan-terkelola' => ['managed-service', 'it-support', 'noc-247'],
                'k2net-noc-dukungan' => ['noc-247', 'sla', 'it-support'],
                'portal-isp-noc-dukungan' => ['noc-247', 'sla', 'it-support'],
                'k2net-maintenance-window' => ['maintenance', 'backbone', 'noc-247'],
                'portal-isp-maintenance-window' => ['maintenance', 'backbone', 'noc-247'],
                'k2net-promo-dedicated-internet' => ['promo-bisnis', 'dedicated-internet', 'bandung-raya'],
                'portal-isp-promo-dedicated-internet' => ['promo-bisnis', 'dedicated-internet', 'bandung-raya'],
                // Pages
                'services' => ['dedicated-internet', 'fiber-optic', 'broadband', 'managed-service'],
                'pricing' => ['dedicated-internet', 'broadband', 'managed-service'],
                'pricing-isp' => ['dedicated-internet', 'broadband'],
                'pricing-msp' => ['managed-service', 'it-support'],
                'achievement' => ['sla', 'as153992', 'idnic'],
                'team' => ['noc-247', 'it-support'],
                'careers' => ['noc-247', 'it-support'],
            ];

            foreach ($contentTags as $contentSlug => $tagSlugs) {
                $content = \Modules\Publishing\Models\Content::where('slug', $contentSlug)->first();
                if ($content) {
                    $tagIds = [];
                    foreach ($tagSlugs as $ts) {
                        if (isset($tagMap[$ts])) {
                            $tagIds[] = $tagMap[$ts]->id;
                        }
                    }
                    $content->tags()->sync($tagIds);
                }
            }

            // Recalculate usage_count on all tags
            foreach ($tagMap as $tag) {
                $count = $tag->contents()->count();
                $tag->update(['usage_count' => $count]);
            }

            // 6. Ensure Navigation Links for Services (ISP & MSP) and Team
            $headerMenus = \Modules\Layout\Models\Menu::where('location', 'header')->get();
            foreach ($headerMenus as $hm) {
                $serviceItem = \Modules\Layout\Models\MenuItem::where('menu_id', $hm->id)
                    ->whereNull('parent_id')
                    ->where(function ($q): void {
                        $q->where('title', 'ilike', '%Layanan%')
                            ->orWhere('title', 'ilike', '%Services%')
                            ->orWhere('url', 'like', '/services%')
                            ->orWhere('url', 'like', '/solusi%');
                    })
                    ->first();

                if ($serviceItem) {
                    $serviceItem->update([
                        'url' => '/services#isp',
                        'sort_order' => 2,
                    ]);

                    // Submenu Internet -> /services#isp
                    \Modules\Layout\Models\MenuItem::where('menu_id', $hm->id)
                        ->where('parent_id', $serviceItem->id)
                        ->where(function ($q): void {
                            $q->where('title', 'ilike', '%Internet%')
                                ->orWhere('url', 'like', '%pricing/isp%')
                                ->orWhere('url', 'like', '%solusi#isp%');
                        })
                        ->update(['url' => '/services#isp']);

                    // Submenu Managed Services -> /services#msp
                    \Modules\Layout\Models\MenuItem::where('menu_id', $hm->id)
                        ->where('parent_id', $serviceItem->id)
                        ->where('title', 'ilike', '%Managed Services%')
                        ->update(['url' => '/services#msp']);

                    // Remove SLA from Services dropdown
                    \Modules\Layout\Models\MenuItem::where('menu_id', $hm->id)
                        ->where('parent_id', $serviceItem->id)
                        ->where(function ($q): void {
                            $q->where('title', 'ilike', '%SLA%')
                                ->orWhere('url', '/achievement');
                        })
                        ->delete();
                }

                // Ensure Team is in Header Menu
                $teamItem = \Modules\Layout\Models\MenuItem::where('menu_id', $hm->id)
                    ->whereNull('parent_id')
                    ->where(function ($q): void {
                        $q->where('title', 'ilike', '%Tim%')
                            ->orWhere('title', 'ilike', '%Team%')
                            ->orWhere('url', '/team')
                            ->orWhere('url', '/tim');
                    })
                    ->first();

                if ($teamItem) {
                    $teamItem->update([
                        'title' => 'Tim',
                        'url' => '/team',
                        'sort_order' => 4,
                        'metadata' => array_merge($teamItem->metadata ?? [], ['title_en' => 'Team']),
                    ]);
                } else {
                    \Modules\Layout\Models\MenuItem::create([
                        'menu_id' => $hm->id,
                        'parent_id' => null,
                        'title' => 'Tim',
                        'url' => '/team',
                        'type' => 'custom',
                        'sort_order' => 4,
                        'open_in_new_tab' => false,
                        'metadata' => ['title_en' => 'Team'],
                    ]);
                }

                // Adjust Berita to sort_order 5 and Kontak to sort_order 6
                \Modules\Layout\Models\MenuItem::where('menu_id', $hm->id)
                    ->whereNull('parent_id')
                    ->where(function ($q): void {
                        $q->where('title', 'ilike', '%Berita%')
                            ->orWhere('url', '/blog');
                    })
                    ->update(['sort_order' => 5]);

                \Modules\Layout\Models\MenuItem::where('menu_id', $hm->id)
                    ->whereNull('parent_id')
                    ->where(function ($q): void {
                        $q->where('title', 'ilike', '%Kontak%')
                            ->orWhere('url', '/contact');
                    })
                    ->update(['sort_order' => 6]);
            }

            // Update footer menus referencing old urls
            \Modules\Layout\Models\MenuItem::where('url', 'like', '/solusi%')->update(['url' => '/services#msp']);
            \Modules\Layout\Models\MenuItem::where('url', '/tim')->update(['url' => '/team']);
        }

        $this->command?->info('K2NET deployment configuration, categorization & tags seeded successfully.');
    }
}
