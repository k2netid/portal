<?php

declare(strict_types=1);

namespace Modules\Core\System\Services;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

class SandboxStorage
{
    /**
     * Build and return an isolated local storage disk for a specific extension slug.
     *
     * All read and write operations are strictly jailed inside:
     * storage/app/extensions/{slug}/sandbox/
     */
    public static function for(string $slug): Filesystem
    {
        // Sanitize slug to prevent path traversal attempts in slug parameter
        $safeSlug = preg_replace('/[^a-zA-Z0-9_-]/', '', $slug) ?? 'default';

        $rootPath = storage_path("app/extensions/{$safeSlug}/sandbox");

        // Ensure the sandbox folder physically exists
        if (! is_dir($rootPath)) {
            @mkdir($rootPath, 0755, true);
        }

        // Dynamically build a localized, jailed standard Filesystem disk
        return Storage::build([
            'driver' => 'local',
            'root' => $rootPath,
            'throw' => true,
        ]);
    }
}
