<?php

declare(strict_types=1);

namespace Modules\Layout\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Modules\Layout\Models\Theme;
use Modules\Layout\Services\ThemeCacheService;

class ThemeStagingUploadedCommand extends Command
{
    protected $signature = 'theme:staging-uploaded
                            {slug=janari : Active frontend theme slug}
                            {--revert : Restore source=bundled and clear bundle_url}';

    protected $description = 'Staging: point active theme at storage theme.esm.js (uploaded hybrid loader)';

    public function handle(ThemeCacheService $cache): int
    {
        $slug = Str::slug((string) $this->argument('slug'));
        $theme = Theme::withoutGlobalScopes()->where('slug', $slug)->where('type', 'frontend')->first();

        if (! $theme) {
            $this->error("Theme not found: {$slug} (frontend)");

            return self::FAILURE;
        }

        if ($this->option('revert')) {
            $theme->update([
                'source' => 'bundled',
                'bundle_url' => null,
            ]);
            $cache->clearTheme($theme);
            $this->info("Reverted {$slug} to bundled source.");

            return self::SUCCESS;
        }

        if (! config('content.layout.uploaded_themes_enabled')) {
            $this->warn('FEATURE_UPLOADED_THEMES is false — frontend will still use import.meta.glob until env is enabled.');
        }

        $storageDir = storage_path('app/public/themes/'.$slug);
        $bundlePath = $storageDir.'/theme.esm.js';
        if (! is_file($bundlePath)) {
            $this->error("Missing bundle: {$bundlePath}");
            $this->line('Run: ./scripts/build-theme-bundle.sh '.$slug);

            return self::FAILURE;
        }

        $manifestPath = $storageDir.'/theme.json';
        $bundleUrl = "/storage/themes/{$slug}/theme.esm.js";
        if (is_file($manifestPath)) {
            $manifest = json_decode((string) File::get($manifestPath), true);
            if (is_array($manifest) && ! empty($manifest['bundle_url'])) {
                $bundleUrl = is_string($manifest['bundle_url']) ? $manifest['bundle_url'] : $bundleUrl;
            }
        }

        $theme->update([
            'source' => 'uploaded',
            'bundle_url' => $bundleUrl,
            'is_active' => true,
        ]);

        Theme::withoutGlobalScopes()
            ->where('type', 'frontend')
            ->where('id', '!=', $theme->id)
            ->update(['is_active' => false]);

        $theme->activate();
        $cache->clearTheme($theme);

        $checksum = is_array($manifest ?? null) ? ($manifest['bundle_checksum'] ?? '') : '';
        $this->info("Staging uploaded mode: {$slug}");
        $this->line("  bundle_url: {$bundleUrl}");
        if ($checksum) {
            $checksumStr = is_string($checksum) ? $checksum : '';
            $this->line('  bundle_checksum: '.substr($checksumStr, 0, 16).'…');
        }
        $this->line('Enable: FEATURE_UPLOADED_THEMES=true and VITE_FEATURE_UPLOADED_THEMES=true');
        $this->line('Revert: php artisan theme:staging-uploaded '.$slug.' --revert');

        return self::SUCCESS;
    }
}
