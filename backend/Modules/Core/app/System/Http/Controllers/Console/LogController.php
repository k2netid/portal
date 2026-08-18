<?php

namespace Modules\Core\System\Http\Controllers\Console;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Modules\Core\System\Helpers\IpHelper;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Symfony\Component\HttpFoundation\Response;

class LogController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $logPath = storage_path('logs');
        $files = [];

        if (File::isDirectory($logPath)) {
            $filesInDir = File::files($logPath);
            foreach ($filesInDir as $file) {
                if ($file->getExtension() === 'log') {
                    $files[] = [
                        'name' => $file->getFilename(),
                        'size' => $file->getSize(),
                        'modified' => $file->getMTime(),
                    ];
                }
            }
        }

        // Sort by modified date desc
        usort($files, function (array $a, array $b): int {
            /** @var array{name: string, size: int|false, modified: int|false} $a */
            /** @var array{name: string, size: int|false, modified: int|false} $b */
            return $b['modified'] <=> $a['modified'];
        });

        return $this->success($files, 'Log files retrieved successfully');
    }

    public function show(string $filename): JsonResponse
    {
        $logFile = storage_path('logs/'.basename($filename));

        if (! File::exists($logFile)) {
            return $this->notFound('Log file');
        }

        // Limit content to last 2MB to avoid huge payload
        $content = $this->tailFile($logFile, 2000);

        return $this->success(['content' => $content], 'Log content retrieved successfully');
    }

    public function download(string $filename): Response
    {
        $logFile = storage_path('logs/'.basename($filename));

        if (File::exists($logFile)) {
            return response()->download($logFile, basename($filename));
        }

        return $this->notFoundResponse('Log file');
    }

    protected function notFoundResponse(string $resource): JsonResponse
    {
        return $this->error("{$resource} not found", 404);
    }

    public function destroy(Request $request, string $filename): JsonResponse
    {
        $request->merge(['filename' => $filename]);

        return $this->clear($request);
    }

    public function clear(Request $request): JsonResponse
    {
        $reasonRaw = $request->input('reason');
        $reason = is_string($reasonRaw) ? trim($reasonRaw) : '';
        if ($reason === '' || mb_strlen($reason) < 8) {
            return $this->validationError([
                'reason' => ['A clear reason (minimum 8 characters) is required to clear logs.'],
            ]);
        }

        $filenameRaw = $request->input('filename');
        $filename = is_string($filenameRaw) ? $filenameRaw : null;
        $logPath = storage_path('logs');
        $actor = $request->user();

        $audit = function (string $scope) use ($reason, $actor): void {
            Log::channel('daily')->warning('System journal clear requested', [
                'scope' => $scope,
                'reason' => $reason,
                'actor_id' => $actor?->id,
                'actor_email' => $actor?->email,
                'ip' => IpHelper::getClientIp(request()),
            ]);
        };

        if ($filename) {
            $logFile = $logPath.'/'.basename($filename);
            if (File::exists($logFile)) {
                try {
                    $audit($filename);
                    File::put($logFile, '');
                    $deletedEmptyFiles = $this->deleteEmptyLogFiles($logPath);

                    return $this->success([
                        'deleted_empty_files' => $deletedEmptyFiles,
                    ], 'Log file cleared successfully');
                } catch (\Exception $e) {
                    return $this->error('Failed to clear log file: '.$e->getMessage(), 500);
                }
            }

            return $this->notFound('Log file');
        }

        // Clear all .log files if no filename provided
        if (File::isDirectory($logPath)) {
            $files = File::files($logPath);
            $audit('all');
            foreach ($files as $file) {
                if ($file->getExtension() === 'log') {
                    try {
                        File::put($file->getPathname(), '');
                    } catch (\Exception) {
                        // Ignore permission errors or read-only files
                        // We continue clearing other files
                        continue;
                    }
                }
            }

            $deletedEmptyFiles = $this->deleteEmptyLogFiles($logPath);

            return $this->success([
                'deleted_empty_files' => $deletedEmptyFiles,
            ], 'All log files cleared successfully');
        }

        return $this->error('Log directory not found', 404);
    }

    protected function tailFile(string $filepath, int $lines = 100): string
    {
        // Simple file get for now, or sophisticated tail logic
        // Since admin might want to see whole file but pagination is hard with text files
        // We will return the last 100 KB text

        $content = File::get($filepath);
        // If too large, truncate
        if (strlen($content) > 1024 * 500) { // 500KB limit
            return '... (File too large, showing last 500KB) ...'."\n".substr($content, -1024 * 500);
        }

        return $content;
    }

    /**
     * Remove empty .log files to keep journal directory clean.
     *
     * @return int Number of deleted empty files
     */
    protected function deleteEmptyLogFiles(string $logPath): int
    {
        if (! File::isDirectory($logPath)) {
            return 0;
        }

        $deletedCount = 0;
        foreach (File::files($logPath) as $file) {
            if ($file->getExtension() !== 'log') {
                continue;
            }

            if ($file->getSize() !== 0) {
                continue;
            }

            try {
                File::delete($file->getPathname());
                $deletedCount++;
            } catch (\Exception) {
                // Ignore deletion errors to avoid failing the main clear action.
                continue;
            }
        }

        return $deletedCount;
    }
}
