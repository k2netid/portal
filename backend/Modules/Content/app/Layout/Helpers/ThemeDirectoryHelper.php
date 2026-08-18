<?php

namespace Modules\Content\Layout\Helpers;

use Illuminate\Support\Facades\File;

class ThemeDirectoryHelper
{
    /**
     * Create Vue theme directory structure
     */
    public static function createStructure(string $themePath, string $themeName, string $themeSlug): bool
    {
        try {
            // Create Vue theme directories
            $directories = [
                'assets/css',
                'assets/js',
                'components',
                'composables',
            ];

            foreach ($directories as $dir) {
                $fullPath = "{$themePath}/{$dir}";
                if (! File::isDirectory($fullPath)) {
                    File::makeDirectory($fullPath, 0755, true);
                }
            }

            // Create default theme.json
            $manifest = [
                'name' => $themeName,
                'slug' => $themeSlug,
                'version' => '1.0.0',
                'type' => 'frontend',
                'description' => "A custom theme: {$themeName}",
                'author' => 'Jejakawan',
                'license' => 'MIT',
                'requires' => [
                    'cms_version' => '>=1.0.0',
                    'php' => '>=8.2',
                ],
                'supports' => [
                    'custom_logo' => true,
                    'custom_colors' => true,
                    'custom_fonts' => true,
                    'widgets' => true,
                    'menus' => true,
                ],
                'assets' => [
                    'css' => [
                        'assets/css/main.css',
                    ],
                    'js' => [
                        'assets/js/theme.js',
                    ],
                ],
                'settings_schema' => [
                    'primary_color' => [
                        'type' => 'color',
                        'default' => '#3B82F6',
                        'label' => 'Primary Color',
                        'description' => 'The main brand color',
                    ],
                    'secondary_color' => [
                        'type' => 'color',
                        'default' => '#8B5CF6',
                        'label' => 'Secondary Color',
                        'description' => 'The secondary brand color',
                    ],
                ],
            ];

            ThemeManifest::create($themePath, $manifest);

            // Create default CSS file
            $cssContent = <<<'CSS'
/* Theme: {$themeName} */
:root {
    --theme-primary-color: #3B82F6;
    --theme-secondary-color: #8B5CF6;
}

body {
    font-family: system-ui, -apple-system, sans-serif;
}
CSS;
            File::put("{$themePath}/assets/css/main.css", $cssContent);

            // Create default JS file
            $jsContent = <<<'JS'
// Theme: {$themeName}
document.addEventListener('DOMContentLoaded', function() {
    console.log('Theme loaded: {$themeName}');
});
JS;
            File::put("{$themePath}/assets/js/theme.js", $jsContent);

            // Create README
            $readme = <<<README
# {$themeName}

A Vue-based theme for Jejakawan.

## Installation

This theme is automatically loaded when placed in the themes directory.

## Structure

```
{$themeSlug}/
├── assets/css/           # CSS variables and styles
├── components/           # Vue SFC components
├── composables/          # Vue composables (optional)
└── theme.json            # Theme configuration
```

## Customization

- `assets/css/variables.css` - CSS variables for theming
- `components/` - Vue components (Header, Footer, etc.)
- `theme.json` - Theme manifest and settings schema

## Settings

Configure theme settings in the admin panel under Themes > Settings.

## Development

Create Vue components in the `components/` directory and reference them
in the `theme.json` manifest. The theme system will dynamically load
them via the application's composables.
README;
            File::put("{$themePath}/README.md", $readme);

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to create theme structure: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Get theme directory structure info
     *
     * @return array{exists: bool, directories: array<int, string>, files: array<int, string>}
     */
    public static function getStructureInfo(string $themePath): array
    {
        $info = [
            'exists' => is_dir($themePath),
            'directories' => [],
            'files' => [],
        ];

        if (! $info['exists']) {
            return $info;
        }

        // Scan directories
        $directories = File::directories($themePath);
        foreach ($directories as $dir) {
            $info['directories'][] = basename((string) $dir);
        }

        // Scan files
        $files = File::files($themePath);
        foreach ($files as $file) {
            $info['files'][] = basename($file);
        }

        return $info;
    }

    /**
     * Validate theme directory structure
     *
     * @return array<int, string>
     */
    public static function validateStructure(string $themePath): array
    {
        $errors = [];

        if (! is_dir($themePath)) {
            $errors[] = 'Theme directory does not exist';

            return $errors;
        }

        // Check for theme.json
        if (! file_exists("{$themePath}/theme.json")) {
            $errors[] = 'theme.json not found';
        }

        // Check for assets directory
        if (! is_dir("{$themePath}/assets")) {
            $errors[] = 'assets directory not found';
        }

        return $errors;
    }
}
