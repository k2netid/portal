<?php

declare(strict_types=1);

namespace Modules\Core\System\Services;

use Illuminate\Support\Facades\File;
use Modules\Core\System\Models\Setting;

/**
 * Console (admin dashboard) theme tokens — schema-driven, DB-backed.
 */
final class ConsoleThemeService
{
    private const SCHEMA_PATH = 'Modules/Core/config/console_theme_schema.json';

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getSchema(): array
    {
        $path = module_path('Core', 'config/console_theme_schema.json');
        if (! File::isFile($path)) {
            $path = base_path(self::SCHEMA_PATH);
        }

        if (! File::isFile($path)) {
            return [];
        }

        $decoded = json_decode((string) File::get($path), true);

        if (! is_array($decoded)) {
            return [];
        }

        /** @var array<string, array<string, mixed>> $decoded */
        return $decoded;
    }

    /**
     * Default values from schema only.
     *
     * @return array<string, mixed>
     */
    public function getDefaults(): array
    {
        $out = [];
        foreach ($this->getSchema() as $key => $def) {
            if (! is_array($def)) {
                continue;
            }
            $out[$key] = $def['default'] ?? null;
        }

        return $out;
    }

    /**
     * Resolved settings: DB group console_branding merged over schema defaults.
     *
     * @return array<string, mixed>
     */
    public function getResolvedSettings(): array
    {
        $merged = $this->getDefaults();
        $stored = Setting::getGroup('console_branding');

        foreach ($stored as $key => $value) {
            if ($value !== null && $value !== '') {
                $merged[$key] = $value;
            }
        }

        // Logo assets are saved under group `brand` from console appearance UI.
        $brand = Setting::getGroup('brand');
        foreach ($brand as $key => $value) {
            if (! array_key_exists($key, $merged)) {
                continue;
            }
            if ($value !== null && $value !== '') {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    /**
     * @return array{schema: array<string, array<string, mixed>>, settings: array<string, mixed>}
     */
    public function getPayload(): array
    {
        return [
            'schema' => $this->getSchema(),
            'settings' => $this->getResolvedSettings(),
        ];
    }
}
