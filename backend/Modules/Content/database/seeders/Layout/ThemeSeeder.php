<?php

namespace Modules\Content\Layout\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Modules\Content\Layout\Models\Theme;
use Modules\Content\Library\Models\Category;
use Modules\Content\Publishing\Models\Content;
use Modules\Core\System\Models\User;

class ThemeSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        if (! $user) {
            return;
        }

        $this->seedThemes();
        $this->seedThemeSettings();
        $this->seedCategories($user);
        $this->seedEssentialContent($user);
    }

    private function seedThemes(): void
    {
        Theme::updateOrCreate(
            ['slug' => 'janari'],
            [
                'name' => 'Janari',
                'type' => 'frontend',
                'path' => 'janari',
                'source' => 'bundled',
                'version' => '1.0.0',
                'is_active' => true,
                'settings' => is_array(Theme::where('slug', 'janari')->first()?->settings) ? Theme::where('slug', 'janari')->first()->settings : [],
            ]
        );
    }

    private function seedThemeSettings(): void
    {
        $theme = Theme::withoutGlobalScopes()->where('slug', 'janari')->first();
        if (! $theme) {
            return;
        }

        $manifestPath = base_path('../frontend/src/modules/Content/Layout/views/themes/janari/theme.json');

        if (File::exists($manifestPath)) {
            $manifest = json_decode(File::get($manifestPath), true);
            if (! is_array($manifest)) {
                return;
            }

            $settings = is_array($theme->settings) ? $theme->settings : [];
            $schema = $manifest['settings_schema'] ?? null;
            if (is_array($schema)) {
                foreach ($schema as $key => $schemaItem) {
                    $settingKey = (string) $key;
                    if (! is_array($schemaItem)) {
                        continue;
                    }
                    if (array_key_exists('default', $schemaItem) && ! array_key_exists($settingKey, $settings)) {
                        $settings[$settingKey] = $schemaItem['default'];
                    }
                }
            }

            $settings['site_title'] ??= 'JEJAKAWAN';
            $settings['hero_title'] ??= 'JEJAKAWAN';
            $settings['hero_subtitle'] ??= 'Modern Enterprise Application';
            $settings['brand_logo'] ??= '/logo.png';
            $settings['brand_favicon'] ??= '/favicon.ico';

            $theme->update(['settings' => $settings]);
        }
    }

    private function seedCategories(User $user): void
    {
        $categories = [
            'news-announcement' => 'News & Announcements',
            'services' => 'Services',
            'company-stats' => 'Company Stats',
            'testimonials' => 'Testimonials',
            'industry-partners' => 'Industry Partners',
        ];

        foreach ($categories as $slug => $name) {
            Category::withTrashed()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'author_id' => $user->id,
                    'is_active' => true,
                ]
            );
        }
    }

    private function seedEssentialContent(User $user): void
    {
        // 1. Essential Home Page Record (Global)
        Content::withTrashed()->updateOrCreate(
            ['slug' => 'home'],
            [
                'title' => 'Home',
                'type' => 'page',
                'status' => 'published',
                'author_id' => $user->id,
                'body' => 'Welcome to Jejakawan. This is a clean production foundation.',
            ]
        );
    }
}
