<?php

declare(strict_types=1);

namespace Modules\Mail\Support;

use InvalidArgumentException;

final class MailHostValidator
{
    /**
     * Reject hosts that resolve to private/link-local/metadata addresses (SSRF guard).
     */
    public static function assertReachablePublicHost(string $host): void
    {
        $host = trim(strtolower($host));

        if ($host === '' || $host === 'localhost' || str_ends_with($host, '.local')) {
            throw new InvalidArgumentException('Host is not allowed.');
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            self::assertPublicIp($host);

            return;
        }

        $resolved = @gethostbynamel($host);
        if ($resolved === false || $resolved === []) {
            throw new InvalidArgumentException('Host could not be resolved.');
        }

        foreach ($resolved as $ip) {
            self::assertPublicIp($ip);
        }
    }

    private static function assertPublicIp(string $ip): void
    {
        if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            throw new InvalidArgumentException('Host resolves to a private or reserved address.');
        }
    }
}
