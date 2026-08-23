<?php

namespace Modules\Media\Console\Commands;

use Illuminate\Console\Command;
use Modules\Media\Models\File;
use Modules\Media\Services\MediaService;

class GenerateThumbnails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:generate-thumbnails {--force : Re-generate existing thumbnails}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate missing thumbnails for image media files';

    /**
     * Execute the console command.
     */
    public function handle(MediaService $mediaService): int
    {
        $force = $this->option('force');

        $this->info('Generating thumbnails...');

        $files = File::where('mime_type', 'like', 'image/%')->get();

        $count = 0;
        $errors = 0;

        foreach ($files as $file) {
            try {
                // If force is true, we always generate. If false, we only generate if it doesn't exist
                if ($force || ! $file->thumbnail_path || ! \Storage::disk($file->disk)->exists($file->thumbnail_path)) {
                    $mediaService->generateThumbnail($file);
                    $count++;
                    $this->line("Generated thumbnail for: {$file->file_name}");
                }
            } catch (\Exception $e) {
                $this->error("Failed to generate thumbnail for {$file->file_name}: ".$e->getMessage());
                $errors++;
            }
        }

        $this->info('Thumbnail generation completed.');
        $this->info("Generated: {$count}");
        if ($errors > 0) {
            $this->error("Errors: {$errors}");
        }

        return 0;
    }
}
