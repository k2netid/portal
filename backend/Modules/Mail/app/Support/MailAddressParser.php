<?php

declare(strict_types=1);

namespace Modules\Mail\Support;

use InvalidArgumentException;

final class MailAddressParser
{
    /**
     * @return list<string>
     */
    public static function parseList(?string $raw): array
    {
        if ($raw === null || trim($raw) === '') {
            return [];
        }

        $parts = array_filter(array_map('trim', explode(',', $raw)));
        $valid = [];

        foreach ($parts as $part) {
            if (! filter_var($part, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException("Invalid email address: {$part}");
            }
            $valid[] = $part;
        }

        return $valid;
    }
}
