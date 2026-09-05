<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\System\Models\Setting;
use Modules\Layout\Models\Theme;
use Modules\Layout\Services\ThemeCacheService;

class K2netBrandingSeeder extends Seeder
{
    /**
     * Seed K2NET deployment identity settings into the database.
     */
    public function run(): void
    {
        $this->command?->info('Seeding K2NET dynamic branding configuration...');

        // 1. System Settings (Core Platform Identity)
        Setting::set('site_name', 'K2NET', 'string', 'general');
        Setting::set('site_title', 'K2NET', 'string', 'general');
        Setting::set('site_tagline', 'Internet Service Provider & Managed Service Provider — Bandung, Jawa Barat', 'string', 'general');
        Setting::set('site_description', 'K2NET menyediakan layanan konektivitas internet cepat berkecepatan tinggi dan solusi managed service andal untuk korporasi dan bisnis.', 'string', 'general');
        Setting::set('contact_email', 'info@k2net.id', 'string', 'general');
        Setting::set('admin_email', 'admin@k2net.id', 'string', 'general');
        Setting::set('brand_logo', '/logofull_k2net.png', 'string', 'general');

        // 2. Active Theme Configuration (Layung ISP/Corporate Theme)
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
            ];

            $layung->settings = array_merge($currentSettings, $k2netThemeSettings);
            $layung->is_active = true;
            $layung->save();

            // Set Layung as primary active theme in settings
            Setting::set('theme_active', 'layung', 'string', 'layout');

            if (class_exists(ThemeCacheService::class)) {
                try {
                    app(ThemeCacheService::class)->clear();
                } catch (\Throwable) {
                    // Ignore cache clear error during seeding
                }
            }

            $this->command?->info('✅ Layung theme configured with K2NET identity settings.');
        } else {
            $this->command?->warn('⚠️ Layung theme not found in database. Seed themes first.');
        }

        // 3. Perpetual Enterprise License Activation
        /** @var \Modules\Core\System\Services\LicenseService $licenseService */
        $licenseService = app(\Modules\Core\System\Services\LicenseService::class);
        $licenseService->activateLicense('JACP-ENT-PERPETUAL-K2NET-ID');
        Setting::set('license_domain', 'staging.k2net.id');
        Setting::set('app_license_tier', 'enterprise');
        Setting::set('has_white_label', true);
        $this->command?->info('✅ Perpetual Enterprise license applied.');

        $this->command?->info('✅ K2NET branding & enterprise license seeded successfully.');
    }
}
