<?php

declare(strict_types=1);

namespace Modules\Content\Layout\Console\Commands;

use Illuminate\Console\Command;
use Modules\Content\Layout\Models\Theme;
use Modules\Content\Layout\Support\ThemeViews;

class ThemeBackfillSourceCommand extends Command
{
    protected $signature = 'theme:backfill-source {--force : Overwrite existing source values}';

    protected $description = 'Set lay_themes.source=bundled for monorepo themes; uploaded for storage/app/public/themes';

    public function handle(): int
    {
        $force = (bool) $this->option('force');
        $uploadedRoot = storage_path('app/public/themes');
        $updated = 0;

        foreach (Theme::query()->get() as $theme) {
            $path = $uploadedRoot.DIRECTORY_SEPARATOR.$theme->slug;
            $newSource = is_dir($path) && is_file($path.'/theme.json') ? 'uploaded' : 'bundled';

            if (! $force && in_array($theme->source, ['bundled', 'uploaded'], true)) {
                continue;
            }

            if ($theme->source !== $newSource) {
                $theme->update(['source' => $newSource]);
                $this->line("  {$theme->slug} → {$newSource}");
                $updated++;
            }
        }

        $this->info("Backfill selesai ({$updated} baris diubah). Scan roots:");
        foreach (ThemeViews::scanRootPaths() as $root) {
            $this->line("  [{$root['source']}] {$root['path']}");
        }

        return self::SUCCESS;
    }
}
