<?php

declare(strict_types=1);

namespace Modules\Core\System\Services\Ai;

use Modules\Core\System\Models\Setting;

/**
 * Shared availability checks for Core AI (Settings → AI).
 */
class AiAvailability
{
    public static function isGloballyEnabled(): bool
    {
        $value = Setting::get('ai_enabled', false);

        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            return ! in_array(strtolower($value), ['0', 'false', 'off', ''], true);
        }

        return (bool) $value;
    }

    public static function defaultProvider(): string
    {
        $provider = Setting::get('ai_default_provider', 'gemini');

        return is_string($provider) && $provider !== '' ? $provider : 'gemini';
    }

    public static function providerHasKey(string $provider): bool
    {
        $key = Setting::get($provider.'_api_key');

        return is_string($key) && trim($key) !== '';
    }

    public static function isReady(?string $provider = null): bool
    {
        if (! self::isGloballyEnabled()) {
            return false;
        }

        $slug = is_string($provider) && $provider !== '' ? $provider : self::defaultProvider();

        return self::providerHasKey($slug);
    }
}
