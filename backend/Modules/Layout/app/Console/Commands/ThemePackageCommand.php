<?php

declare(strict_types=1);

namespace Modules\Layout\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Modules\Layout\Support\ThemeViews;

class ThemePackageCommand extends Command
{
    protected $signature = 'theme:package
                            {slug : Theme slug folder under bundled views/themes}
                            {--output= : Output directory (default: storage/app/public/themes/{slug})}';

    protected $description = 'Copy a bundled theme into uploadable storage layout (for runtime distribution)';

    public function handle(): int
    {
        $slug = Str::slug((string) $this->argument('slug'));
        $source = ThemeViews::pathForSlug($slug);

        if (! is_dir($source) || ! is_file($source.'/theme.json')) {
            $this->error("Bundled theme not found: {$source}");

            return self::FAILURE;
        }

        $dest = $this->option('output')
            ? (string) $this->option('output')
            : storage_path('app/public/themes/'.$slug);

        if (is_dir($dest)) {
            File::deleteDirectory($dest);
        }

        File::copyDirectory($source, $dest);
        $this->info("Packaged theme copied to: {$dest}");
        $this->line('Next: build theme.esm.js with your external Vite pipeline, then add bundle_checksum to theme.json.');
        $this->line('Install via admin Themes → Upload ZIP, or enable FEATURE_UPLOADED_THEMES.');

        return self::SUCCESS;
    }
}
