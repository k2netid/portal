<?php

declare(strict_types=1);

namespace Modules\Core\System\Services;

class ExtensionLogger
{
    /**
     * Log a message scoped strictly inside an extension's jailed VFS sandbox logs.
     * Writes to storage/app/extensions/{slug}/sandbox/logs/extension.log
     */
    public static function log(string $slug, string $message, string $level = 'INFO'): void
    {
        $disk = SandboxStorage::for($slug);

        $logLine = sprintf(
            "[%s] EXTENSION.%s: %s\n",
            now()->toDateTimeString(),
            strtoupper($level),
            $message
        );

        $disk->append('logs/extension.log', $logLine);
    }
}
