<?php

declare(strict_types=1);

namespace Modules\Layout\SampleData;

use Modules\Layout\Support\ThemeViews;

final class ThemeSampleDataReader
{
    /**
     * @return array<string, mixed>|null
     */
    public function readBundle(string $themeSlug): ?array
    {
        $path = ThemeViews::pathForSlug($themeSlug).DIRECTORY_SEPARATOR.'sample-data'.DIRECTORY_SEPARATOR.'bundle.json';
        if (! is_file($path)) {
            return null;
        }

        $raw = file_get_contents($path);
        if ($raw === false || trim($raw) === '') {
            return null;
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            throw new \RuntimeException("Invalid sample-data bundle JSON for theme [{$themeSlug}].");
        }

        return $decoded;
    }

    public function hasBundle(string $themeSlug): bool
    {
        $path = ThemeViews::pathForSlug($themeSlug).DIRECTORY_SEPARATOR.'sample-data'.DIRECTORY_SEPARATOR.'bundle.json';

        return is_file($path);
    }
}
