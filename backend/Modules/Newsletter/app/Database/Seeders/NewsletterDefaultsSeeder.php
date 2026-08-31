<?php

declare(strict_types=1);

namespace Modules\Newsletter\Database\Seeders;

use Modules\Core\System\Models\Extension;

/**
 * Idempotent newsletter defaults (metadata until list/campaign UI ships).
 */
class NewsletterDefaultsSeeder
{
    public static function ensure(): void
    {
        $extension = Extension::query()->where('slug', 'newsletter')->first();
        if ($extension === null) {
            return;
        }

        $settings = is_array($extension->settings) ? $extension->settings : [];
        if (($settings['default_list_name'] ?? null) === 'General') {
            return;
        }

        $settings['default_list_name'] = 'General';
        $extension->update(['settings' => $settings]);
    }
}
