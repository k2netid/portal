<?php

namespace Modules\Core\System\Http\Controllers\Console;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\Core\System\Http\Controllers\BaseApiController;

class StorageMigrationController extends BaseApiController
{
    /**
     * List all files from local public storage that need migration
     */
    public function index(): JsonResponse
    {
        try {
            // We specifically want to list files from the 'public' disk (local)
            // even if the default driver is changed to s3/google
            $files = Storage::disk('public')->allFiles();

            // Filter out hidden files or specific system files if needed
            $files = array_filter($files, fn (string $file) => ! str_starts_with($file, '.'));

            return $this->success(array_values($files), 'Files retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to list files: '.$e->getMessage());
        }
    }

    /**
     * Migrate a batch of files from public disk to the currently configured default disk
     */
    public function migrate(Request $request): JsonResponse
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'string',
        ]);

        /** @var array<string> $files */
        $files = $request->input('files');
        $results = [
            'success' => [],
            'failed' => [],
            'skipped' => [], // Files that already exist
        ];

        $targetDisk = Storage::getDefaultDriver();

        // Safety check: Don't migrate if target is public (local)
        if ($targetDisk === 'public' || $targetDisk === 'local') {
            return $this->error('Target storage is set to local. Please configure an external storage driver first.');
        }

        foreach ($files as $file) {
            try {
                // Check if file exists on source
                if (! Storage::disk('public')->exists($file)) {
                    $results['failed'][$file] = 'File not found on source';

                    continue;
                }

                // Check if file already exists on target
                if (Storage::disk($targetDisk)->exists($file)) {
                    $results['skipped'][] = $file;

                    continue;
                }

                // Perform copy using streams for memory efficiency
                $stream = Storage::disk('public')->readStream($file);
                if (! is_resource($stream)) {
                    $results['failed'][$file] = 'Failed to read from source';

                    continue;
                }

                $copied = Storage::disk($targetDisk)->writeStream($file, $stream);

                if ($copied) {
                    $results['success'][] = $file;
                } else {
                    $results['failed'][$file] = 'Failed to write to target';
                }

                if (is_resource($stream)) {
                    fclose($stream);
                }

            } catch (\Exception $e) {
                $results['failed'][$file] = $e->getMessage();
                Log::error("Migration failed for file {$file}: ".$e->getMessage());
            }
        }

        return $this->success($results, 'Batch migration completed');
    }
}
