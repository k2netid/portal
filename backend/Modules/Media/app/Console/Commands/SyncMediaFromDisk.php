<?php

namespace Modules\Media\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Modules\Core\System\Console\Concerns\SkipsWhenProductInactive;
use Modules\Media\Contracts\MediaServiceInterface;
use Modules\Media\Models\File;

class SyncMediaFromDisk extends Command
{
    use SkipsWhenProductInactive;
    protected $signature = 'media:sync-from-disk
                            {--disk=public : Storage disk name}
                            {--path=media : Directory relative to disk root}
                            {--dry-run : List files without importing}
                            {--thumbnails : Generate thumbnails for imported images}';

    protected $description = 'Import filesystem files into the media library database (srv_media_files)';

    public function handle(MediaServiceInterface $mediaService): int
    {
        if ($this->skipUnlessProductActive('media')) {
            return self::SUCCESS;
        }

        $disk = (string) $this->option('disk');
        $basePath = trim((string) $this->option('path'), '/');
        $dryRun = (bool) $this->option('dry-run');
        $generateThumbnails = (bool) $this->option('thumbnails');

        if (! Storage::disk($disk)->exists($basePath)) {
            $this->error("Path '{$basePath}' does not exist on disk '{$disk}'.");

            return 1;
        }

        $files = Storage::disk($disk)->allFiles($basePath);
        $imported = 0;
        $skipped = 0;

        $this->info(sprintf(
            'Scanning %d file(s) under %s on disk %s%s',
            count($files),
            $basePath,
            $disk,
            $dryRun ? ' (dry run)' : ''
        ));

        foreach ($files as $relativePath) {
            $exists = File::withoutGlobalScopes()
                ->where('disk', $disk)
                ->where('path', $relativePath)
                ->exists();

            if ($exists) {
                $skipped++;

                continue;
            }

            if ($dryRun) {
                if (! Storage::disk($disk)->exists($relativePath)) {
                    continue;
                }
                $this->line("  [would import] {$relativePath}");
                $imported++;

                continue;
            }

            $mediaFile = $mediaService->registerFromDisk(
                $disk,
                $relativePath,
                null,
                $generateThumbnails
            );

            if ($mediaFile === null) {
                $skipped++;

                continue;
            }

            $this->line("  [imported] {$relativePath}");
            $imported++;
        }

        $this->newLine();
        $this->info("Done. Imported: {$imported}, already in DB or skipped: {$skipped}.");

        return 0;
    }
}
