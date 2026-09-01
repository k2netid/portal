<?php

declare(strict_types=1);

namespace Modules\Core\System\Services;

use Illuminate\Support\Facades\Cache;
use Modules\Core\System\Contracts\LoginThrottlePortInterface;
use Modules\Core\System\Models\Setting;

/**
 * Realm-aware login throttle (console | member) using the same System settings
 * as console SecurityService, without sharing IpList / User lock state across realms.
 */
final class LoginThrottleService implements LoginThrottlePortInterface
{
    private string $cachePrefix = 'auth_throttle:';

    private int $maxFailedAttempts;

    private int $baseBlockMinutes;

    private int $maxBlockMinutes = 60;

    private int $offenseResetHours = 24;

    public function __construct()
    {
        $this->maxFailedAttempts = is_scalar($v1 = Setting::get('login_attempts_limit', 5)) ? max(1, (int) $v1) : 5;
        $this->baseBlockMinutes = is_scalar($v2 = Setting::get('block_duration_minutes', 15)) ? max(1, (int) $v2) : 15;
    }

    public function blockedState(string $realm, string $email, string $ipAddress): ?array
    {
        $realm = $this->normalizeRealm($realm);

        if ($this->isBlocked($realm, $ipAddress, 'ip')) {
            $retry = $this->remainingSeconds($realm, $ipAddress, 'ip');

            return [
                'blocked' => true,
                'retry_after' => $retry,
                'message' => 'Too many failed login attempts. Please try again later.',
            ];
        }

        if ($this->isBlocked($realm, $email, 'email')) {
            $retry = $this->remainingSeconds($realm, $email, 'email');

            return [
                'blocked' => true,
                'retry_after' => $retry,
                'message' => 'Account temporarily locked due to too many failed login attempts.',
            ];
        }

        return null;
    }

    public function recordFailure(string $realm, string $email, string $ipAddress): array
    {
        $realm = $this->normalizeRealm($realm);
        $ipAttempts = $this->increment($realm, $ipAddress, 'ip');
        $emailAttempts = $this->increment($realm, $email, 'email');

        $retryAfter = 0;
        $blocked = false;

        if ($ipAttempts >= $this->maxFailedAttempts) {
            $retryAfter = max($retryAfter, $this->block($realm, $ipAddress, 'ip'));
            $blocked = true;
        }

        if ($emailAttempts >= $this->maxFailedAttempts) {
            $retryAfter = max($retryAfter, $this->block($realm, $email, 'email'));
            $blocked = true;
        }

        return [
            'blocked' => $blocked,
            'retry_after' => $retryAfter,
        ];
    }

    public function recordSuccess(string $realm, string $email, string $ipAddress): void
    {
        $realm = $this->normalizeRealm($realm);
        Cache::forget($this->attemptsKey($realm, $ipAddress, 'ip'));
        Cache::forget($this->attemptsKey($realm, $email, 'email'));
        Cache::forget($this->blockKey($realm, $ipAddress, 'ip'));
        Cache::forget($this->blockKey($realm, $email, 'email'));
    }

    private function normalizeRealm(string $realm): string
    {
        $clean = strtolower(trim($realm));

        return $clean !== '' ? preg_replace('/[^a-z0-9_\-]/', '', $clean) ?? 'default' : 'default';
    }

    private function attemptsKey(string $realm, string $identifier, string $type): string
    {
        return $this->cachePrefix."{$realm}:attempts:{$type}:".sha1(strtolower($identifier));
    }

    private function blockKey(string $realm, string $identifier, string $type): string
    {
        return $this->cachePrefix."{$realm}:block:{$type}:".sha1(strtolower($identifier));
    }

    private function offenseKey(string $realm, string $identifier, string $type): string
    {
        return $this->cachePrefix."{$realm}:offense:{$type}:".sha1(strtolower($identifier));
    }

    private function increment(string $realm, string $identifier, string $type): int
    {
        $key = $this->attemptsKey($realm, $identifier, $type);
        if (! Cache::has($key)) {
            Cache::put($key, 1, now()->addMinutes($this->baseBlockMinutes));

            return 1;
        }

        $newValue = Cache::increment($key);

        return is_int($newValue) ? $newValue : 1;
    }

    private function block(string $realm, string $identifier, string $type): int
    {
        $offenseKey = $this->offenseKey($realm, $identifier, $type);
        $current = Cache::get($offenseKey, 0);
        $offenseCount = (is_numeric($current) ? (int) $current : 0) + 1;
        Cache::put($offenseKey, $offenseCount, now()->addHours($this->offenseResetHours));

        $blockMinutes = min(
            $this->baseBlockMinutes * (2 ** ($offenseCount - 1)),
            $this->maxBlockMinutes,
        );

        Cache::put(
            $this->blockKey($realm, $identifier, $type),
            now()->addMinutes($blockMinutes)->toIso8601String(),
            $blockMinutes * 60,
        );

        return $blockMinutes * 60;
    }

    private function isBlocked(string $realm, string $identifier, string $type): bool
    {
        return Cache::has($this->blockKey($realm, $identifier, $type));
    }

    private function remainingSeconds(string $realm, string $identifier, string $type): int
    {
        $until = Cache::get($this->blockKey($realm, $identifier, $type));
        if (! is_string($until) || $until === '') {
            return $this->baseBlockMinutes * 60;
        }

        try {
            $seconds = now()->diffInSeconds(\Illuminate\Support\Carbon::parse($until), false);

            return $seconds > 0 ? (int) $seconds : $this->baseBlockMinutes * 60;
        } catch (\Throwable) {
            return $this->baseBlockMinutes * 60;
        }
    }
}
