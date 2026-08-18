<?php

namespace Modules\Content\Layout\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Modules\Content\Layout\Helpers\ThemeDirectoryHelper;
use Modules\Content\Layout\Models\Theme;
use Modules\Content\Layout\Services\ThemeService;

class ThemeMake extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'theme:make 
                            {name : The name of the theme}
                            {--slug= : The theme slug (default: auto-generated)}
                            {--type=frontend : Theme type (frontend, admin, email)}
                            {--parent= : Parent theme slug}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new theme structure';

    /**
     * Execute the console command.
     */
    public function handle(ThemeService $themeService): int
    {
        $name = (string) $this->argument('name');
        $slug = (string) ($this->option('slug') ?: Str::slug($name));
        $type = (string) ($this->option('type') ?? 'frontend');
        $parent = (string) $this->option('parent');

        // Validate type
        if (! in_array($type, ['frontend', 'admin', 'email'])) {
            $this->error('Invalid type. Must be one of: frontend, admin, email');

            return Command::FAILURE;
        }

        // Check if theme already exists
        $themeService->ensureThemeDirectory();
        $themePath = $themeService->getThemeDirectory()."/{$slug}";

        if (is_dir($themePath) && ! $this->confirm('Theme directory already exists. Continue anyway?')) {
            return Command::FAILURE;
        }

        $this->info("Creating theme: {$name} ({$slug})");

        // Create theme structure
        if (! ThemeDirectoryHelper::createStructure($themePath, $name, $slug)) {
            $this->error('Failed to create theme structure');

            return Command::FAILURE;
        }

        // Update theme.json with type and parent
        $manifestPath = "{$themePath}/theme.json";
        if (file_exists($manifestPath)) {
            $jsonContent = file_get_contents($manifestPath);
            if ($jsonContent === false) {
                $this->error('Could not read theme.json');
                // Continue without updating manifest if read fails, or return Command::FAILURE;
            } else {
                $manifest = json_decode($jsonContent, true);
                if (is_array($manifest)) {
                    $manifest['type'] = $type;
                    if ($parent) {
                        $manifest['parent_theme'] = $parent;
                    }
                    file_put_contents($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                }
            }
        }

        // Create theme in database
        $theme = Theme::create([
            'name' => $name,
            'slug' => $slug,
            'type' => $type,
            'path' => $slug,
            'version' => '1.0.0',
            'parent_theme' => $parent,
            'status' => 'active',
        ]);

        $this->info("✓ Theme structure created at: {$themePath}");
        $this->info("✓ Theme registered in database (ID: {$theme->id})");
        $this->newLine();
        $this->info('Next steps:');
        $this->line("  1. Edit theme files in: {$themePath}");
        $this->line('  2. Customize theme.json for configuration');
        $this->line('  3. Add your CSS/JS files in assets/ directory');
        $this->line('  4. Create templates in templates/ directory');
        $this->line("  5. Run 'php artisan themes:scan' to refresh theme list");

        return Command::SUCCESS;
    }
}
