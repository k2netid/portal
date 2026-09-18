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
     * First-party theme packs — registered in Module Registry as type=theme.
     *
     * @see ADR-023: First-party themes as Module Registry packs + license quotas
     */
    public const THEME = 'theme';

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
        // First-party theme packs (ADR-023)
        'theme-janari' => self::THEME,
        'theme-layung' => self::THEME,
        'theme-sarangenge' => self::THEME,
        'theme-sareupna' => self::THEME,
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

        if ($type === 'theme') {
            return self::THEME;
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
     * Theme packs are ordered after layout (their required dependency).
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
            // Theme packs activate after layout (ADR-023)
            'theme-janari' => 110,
            'theme-layung' => 120,
            'theme-sarangenge' => 130,
            'theme-sareupna' => 140,
        ];

        return $order[strtolower($slug)] ?? 500;
    }

    /**
     * Slugs of all first-party theme packs (ADR-023).
     *
     * @return list<string>
     */
    public static function firstPartyThemePackSlugs(): array
    {
        return array_keys(array_filter(
            self::SLUG_FAMILY,
            static fn (string $family): bool => $family === self::THEME,
        ));
    }

    /**
     * Slugs of premium (non-baseline) first-party theme packs.
     * janari = baseline / always-on, therefore excluded.
     *
     * @return list<string>
     */
    public static function premiumThemePackSlugs(): array
    {
        return array_values(array_filter(
            self::firstPartyThemePackSlugs(),
            static fn (string $slug): bool => $slug !== 'theme-janari',
        ));
    }
}
