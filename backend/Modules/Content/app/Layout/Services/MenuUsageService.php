<?php

namespace Modules\Content\Layout\Services;

use Modules\Content\Layout\Models\Menu;
use Modules\Content\Layout\Models\Theme;

class MenuUsageService
{
    /** @var array<string, string> */
    private const LOCATION_LABELS = [
        'header' => 'Header utama',
        'header_top' => 'Header atas (utility)',
        'footer' => 'Footer bawah',
        'footer_col_1' => 'Footer kolom 1',
        'footer_col_2' => 'Footer kolom 2',
        'sidebar' => 'Sidebar',
    ];

    /**
     * @return array{
     *     menu_id: string,
     *     is_active: bool,
     *     is_in_use: bool,
     *     is_served_on_public: bool,
     *     location: string|null,
     *     usages: list<array<string, mixed>>
     * }
     */
    public function analyze(Menu $menu): array
    {
        $usages = [];
        $usages = array_merge($usages, $this->themeSettingUsages($menu));
        $usages = array_merge($usages, $this->publicLocationUsage($menu));

        $isServed = $this->isServedOnPublic($menu);

        return [
            'menu_id' => $menu->id,
            'is_active' => (bool) $menu->is_active,
            'is_in_use' => $usages !== [] || $isServed,
            'is_served_on_public' => $isServed,
            'location' => $menu->location,
            'usages' => $usages,
        ];
    }

    public function isInUse(Menu $menu): bool
    {
        return $this->analyze($menu)['is_in_use'];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function themeSettingUsages(Menu $menu): array
    {
        $usages = [];

        Theme::query()
            ->where('type', 'frontend')
            ->orderByDesc('is_active')
            ->each(function (Theme $theme) use ($menu, &$usages): void {
                $settings = is_array($theme->settings) ? $theme->settings : [];

                foreach ($settings as $key => $value) {
                    if (! is_string($key) || ! str_starts_with($key, 'menu_location_')) {
                        continue;
                    }

                    if (! is_scalar($value) || ! $this->settingReferencesMenu((string) $value, $menu)) {
                        continue;
                    }

                    $slotKey = substr($key, strlen('menu_location_'));
                    $usages[] = [
                        'type' => 'theme_assignment',
                        'theme_id' => $theme->id,
                        'theme_name' => $theme->name,
                        'theme_slug' => $theme->slug,
                        'theme_is_active' => (bool) $theme->is_active,
                        'setting_key' => $key,
                        'slot_key' => $slotKey,
                        'location_label' => self::LOCATION_LABELS[$slotKey] ?? ucfirst(str_replace('_', ' ', $slotKey)),
                        'assigned_value' => (string) $value,
                    ];
                }
            });

        return $usages;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function publicLocationUsage(Menu $menu): array
    {
        if (! $menu->is_active || ! $menu->location) {
            return [];
        }

        if (! $this->isServedOnPublic($menu)) {
            return [];
        }

        $loc = $menu->location;

        return [[
            'type' => 'public_location',
            'location' => $loc,
            'location_label' => self::LOCATION_LABELS[$loc] ?? ucfirst(str_replace('_', ' ', $loc)),
            'detail' => 'Menu aktif yang dilayani API publik untuk lokasi ini.',
        ]];
    }

    private function isServedOnPublic(Menu $menu): bool
    {
        if (! $menu->is_active || ! $menu->location) {
            return false;
        }

        $served = Menu::getByLocation($menu->location);

        return $served !== null && $served->id === $menu->id;
    }

    private function settingReferencesMenu(string $value, Menu $menu): bool
    {
        $value = trim($value);

        if ($value === '' || $value === 'none') {
            return false;
        }

        return $value === $menu->id
            || $value === $menu->slug
            || ($menu->location !== null && $value === $menu->location);
    }
}
