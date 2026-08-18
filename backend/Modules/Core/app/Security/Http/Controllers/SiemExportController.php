<?php

declare(strict_types=1);

namespace Modules\Core\Security\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Modules\Core\System\Http\Controllers\BaseApiController;

class SiemExportController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $limit = min(max((int) $request->query('limit', 50), 1), 100);
        $path = storage_path('logs/siem_export.log');

        if (! File::exists($path)) {
            $rotated = storage_path('logs/siem_export-'.now()->format('Y-m-d').'.log');
            $path = File::exists($rotated) ? $rotated : $path;
        }

        if (! File::exists($path)) {
            return $this->success([], 'No SIEM export log file yet');
        }

        $lines = $this->tailLines($path, $limit);
        $events = [];

        foreach (array_reverse($lines) as $line) {
            if (! str_contains($line, 'SECURITY_EVENT:')) {
                continue;
            }
            $events[] = [
                'raw' => $line,
                'recorded_at' => $this->extractTimestamp($line),
            ];
            if (count($events) >= $limit) {
                break;
            }
        }

        return $this->success($events, 'SIEM export events');
    }

    /** @return list<string> */
    private function tailLines(string $path, int $limit): array
    {
        $file = new \SplFileObject($path, 'r');
        $file->seek(PHP_INT_MAX);
        $lastLine = $file->key();
        $start = max(0, $lastLine - ($limit * 3));
        $lines = [];
        $file->seek($start);
        while (! $file->eof()) {
            $current = $file->current();
            $line = trim(is_string($current) ? $current : '');
            if ($line !== '') {
                $lines[] = $line;
            }
            $file->next();
        }

        return $lines;
    }

    private function extractTimestamp(string $line): ?string
    {
        if (preg_match('/^\[(.+?)\]/', $line, $m)) {
            return $m[1];
        }

        return null;
    }
}
