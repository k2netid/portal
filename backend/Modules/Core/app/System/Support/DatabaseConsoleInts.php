<?php

declare(strict_types=1);

namespace Modules\Core\System\Support;

/**
 * Narrow mixed values from Artisan options, settings, and query delete()/count()
 * results for Larastan-friendly console output (no blind mixed→int casts).
 */
final class DatabaseConsoleInts
{
    public static function fromDeleteResult(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }

        return 0;
    }

    public static function fromMixed(mixed $raw, int $default): int
    {
        if (is_int($raw)) {
            return $raw;
        }
        if (is_float($raw)) {
            return (int) $raw;
        }
        if (is_string($raw) && is_numeric($raw)) {
            return (int) $raw;
        }

        return $default;
    }
}
