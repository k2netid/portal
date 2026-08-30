<?php

declare(strict_types=1);

namespace Modules\Layout\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Modules\Layout\Support\ThemeViews;

class ThemeBuildCommand extends Command
{
    protected $signature = 'theme:build
                            {slug : Theme slug}
                            {--output= : Directory containing theme.esm.js (default: storage/app/public/themes/{slug})}
                            {--bundle=theme.esm.js : Bundle filename}';

    protected $description = 'Verify uploaded theme bundle and write bundle_checksum into theme.json (Fase 6)';

    public function handle(): int
    {
        $slug = Str::slug((string) $this->argument('slug'));
        $dir = $this->option('output')
            ? (string) $this->option('output')
            : storage_path('app/public/themes/'.$slug);
        $bundleName = (string) $this->option('bundle');
        $bundlePath = rtrim($dir, '/').'/'.$bundleName;
        $manifestPath = rtrim($dir, '/').'/theme.json';

        if (! is_dir(ThemeViews::pathForSlug($slug))) {
            $this->warn('Bundled source not found under ThemeViews; continuing with output dir only.');
        }

        if (! is_file($bundlePath)) {
            $this->error("Bundle not found: {$bundlePath}");
            $this->line('Build theme.esm.js with your Vite pipeline (export resolveComponent).');
            $this->line('See docs/theme-system-modernization/theme-build.md');
            $this->line("Tip: php artisan theme:package {$slug} then build into {$dir}");

            return self::FAILURE;
        }

        $checksum = hash_file('sha256', $bundlePath);
        $this->info("Bundle: {$bundlePath}");
        $this->info("SHA-256: {$checksum}");

        if (! is_file($manifestPath)) {
            $this->warn("No theme.json at {$manifestPath} — checksum not persisted.");

            return self::SUCCESS;
        }

        $manifest = json_decode((string) File::get($manifestPath), true);
        if (! is_array($manifest)) {
            $this->error('Invalid theme.json');

            return self::FAILURE;
        }

        $manifest['bundle_checksum'] = $checksum;
        $manifest['bundle_url'] = $manifest['bundle_url'] ?? "/storage/themes/{$slug}/{$bundleName}";
        File::put($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");
        $this->info("Updated {$manifestPath} with bundle_checksum.");

        return self::SUCCESS;
    }
}
