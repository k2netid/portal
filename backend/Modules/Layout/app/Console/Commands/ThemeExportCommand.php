<?php

declare(strict_types=1);

namespace Modules\Layout\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Modules\Layout\Support\ThemeViews;
use ZipArchive;

class ThemeExportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'theme:export
                            {slug : Theme slug to export}
                            {--output= : Destination path for the generated zip archive}
                            {--force : Overwrite destination zip if it exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export a theme directory into a portable .zip distribution package';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $slug = Str::slug((string) $this->argument('slug'));
        $sourcePath = ThemeViews::pathForSlug($slug);

        if (! is_dir($sourcePath) || ! is_file($sourcePath.'/theme.json')) {
            $this->error("Theme not found or invalid theme directory: {$sourcePath}");

            return self::FAILURE;
        }

        $defaultOutput = storage_path("app/public/themes/{$slug}-package.zip");
        $destPath = $this->option('output') ? (string) $this->option('output') : $defaultOutput;

        $destDir = dirname($destPath);
        if (! is_dir($destDir)) {
            File::makeDirectory($destDir, 0755, true, true);
        }

        if (file_exists($destPath) && ! $this->option('force')) {
            if (! $this->confirm("Destination file [{$destPath}] already exists. Overwrite?")) {
                $this->info('Export aborted.');

                return self::SUCCESS;
            }
        }

        $this->info("Packaging theme [{$slug}] from: {$sourcePath}");

        $zip = new ZipArchive;
        if ($zip->open($destPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->error("Cannot create zip archive at: {$destPath}");

            return self::FAILURE;
        }

        $files = File::allFiles($sourcePath);
        $totalFiles = count($files);
        $progressBar = $this->output->createProgressBar($totalFiles);
        $progressBar->start();

        foreach ($files as $file) {
            $relative = $file->getRelativePathname();

            // Skip git and dev lockfiles in package
            if (str_starts_with($relative, '.git') || str_ends_with($relative, '.DS_Store')) {
                $progressBar->advance();

                continue;
            }

            $zip->addFile($file->getRealPath(), "{$slug}/{$relative}");
            $progressBar->advance();
        }

        $zip->close();
        $progressBar->finish();
        $this->newLine(2);

        $sizeMb = round(filesize($destPath) / (1024 * 1024), 2);
        $this->info("✓ Theme package successfully exported to: {$destPath} ({$sizeMb} MB)");
        $this->line('You can now distribute or upload this package via JA-CMS Themes Manager.');

        return self::SUCCESS;
    }
}
