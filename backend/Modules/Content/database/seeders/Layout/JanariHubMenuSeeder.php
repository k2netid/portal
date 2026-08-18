<?php

declare(strict_types=1);

namespace Modules\Content\Layout\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Content\Layout\Models\Menu;
use Modules\Content\Layout\Models\MenuItem;

/** Header/footer menus for Jejakawan hub marketing site (single-site control plane). */
class JanariHubMenuSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedHeaderMenu();
        $this->seedFooterMenus();
        $this->seedHeaderTopUtility();

        $this->command->info('Janari hub menus seeded (produk, layanan, perusahaan).');
    }

    private function seedHeaderMenu(): void
    {
        $menu = Menu::updateOrCreate(
            ['location' => 'header'],
            ['name' => 'Header Primary Navigation', 'slug' => 'header-primary', 'location' => 'header']
        );

        MenuItem::where('menu_id', $menu->id)->delete();

        $order = 0;
        $this->addItem($menu->id, null, 'Beranda', '/', $order++);

        $produkId = $this->addItem($menu->id, null, 'Produk', '/solusi', $order++);
        $this->addItem($menu->id, $produkId, 'Content & Publishing', '/blog', 0);
        $this->addItem($menu->id, $produkId, 'Intelligence', '/search', 1);
        $this->addItem($menu->id, $produkId, 'Platform & Harga', '/pricing', 2);

        $layananId = $this->addItem($menu->id, null, 'Layanan', '/contact', $order++);
        $this->addItem($menu->id, $layananId, 'Forms & Reach', '/contact', 0);
        $this->addItem($menu->id, $layananId, 'Jejakawan', '/auth/console-sign-in', 1);

        $companyId = $this->addItem($menu->id, null, 'Perusahaan', '/about', $order++);
        $this->addItem($menu->id, $companyId, 'Tentang', '/about', 0);
        $this->addItem($menu->id, $companyId, 'Tim', '/tim', 1);
        $this->addItem($menu->id, $companyId, 'Karier', '/careers', 2);
        $this->addItem($menu->id, $companyId, 'Sorotan', '/highlights', 3);

        $this->addItem($menu->id, null, 'Kontak', '/contact', $order++);
    }

    private function seedHeaderTopUtility(): void
    {
        $menu = Menu::updateOrCreate(
            ['location' => 'header_top'],
            ['name' => 'Header Top Utility', 'slug' => 'header-top-utility', 'location' => 'header_top']
        );

        MenuItem::where('menu_id', $menu->id)->delete();

        $this->addItem($menu->id, null, 'Berita', '/blog', 0);
        $this->addItem($menu->id, null, 'Masuk member', '/auth/console-sign-in', 1);
    }

    private function seedFooterMenus(): void
    {
        $col1 = Menu::updateOrCreate(
            ['location' => 'footer_col_1'],
            ['name' => 'Produk & layanan', 'slug' => 'footer-products', 'location' => 'footer_col_1']
        );
        MenuItem::where('menu_id', $col1->id)->delete();
        $i = 0;
        $this->addItem($col1->id, null, 'Stack produk', '/solusi', $i++);
        $this->addItem($col1->id, null, 'Harga', '/pricing', $i++);
        $this->addItem($col1->id, null, 'Blog', '/blog', $i++);
        $this->addItem($col1->id, null, 'Pencarian', '/search', $i++);

        $col2 = Menu::updateOrCreate(
            ['location' => 'footer_col_2'],
            ['name' => 'Perusahaan & legal', 'slug' => 'footer-company', 'location' => 'footer_col_2']
        );
        MenuItem::where('menu_id', $col2->id)->delete();
        $j = 0;
        $this->addItem($col2->id, null, 'Tentang', '/about', $j++);
        $this->addItem($col2->id, null, 'Tim', '/tim', $j++);
        $this->addItem($col2->id, null, 'Kontak', '/contact', $j++);
        $this->addItem($col2->id, null, 'Privasi', '/privacy', $j++);
        $this->addItem($col2->id, null, 'Syarat', '/terms', $j++);

        $footer = Menu::updateOrCreate(
            ['location' => 'footer'],
            ['name' => 'Footer Bottom Links', 'slug' => 'footer-bottom', 'location' => 'footer']
        );
        MenuItem::where('menu_id', $footer->id)->delete();
        $this->addItem($footer->id, null, '© Jejakawan', '/', 0);
    }

    private function addItem(string $menuId, ?string $parentId, string $title, string $url, int $sortOrder): string
    {
        $item = MenuItem::create([
            'menu_id' => $menuId,
            'parent_id' => $parentId,
            'title' => $title,
            'url' => $url,
            'type' => 'custom',
            'sort_order' => $sortOrder,

        ]);

        return $item->id;
    }
}
