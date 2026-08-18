<?php

declare(strict_types=1);

namespace Modules\Content\Layout\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Content\Layout\Models\Theme;
use Modules\Content\Layout\Services\ThemeCacheService;

final class JanariHubThemeSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $theme = Theme::withoutGlobalScopes()->where('slug', 'janari')->first();
        if (! $theme) {
            $this->command->warn('Janari theme not found; skip hub theme settings.');

            return;
        }

        $settings = is_array($theme->settings) ? $theme->settings : [];

        $hubDefaults = [
            'site_title' => 'Jejakawan',
            'site_tagline' => 'Control plane untuk konten, tema, intelligence, dan platform komersial.',
            'site_description' => 'jejakawan.com — hub publik Jejakawan: Publishing, Janari, Forms, Intelligence, langganan, dan Jejakawan.',
            'hero_title' => 'JEJAKAWAN',
            'hero_title_en' => 'JEJAKAWAN',
            'hero_title_id' => 'JEJAKAWAN',
            'hero_subtitle' => 'Jejakawan editorial, tema Janari, dan operasi platform dalam satu hub.',
            'hero_subtitle_en' => 'Editorial Jejakawan, Janari theme, and platform operations in one hub.',
            'hero_subtitle_id' => 'Jejakawan editorial, tema Janari, dan operasi platform dalam satu hub.',
            'hero_badge' => 'PLATFORM JEJAKAWAN',
            'hero_badge_en' => 'JEJAKAWAN HUB',
            'hero_badge_id' => 'HUB JEJAKAWAN',

            'cta_badge' => 'SIAP JALANKAN HUB',
            'cta_badge_id' => 'SIAP JALANKAN DI HUB',
            'cta_badge_en' => 'READY ON ONE HUB',
            'cta_title' => 'MULAI DENGAN JEJAKAWAN',
            'cta_title_id' => 'MULAI DENGAN JEJAKAWAN',
            'cta_title_en' => 'START WITH JEJAKAWAN',
            'cta_subtitle' => 'Publishing, intelligence, dan Jejakawan dalam satu control plane.',
            'cta_subtitle_id' => 'Publishing, intelligence, dan Jejakawan dalam satu control plane.',
            'cta_subtitle_en' => 'Publishing, intelligence, and Jejakawan in one control plane.',
            'cta_button_text' => 'Hubungi kami',
            'cta_button_text_id' => 'Hubungi kami',
            'cta_button_text_en' => 'Contact us',
            'cta_secondary_text' => 'Lihat harga',
            'cta_secondary_text_id' => 'Lihat harga',
            'cta_secondary_text_en' => 'View pricing',

            'home_sections' => ['hero', 'products', 'updates', 'partners', 'testimonials', 'cta'],
            'page_solusi_title' => 'Produk & solusi Jejakawan',
            'page_solusi_subtitle' => 'Stack modul hub di jejakawan.com — Publishing, Janari, Forms, Intelligence, Platform, dan Jejakawan.',
            'page_about_title' => 'Tentang Jejakawan',
            'page_about_subtitle' => 'Control plane operasional Jejakawan — produk, langganan, dan layanan dalam satu platform.',
            'page_career_title' => 'Karier di Jejakawan',
            'page_career_subtitle' => 'Bergabung dengan tim produk yang membangun hub dan tema Janari.',
            'page_achievement_title' => 'Sorotan produk',
            'page_achievement_subtitle' => 'Tonggak rilis modul, tema, dan platform.',
        ];

        $theme->update(['settings' => array_merge($settings, $hubDefaults)]);
        app(ThemeCacheService::class)->clearTheme($theme);
        $this->command->info('Janari hub theme settings applied (hero, tagline, home sections).');
    }
}
