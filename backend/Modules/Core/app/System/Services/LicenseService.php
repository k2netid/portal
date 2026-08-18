<?php

declare(strict_types=1);

namespace Modules\Core\System\Services;

use Modules\Core\System\Models\Setting;

class LicenseService
{
    /**
     * Get the current license tier of the application.
     * In a real app, this would check an encrypted license key or remote server.
     */
    public function getLicenseTier(): string
    {
        // Check both keys for backward compatibility
        $tier = Setting::get('license_type') ?: Setting::get('app_license_tier', 'basic');

        return is_scalar($tier) ? (string) $tier : 'basic';
    }

    /**
     * Check if the current license allows white labeling.
     */
    public function hasWhiteLabel(): bool
    {
        $tier = $this->getLicenseTier();

        return in_array($tier, ['pro_plus', 'white_label']);
    }

    /**
     * Check if a specific branding key is protected by white label license.
     */
    public function isProtectedKey(string $key): bool
    {
        $protectedKeys = [
            'app_name',
            'app_logo',
            'app_favicon',
            'app_identity',
            'powered_by_link',
            'admin_footer_text',
        ];

        return in_array($key, $protectedKeys);
    }
}
