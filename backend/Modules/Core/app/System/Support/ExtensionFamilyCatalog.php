<?php

declare(strict_types=1);

namespace Modules\Core\System\Support;

final class ExtensionFamilyCatalog
{
    public const PLATFORM = 'platform';

    public const CMS = 'cms';

    public const COMMUNICATIONS = 'communications';

    public const AUDIENCE = 'audience';

    public const MODULE = 'module';

    public const PLUGIN = 'plugin';

    /**
     * @var array<string, string>
     */
    private const SLUG_FAMILY = [
        'core' => self::PLATFORM,
        'library' => self::CMS,
        'publishing' => self::CMS,
        'media' => self::CMS,
        'layout' => self::CMS,
        'forms' => self::CMS,
        'newsletter' => self::CMS,
        'analytics' => self::CMS,
        'search' => self::CMS,
        'cms-ai' => self::CMS,
        'mail' => self::COMMUNICATIONS,
        'member' => self::AUDIENCE,
        'site' => self::AUDIENCE,
    ];

    public static function resolve(?string $manifestFamily, string $slug, string $type, bool $isCore): string
    {
        if (is_string($manifestFamily) && $manifestFamily !== '') {
            return $manifestFamily;
        }

        if ($isCore || strtolower($slug) === 'core') {
            return self::PLATFORM;
        }

        if ($type === 'plugin') {
            return self::PLUGIN;
        }

        return self::SLUG_FAMILY[strtolower($slug)] ?? self::MODULE;
    }

    /**
     * @return list<string>
     */
    public static function slugsInFamily(string $family): array
    {
        $family = strtolower($family);
        $slugs = [];
        foreach (self::SLUG_FAMILY as $slug => $mapped) {
            if ($mapped === $family) {
                $slugs[] = $slug;
            }
        }

        return $slugs;
    }

    /**
     * Stable tie-break so CMS cascade is library → publishing → the rest.
     */
    public static function activationPriority(string $slug): int
    {
        $order = [
            'library' => 10,
            'publishing' => 20,
            'media' => 30,
            'layout' => 40,
            'forms' => 50,
            'newsletter' => 60,
            'analytics' => 70,
            'search' => 80,
            'cms-ai' => 90,
        ];

        return $order[strtolower($slug)] ?? 500;
    }
}
