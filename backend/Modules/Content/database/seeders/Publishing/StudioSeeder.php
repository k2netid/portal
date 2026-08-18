<?php

namespace Modules\Content\Publishing\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Content\Forms\Models\Form;
use Modules\Content\Forms\Models\FormField;
use Modules\Content\Layout\Models\Menu;
use Modules\Content\Layout\Models\Theme;
use Modules\Content\Library\Models\Category;
use Modules\Content\Publishing\Models\Content;
use Modules\Core\System\Models\User;

class StudioSeeder extends Seeder
{
    /**
     * Run the studio structure seeds.
     */
    public function run(): void
    {
        $emailRaw = config('app.super_admin_email', 'super@jejakawan.com');
        $email = is_scalar($emailRaw) ? (string) $emailRaw : 'super@jejakawan.com';
        $admin = User::where('email', $email)->first();
        if (! $admin) {
            return;
        }

        $this->command->info('Seeding hub studio data (public landing)...');
        $this->seedHubContent($admin);
        $this->seedJanariThemeDefaults();

        $this->command->info('Studio structure seeded successfully!');
    }

    private function seedHubContent(User $admin): void
    {
        // 1. Categories
        $categories = [
            ['name' => 'Uncategorized', 'slug' => 'uncategorized', 'description' => 'Default category'],
            ['name' => 'Tutorials', 'slug' => 'tutorials', 'description' => 'Helpful guides and walkthroughs'],
            ['name' => 'News', 'slug' => 'news', 'description' => 'Latest updates and announcements'],
            ['name' => 'Design', 'slug' => 'design', 'description' => 'UI/UX and design inspiration'],
            ['name' => 'Karier', 'slug' => 'careers', 'description' => 'Lowongan dan peluang di Jejakawan'],
            ['name' => 'Sorotan', 'slug' => 'highlights', 'description' => 'Pencapaian dan sorotan Jejakawan'],
        ];

        $categoryMap = [];

        foreach ($categories as $cat) {
            $category = Category::withTrashed()->updateOrCreate(
                ['slug' => $cat['slug']],
                array_merge($cat, ['author_id' => $admin->id])
            );
            if ($category->trashed()) {
                $category->restore();
            }
            $categoryMap[$cat['slug']] = $category->id;
        }

        $this->seedHubListings($admin, array_map(static fn (mixed $id): string => (string) $id, $categoryMap));

        // 2. Jejakawan Pages (Wadah)
        $pages = [
            ['title' => 'Tentang Kami', 'slug' => 'about'],
            ['title' => 'Produk', 'slug' => 'solusi'],
            ['title' => 'Tim Kami', 'slug' => 'tim'],
            ['title' => 'Harga', 'slug' => 'pricing'],
            ['title' => 'Karier', 'slug' => 'careers'],
            ['title' => 'Sorotan', 'slug' => 'highlights'],
            ['title' => 'Berita', 'slug' => 'blog'],
            ['title' => 'Kontak', 'slug' => 'contact'],
        ];

        $pageMap = [];
        foreach ($pages as $p) {
            $page = Content::updateOrCreate(
                ['slug' => $p['slug']],
                array_merge($p, [
                    'type' => 'page',
                    'status' => 'published',
                    'author_id' => $admin->id,
                    'published_at' => now(),
                ])
            );
            $pageMap[$p['slug']] = $page->id;
        }

        // 3. Header Menu
        $mainMenu = Menu::withTrashed()
            ->where('location', 'header')
            ->first();

        if (! $mainMenu) {
            $mainMenu = Menu::create([
                'name' => 'Header Primary Navigation',
                'slug' => 'menu-header-primary',
                'location' => 'header',
                'is_active' => true,
            ]);
        } elseif ($mainMenu->trashed()) {
            $mainMenu->restore();
        }

        // Cleanup items
        $mainMenu->items()->forceDelete();

        $menuItems = [
            ['title' => 'Beranda', 'url' => '/', 'sort_order' => 1, 'type' => 'custom'],
            [
                'title' => 'Tentang Kami',
                'type' => 'page',
                'target_id' => $pageMap['about'] ?? null,
                'url' => '/about',
                'sort_order' => 2,
            ],
            [
                'title' => 'Tim Kami',
                'type' => 'page',
                'target_id' => $pageMap['tim'] ?? null,
                'url' => '/tim',
                'sort_order' => 3,
                'metadata' => [
                    'title_id' => 'Tim Kami',
                    'title_en' => 'Our Team',
                ],
            ],
            [
                'title' => 'Produk',
                'type' => 'page',
                'target_id' => $pageMap['solusi'] ?? null,
                'url' => '/solusi',
                'sort_order' => 4,
                'metadata' => [
                    'title_id' => 'Produk',
                    'title_en' => 'Products',
                ],
            ],
            [
                'title' => 'Harga',
                'type' => 'page',
                'target_id' => $pageMap['pricing'] ?? null,
                'url' => '/pricing',
                'sort_order' => 5,
                'metadata' => [
                    'title_id' => 'Harga',
                    'title_en' => 'Pricing',
                ],
            ],
            [
                'title' => 'Karier',
                'type' => 'page',
                'target_id' => $pageMap['careers'] ?? null,
                'url' => '/careers',
                'sort_order' => 6,
            ],
            [
                'title' => 'Sorotan',
                'type' => 'page',
                'target_id' => $pageMap['highlights'] ?? null,
                'url' => '/highlights',
                'sort_order' => 7,
            ],
            [
                'title' => 'Berita',
                'type' => 'page',
                'target_id' => $pageMap['blog'] ?? null,
                'url' => '/blog',
                'sort_order' => 8,
            ],
            [
                'title' => 'Kontak',
                'type' => 'page',
                'target_id' => $pageMap['contact'] ?? null,
                'url' => '/contact',
                'sort_order' => 9,
            ],
        ];

        $this->seedMenuItems($mainMenu, $menuItems);

        // 4. Default public contact form
        $contactForm = Form::withTrashed()->updateOrCreate(
            ['slug' => 'contact'],
            [
                'name' => 'Formulir Kontak Publik',
                'description' => 'Formulir untuk pertanyaan umum dan kerja sama.',
                'success_message' => 'Terima kasih! Pesan Anda telah terkirim.',
                'is_active' => true,
                'author_id' => $admin->id,
                'settings' => [
                    'email_notifications' => true,
                    'notification_email' => $admin->email,
                ],
            ]
        );
        if ($contactForm->trashed()) {
            $contactForm->restore();
        }

        $fieldDefs = [
            ['name' => 'first_name', 'label' => 'Nama Depan', 'type' => 'text', 'is_required' => true, 'sort_order' => 1],
            ['name' => 'last_name', 'label' => 'Nama Belakang', 'type' => 'text', 'is_required' => true, 'sort_order' => 2],
            ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'is_required' => true, 'sort_order' => 3],
            ['name' => 'message', 'label' => 'Pesan', 'type' => 'textarea', 'is_required' => true, 'sort_order' => 4],
        ];

