<?php

declare(strict_types=1);

namespace Modules\Core\System\Services;

use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Throwable;

/**
 * Serializes extension activate / deactivate / install-profile mutations.
 * Prevents two operators (or double-submit) from racing migrations + status flips.
 */
final class ExtensionLifecycleLock
{
    public const KEY = 'ja:extensions:lifecycle';

    /**
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     *
     * @throws RuntimeException when another lifecycle operation holds the lock
     */
    public static function run(callable $callback, int $seconds = 180): mixed
    {
        $lock = Cache::lock(self::KEY, $seconds);

        if (! $lock->get()) {
            throw new RuntimeException(
                'Operasi ekstensi sedang berjalan. Tunggu selesai lalu coba lagi.',
            );
        }

        try {
            return $callback();
        } finally {
            try {
                $lock->release();
            } catch (Throwable) {
                // Lock may already have expired; ignore.
            }
        }
    }
}
