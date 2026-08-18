<?php

declare(strict_types=1);

namespace Modules\Core\System\Services;

use Illuminate\Support\Facades\Log;
use Modules\Core\Security\Models\SecurityLog;
use Modules\Core\Security\Services\SecurityService;

/**
 * Honeypot Service.
 * Manages "trap" paths that no legitimate user or bot should access.
 * Accessing these paths results in immediate permanent blocking.
 */
class HoneypotService
{
    public function __construct(protected SecurityService $security) {}

    /**
     * List of trap paths (bait).
     */
    public const TRAP_PATHS = [
        // Configuration & Environment
        '.env',
        '.git/config',
        'config/database.php',
        'composer.json',
        'package.json',

        // Common Jejakawan vulnerabilities targets (Bait for generic bots)
        'wp-login.php',
        'wp-admin/',
        'wp-content/plugins/',
        'xmlrpc.php',
        'info.php',
        'phpinfo.php',
        'shell.php',

        // Database & Backups
        'backup.sql',
        'db.sql',
        'database.sqlite',
        'dump.sql',
        'dump.gz',
        'backup.zip',
        'backup.tar.gz',

        // Admin Panels
        'phpmyadmin/',
        'pma/',
        'admin/phpmyadmin/',
        'administrator/index.php',

        // Suspicious hidden files
        '.ssh/id_rsa',
        '.aws/credentials',
        '.history',
    ];

    /**
     * Check if a path is a honeymoon trap.
     */
    public function isTrap(string $path): bool
    {
        $path = ltrim($path, '/');

        foreach (self::TRAP_PATHS as $trap) {
            if ($trap === $path || (str_ends_with($trap, '/') && str_starts_with($path, $trap))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Handle a honeymoon hit by blocking the IP.
     */
    public function handleHit(string $ip, string $path, string $userAgent): void
    {
        $reasonRaw = json_encode([
            'key' => 'features.security.reasons.honeypotTriggered',
            'params' => ['path' => $path],
        ]);
        $reason = is_string($reasonRaw) ? $reasonRaw : 'Honeypot trap';

        // Log the event specifically
        SecurityLog::log(
            'shield_honeypot',
            null,
            $ip,
            "Honeypot trap triggered: {$path}",
            ['path' => $path, 'ua' => $userAgent]
        );

        Log::channel('security')->alert("Honeypot trap triggered by {$ip}", [
            'path' => $path,
            'ua' => $userAgent,
        ]);

        // Immediate permanent block
        $this->security->blockIpPermanently($ip, $reason);
    }
}
