<?php

namespace Modules\Content\Layout\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Modules\Content\Layout\Models\Menu;
use Modules\Content\Layout\Models\Theme;

class MenuLocationStandardizationSeeder extends Seeder
{
    /**
     * Recommended menu locations for Janari-like enterprise themes.
     *
     * @var array<string, array{name: string, slug: string}>
     */
    private array $standardLocations = [
        'header' => ['name' => 'Header Primary Navigation', 'slug' => 'menu-header-primary'],
        'header_top' => ['name' => 'Header Top Utility', 'slug' => 'menu-header-top'],
        'footer' => ['name' => 'Footer Bottom Links', 'slug' => 'menu-footer-bottom'],
        'footer_col_1' => ['name' => 'Footer Column 1', 'slug' => 'menu-footer-col-1'],
        'footer_col_2' => ['name' => 'Footer Column 2', 'slug' => 'menu-footer-col-2'],
        'sidebar' => ['name' => 'Sidebar Navigation', 'slug' => 'menu-sidebar'],
    ];

    /**
     * Normalize legacy location keys to canonical keys.
     *
     * @var array<string, string>
     */
    private array $locationAliases = [
        'main' => 'header',
        'main_menu' => 'header',
        'header_main' => 'header',
        'topbar' => 'header_top',
        'header-top' => 'header_top',
        'footer_main' => 'footer',
        'footer-col-1' => 'footer_col_1',
        'footer-col-2' => 'footer_col_2',
        'footer_col1' => 'footer_col_1',
        'footer_col2' => 'footer_col_2',
        'side' => 'sidebar',
    ];

    public function run(): void
    {
        $this->normalizeLegacyLocations();
        $this->ensureSingleActiveMenuPerLocation();
        $this->seedThemeMenuLocationDefaults();
    }

    private function normalizeLegacyLocations(): void
    {
        $menus = Menu::withTrashed()->get();
        foreach ($menus as $menu) {
            if (! $menu->location) {
                continue;
            }
            $normalized = $this->locationAliases[$menu->location] ?? null;
            if ($normalized && $normalized !== $menu->location) {
                $menu->update(['location' => $normalized]);
            }
        }
    }

    private function ensureSingleActiveMenuPerLocation(): void
    {
        foreach ($this->standardLocations as $location => $meta) {
            $menus = Menu::withTrashed()
                ->where('location', $location)
                ->withCount('items')
                ->orderByDesc('is_active')
                ->orderByDesc('items_count')
                ->orderBy('id')
                ->get();

            if ($menus->isEmpty()) {
                Menu::create([
                    'name' => $meta['name'],
                    'slug' => $meta['slug'],
                    'location' => $location,
                    'is_active' => true,
                    'description' => 'Auto-generated standard menu location.',
                ]);
                Cache::forget("menu_location_{$location}");

                continue;
            }

            /** @var Menu $primary */
            $primary = $menus->first(fn (Menu $m): bool => ! $m->trashed()) ?? $menus->first();

            if ($primary && $primary->trashed()) {
                $primary->restore();
            }

            if ($primary && ! $primary->is_active) {
                $primary->update(['is_active' => true]);
            }

            foreach ($menus as $menu) {
                if ($primary && $menu->id === $primary->id) {
                    continue;
                }
                if ($menu->is_active) {
                    $menu->update(['is_active' => false]);
                }
            }

            Cache::forget("menu_location_{$location}");
        }
    }

    private function seedThemeMenuLocationDefaults(): void
    {
        $activeTheme = Theme::where('type', 'frontend')->where('is_active', true)->first();
        if (! $activeTheme) {
            return;
        }

        $settings = is_array($activeTheme->settings) ? $activeTheme->settings : [];

        foreach (array_keys($this->standardLocations) as $location) {
            $settingKey = "menu_location_{$location}";
            if (! isset($settings[$settingKey]) || $settings[$settingKey] === '') {
                $settings[$settingKey] = $location;
            }
        }

        $activeTheme->update(['settings' => $settings]);
    }
}
