<?php

declare(strict_types=1);

namespace Modules\Core\Database\Seeders\System;

use Illuminate\Database\Seeder;
use Modules\Core\System\Models\Setting;
use Modules\Core\System\Services\ConsoleThemeService;

/**
 * Seeds console_branding settings from console_theme_schema.json defaults.
 * Run: php artisan db:seed --class="Modules\\Core\\Database\\Seeders\\System\\ConsoleBrandingSettingsSeeder"
 */
class ConsoleBrandingSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(ConsoleThemeService::class);
        $schema = $service->getSchema();

        foreach ($schema as $key => $def) {
            if (! is_array($def)) {
                continue;
            }

            $type = match ($def['type'] ?? 'string') {
                'color' => 'string',
                'range' => 'integer',
                'boolean' => 'boolean',
                default => 'string',
            };

            $default = $def['default'] ?? '';
            if ($type === 'integer') {
                $default = is_numeric($default) ? (string) (int) $default : '0';
            } else {
                $default = is_scalar($default) ? (string) $default : '';
            }

            Setting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $default,
                    'group' => 'console_branding',
                    'type' => $type,
                    'description' => is_string($def['description'] ?? null) ? $def['description'] : ($def['label'] ?? null),
                ],
            );
        }
    }
}
