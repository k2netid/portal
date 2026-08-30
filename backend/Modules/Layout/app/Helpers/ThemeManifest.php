<?php

namespace Modules\Layout\Helpers;

class ThemeManifest
{
    /** @var array<string, mixed> */
    protected array $manifest;

    public function __construct(protected string $path)
    {
        $this->load();
    }

    /**
     * Load manifest from theme.json
     */
    public function load(): void
    {
        $manifestPath = "{$this->path}/theme.json";

        if (! file_exists($manifestPath)) {
            throw new \Exception("Theme manifest not found: {$manifestPath}");
        }

        $content = file_get_contents($manifestPath);
        if ($content === false) {
            throw new \Exception("Failed to read theme manifest: {$manifestPath}");
        }

        $manifest = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid theme.json: '.json_last_error_msg());
        }

        if (! is_array($manifest)) {
            throw new \Exception('Invalid theme.json: expected array');
        }

        /** @var array<string, mixed> $manifest */
        $this->manifest = $manifest;
    }

    /**
     * Get manifest data
     *
     * @return array<string, mixed>
     */
    public function getManifest(): array
    {
        return $this->manifest;
    }

    /**
     * Get theme name
     */
    public function getName(): string
    {
        /** @var string $name */
        $name = $this->manifest['name'] ?? 'Unknown Theme';

        return $name;
    }

    /**
     * Get theme slug
     */
    public function getSlug(): string
    {
        /** @var string $slug */
        $slug = $this->manifest['slug'] ?? basename($this->path);

        return $slug;
    }

    /**
     * Get theme version
     */
    public function getVersion(): string
    {
        /** @var string $version */
        $version = $this->manifest['version'] ?? '1.0.0';

        return $version;
    }

    /**
     * Get theme type
     */
    public function getType(): string
    {
        /** @var string $type */
        $type = $this->manifest['type'] ?? 'frontend';

        return $type;
    }

    /**
     * Get theme description
     */
    public function getDescription(): ?string
    {
        /** @var string|null $description */
        $description = $this->manifest['description'] ?? null;

        return $description;
    }

    /**
     * Get theme author
     */
    public function getAuthor(): ?string
    {
        /** @var string|null $author */
        $author = $this->manifest['author'] ?? null;

        return $author;
    }

    /**
     * Get author URL
     */
    public function getAuthorUrl(): ?string
    {
        /** @var string|null $authorUrl */
        $authorUrl = $this->manifest['author_url'] ?? null;

        return $authorUrl;
    }

    /**
     * Get license
     */
    public function getLicense(): ?string
    {
        /** @var string|null $license */
        $license = $this->manifest['license'] ?? null;

        return $license;
    }

    /**
     * Get parent theme
     */
    public function getParentTheme(): ?string
    {
        /** @var string|null $parentTheme */
        $parentTheme = $this->manifest['parent_theme'] ?? null;

        return $parentTheme;
    }

    /**
     * Get dependencies
     *
     * @return array<string, string>
     */
    public function getDependencies(): array
    {
        /** @var array<string, string> $dependencies */
        $dependencies = $this->manifest['dependencies'] ?? [];

        return $dependencies;
    }

    /**
     * Get supports
     *
     * @return array<string, bool>
     */
    public function getSupports(): array
    {
        /** @var array<string, bool> $supports */
        $supports = $this->manifest['supports'] ?? [];

        return $supports;
    }

    /**
     * Get requirements
     *
     * @return array<string, string>
     */
    public function getRequirements(): array
    {
        /** @var array<string, string> $requires */
        $requires = $this->manifest['requires'] ?? [];

        return $requires;
    }

    /**
     * Get Jejakawan version requirement
     */
    public function getRequiredCmsVersion(): ?string
    {
        $requires = $this->getRequirements();

        return $requires['cms_version'] ?? null;
    }

    /**
     * Get PHP version requirement
     */
    public function getRequiredPhpVersion(): ?string
    {
        $requires = $this->getRequirements();

        return $requires['php'] ?? null;
    }

    /**
     * Get assets configuration
     *
     * @return array<string, array<int, string>>
     */
    public function getAssets(): array
    {
        /** @var array<string, array<int, string>> $assets */
        $assets = $this->manifest['assets'] ?? [];

        return $assets;
    }

    /**
     * Get CSS assets
     *
     * @return array<int, string>
     */
    public function getCssAssets(): array
    {
        $assets = $this->getAssets();

        /** @var array<int, string> $css */
        $css = $assets['css'] ?? [];

        return $css;
    }

    /**
     * Get JS assets
     *
     * @return array<int, string>
     */
    public function getJsAssets(): array
    {
        $assets = $this->getAssets();

        /** @var array<int, string> $js */
        $js = $assets['js'] ?? [];

        return $js;
    }

    /**
     * Get settings schema
     *
     * @return array<string, array<string, mixed>>
     */
    public function getSettingsSchema(): array
    {
        /** @var array<string, array<string, mixed>> $schema */
        $schema = $this->manifest['settings_schema'] ?? [];

        return $schema;
    }

    /**
     * Validate manifest structure
     *
     * @return array<int, string>
     */
    public function validate(): array
    {
        $errors = [];

        // Required fields
        $required = ['name', 'version'];
        foreach ($required as $field) {
            if (! isset($this->manifest[$field])) {
                $errors[] = "Missing required field: {$field}";
            }
        }

        // Validate version format (semver)
        if (isset($this->manifest['version'])) {
            /** @var string $version */
            $version = $this->manifest['version'];
            if (! preg_match('/^\d+\.\d+\.\d+$/', $version)) {
                $errors[] = 'Invalid version format. Expected semver (e.g., 1.0.0)';
            }
        }

        // Validate type
        if (isset($this->manifest['type'])) {
            $validTypes = ['frontend', 'admin', 'email'];
            /** @var string $type */
            $type = $this->manifest['type'];
            if (! in_array($type, $validTypes)) {
                $errors[] = 'Invalid type. Must be one of: '.implode(', ', $validTypes);
            }
        }

        // Validate assets paths exist
        if (isset($this->manifest['assets']) && is_array($this->manifest['assets'])) {
            $assets = $this->getAssets();

            if (isset($assets['css'])) {
                foreach ($assets['css'] as $cssFile) {
                    $cssPath = "{$this->path}/".$cssFile;
                    if (! file_exists($cssPath)) {
                        $errors[] = 'CSS file not found: '.$cssFile;
                    }
                }
            }

            if (isset($assets['js'])) {
                foreach ($assets['js'] as $jsFile) {
                    $jsPath = "{$this->path}/".$jsFile;
                    if (! file_exists($jsPath)) {
                        $errors[] = 'JS file not found: '.$jsFile;
                    }
                }
            }
        }

        // Validate settings schema
        if (isset($this->manifest['settings_schema']) && is_array($this->manifest['settings_schema'])) {
            $schema = $this->getSettingsSchema();
            foreach ($schema as $key => $setting) {
                if (! isset($setting['type'])) {
                    $errors[] = "Setting '{$key}' missing type";
                }

                $validTypes = ['text', 'textarea', 'number', 'email', 'url', 'color', 'select', 'checkbox', 'radio'];
                /** @var string|null $settingType */
                $settingType = $setting['type'] ?? null;
                if ($settingType !== null && ! in_array($settingType, $validTypes)) {
                    $errors[] = "Setting '{$key}' has invalid type: ".$settingType;
                }

                if ($settingType === 'select' && ! isset($setting['options'])) {
                    $errors[] = "Setting '{$key}' (select) missing options";
                }
            }
        }

        return $errors;
    }

    /**
     * Check if theme meets requirements
     *
     * @return array<int, string>
     */
    public function checkRequirements(): array
    {
        $errors = [];

        // Check Jejakawan version
        $requiredCmsVersion = $this->getRequiredCmsVersion();
        if ($requiredCmsVersion) {
            /** @var string $currentVersion */
            $currentVersion = config('app.version', '1.0.0');
            if (version_compare($currentVersion, $requiredCmsVersion, '<')) {
                $errors[] = "Jejakawan version {$currentVersion} does not meet requirement: {$requiredCmsVersion}";
            }
        }

        // Check PHP version
        $requiredPhpVersion = $this->getRequiredPhpVersion();
        if ($requiredPhpVersion) {
            $currentPhpVersion = PHP_VERSION;
            if (version_compare($currentPhpVersion, $requiredPhpVersion, '<')) {
                $errors[] = "PHP version {$currentPhpVersion} does not meet requirement: {$requiredPhpVersion}";
            }
        }

        return $errors;
    }

    /**
     * Get screenshot path
     */
    public function getScreenshotPath(): ?string
    {
        /** @var string $screenshot */
        $screenshot = $this->manifest['screenshot'] ?? 'screenshot.png';
        $screenshotPath = "{$this->path}/".$screenshot;

        return file_exists($screenshotPath) ? $screenshotPath : null;
    }

    /**
     * Create manifest from array
     *
     * @param  array<string, mixed>  $data
     */
    public static function create(string $themePath, array $data): bool
    {
        $manifestPath = "{$themePath}/theme.json";

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return file_put_contents($manifestPath, $json) !== false;
    }
}