        foreach ($fieldDefs as $def) {
            FormField::updateOrCreate(
                ['form_id' => $contactForm->id, 'name' => $def['name']],
                array_merge($def, ['form_id' => $contactForm->id])
            );
        }
    }

    /**
     * @param  array<string, string>  $categoryMap
     */
    private function seedHubListings(User $admin, array $categoryMap): void
    {
        $careersCategoryId = $categoryMap['careers'] ?? null;
        $highlightsCategoryId = $categoryMap['highlights'] ?? null;

        $jobs = [
            [
                'slug' => 'senior-product-engineer',
                'title' => 'Senior Product Engineer',
                'excerpt' => 'Membangun fitur Publishing, Layout, dan Intelligence di control plane Jejakawan.',
                'meta' => [
                    'company' => 'Jejakawan',
                    'location' => 'Jakarta / Remote',
                    'job_type' => 'Full-time',
                ],
            ],
            [
                'slug' => 'frontend-engineer-janari',
                'title' => 'Frontend Engineer (Janari Theme)',
                'excerpt' => 'Vue 3, design system, dan pengalaman situs marketing jejakawan.com.',
                'meta' => [
                    'company' => 'Jejakawan',
                    'location' => 'Bandung / Hybrid',
                    'job_type' => 'Full-time',
                ],
            ],
            [
                'slug' => 'customer-success-hub',
                'title' => 'Customer Success — Hub',
                'excerpt' => 'Mendampingi pelanggan organization & Platform dari onboarding hingga ekspansi.',
                'meta' => [
                    'company' => 'Jejakawan',
                    'location' => 'Remote',
                    'job_type' => 'Contract',
                ],
            ],
        ];

        foreach ($jobs as $job) {
            Content::updateOrCreate(
                ['slug' => $job['slug']],
                [
                    'title' => $job['title'],
                    'excerpt' => $job['excerpt'],
                    'body' => '<p>'.$job['excerpt'].'</p>',
                    'type' => 'post',
                    'status' => 'published',
                    'author_id' => $admin->id,
                    'category_id' => $careersCategoryId,
                    'published_at' => now()->subDays(random_int(1, 14)),
                    'meta' => $job['meta'],
                ]
            );
        }

        $highlights = [
            [
                'slug' => 'jejakawan-hub-launch',
                'title' => 'Peluncuran Jejakawan Hub',
                'excerpt' => 'Satu control plane untuk katalog produk, langganan, billing, dan situs marketing.',
                'featured_image' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=800&q=80',
                'meta' => [
                    'category_slug' => 'profesional',
                    'level' => 'Milestone',
                    'winner' => 'Tim Produk Jejakawan',
                    'role' => 'Platform',
                ],
            ],
            [
                'slug' => 'janari-theme-v2',
                'title' => 'Janari Theme V2 — Jejakawan',
                'excerpt' => 'Tema marketing hub dengan customizer, binding Jejakawan, dan i18n penuh.',
                'featured_image' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=800&q=80',
                'meta' => [
                    'category_slug' => 'profesional',
                    'level' => 'Release',
                    'winner' => 'Tim Engineering',
                    'role' => 'Design System',
                ],
            ],
            [
                'slug' => 'organization-gold-tier',
                'title' => 'organization Gold — paket enterprise-ready',
                'excerpt' => 'Paket langganan dengan AI, Jejakawan, dan custom domain untuk pelanggan.',
                'featured_image' => 'https://images.unsplash.com/photo-1553877522-43269d4ea984?w=800&q=80',
                'meta' => [
                    'category_slug' => 'profesional',
                    'level' => 'Partnership',
                    'winner' => 'Tim Operasional',
                    'role' => 'Go-to-market',
                ],
            ],
        ];

        foreach ($highlights as $item) {
            Content::updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'title' => $item['title'],
                    'excerpt' => $item['excerpt'],
                    'body' => '<p>'.$item['excerpt'].'</p>',
                    'type' => 'post',
                    'status' => 'published',
                    'author_id' => $admin->id,
                    'category_id' => $highlightsCategoryId,
                    'published_at' => now()->subMonths(random_int(1, 6)),
                    'featured_image' => $item['featured_image'],
                    'meta' => $item['meta'],
                ]
            );
        }
    }

    private function seedJanariThemeDefaults(): void
    {
        $theme = Theme::withoutGlobalScopes()->where('slug', 'janari')->first();
        if (! $theme) {
            return;
        }

        $settings = is_array($theme->settings) ? $theme->settings : [];

        $defaultBindings = [
            'careers' => [
                'slots' => [
                    'jobs' => [
                        'sourceType' => 'api_posts',
                        'categoryFilter' => 'careers',
                        'limit' => 12,
                        'orderBy' => 'published_at',
                        'orderDir' => 'desc',
                        'propMapping' => [
                            'title' => 'title',
                            'company' => 'meta.company',
                            'location' => 'meta.location',
                            'type' => 'meta.job_type',
                            'excerpt' => 'excerpt',
                            'url' => 'slug',
                        ],
                    ],
                ],
            ],
            'achievements' => [
                'slots' => [
                    'list' => [
                        'sourceType' => 'api_posts',
                        'categoryFilter' => 'highlights',
                        'limit' => 12,
                        'orderBy' => 'published_at',
                        'orderDir' => 'desc',
                        'propMapping' => [
                            'title' => 'title',
                            'excerpt' => 'excerpt',
                            'description' => 'excerpt',
                            'image' => 'featured_image',
                            'winner' => 'meta.winner',
                            'role' => 'meta.role',
                            'level' => 'meta.level',
                        ],
                    ],
                ],
            ],
        ];

        $existingBindings = $settings['theme_data_bindings'] ?? [];
        if (! is_array($existingBindings)) {
            $existingBindings = [];
        }

        $settings['theme_data_bindings'] = array_replace_recursive($defaultBindings, $existingBindings);

        $settings['page_about_hero'] ??= 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=1400&q=80';
        $settings['page_about_team_image'] ??= 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=1400&q=80';

        $theme->update(['settings' => $settings]);
    }

    /**
     * Recursively seed menu items
     *
     * @param  list<array<string, mixed>>  $items
     */
    private function seedMenuItems(Menu $menu, array $items, ?string $parentId = null): void
    {
        foreach ($items as $itemData) {
            $childrenRaw = $itemData['children'] ?? null;
            /** @var list<array<string, mixed>> $children */
            $children = [];
            if (is_array($childrenRaw)) {
                foreach ($childrenRaw as $child) {
                    if (is_array($child)) {
                        $children[] = $child;
                    }
                }
            }
            unset($itemData['children']);

            $type = $itemData['type'] ?? 'custom';
            $targetType = null;

            if ($type === 'page') {
                $targetType = Content::class;
            } elseif ($type === 'category') {
                $targetType = Category::class;
            }

            $menuItem = $menu->items()->create(array_merge($itemData, [
                'parent_id' => $parentId,
                'type' => $type,
                'target_type' => $targetType,
            ]));

            if (! empty($children)) {
                $this->seedMenuItems($menu, $children, $menuItem->id);
            }
        }
    }
}
