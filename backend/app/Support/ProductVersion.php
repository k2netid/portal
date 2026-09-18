<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Product SemVer for the Core Engine release line.
 *
 * SoT (when APP_VERSION unset): root package.json "version".
 * Keep frontend/package.json and backend/composer.json in sync — see
 * docs/guides/release-and-versioning.md and npm run versions:check.
 */
final class ProductVersion
{
    /**
     * SemVer core (optional pre-release / build), e.g. 1.0.0-beta.2
     */
    public const SEMVER_PATTERN = '/^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)(?:-((?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\.(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*))?(?:\+([0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?$/';

    public static function readPackageJson(): string
    {
        $path = dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'package.json';
        $real = realpath($path);
        if ($real === false || ! is_readable($real)) {
            return '0.0.0';
        }

        $data = json_decode((string) file_get_contents($real), true);
        if (! is_array($data) || ! isset($data['version']) || ! is_string($data['version']) || $data['version'] === '') {
            return '0.0.0';
        }

        return $data['version'];
    }

    public static function isSemVer(string $version): bool
    {
        return (bool) preg_match(self::SEMVER_PATTERN, $version);
    }
}
