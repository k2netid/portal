<?php

declare(strict_types=1);

namespace Modules\Core\System\Services;

use Illuminate\Support\Facades\Log;
use Modules\Core\System\Support\ExtensionFamilyCatalog;
use Throwable;

/**
 * Durable post-migrate / install applicator.
 *
 * core     → discover only; `/` stays console
 * cms      → CMS family active; `/` still console (Site off)
 * cms_site → CMS + Site; `/` = public theme runtime
 */
class InstallProfileApplicator
{
    public const PROFILE_CORE = 'core';

    public const PROFILE_CMS = 'cms';

    public const PROFILE_CMS_SITE = 'cms_site';

    public function __construct(
        private ExtensionBootstrapService $bootstrap,
    ) {}

    public function resolveProfile(?string $override = null): string
    {
        $raw = $override ?? config('install.profile', self::PROFILE_CORE);
        $profile = is_string($raw) ? strtolower(trim($raw)) : self::PROFILE_CORE;

        return in_array($profile, [self::PROFILE_CORE, self::PROFILE_CMS, self::PROFILE_CMS_SITE], true)
            ? $profile
            : self::PROFILE_CORE;
    }

    /**
     * @return array{
     *   profile: string,
     *   discovered: int,
     *   activated: list<string>,
     *   skipped: list<string>,
     *   errors: list<string>,
     *   themes: array{scanned: int, active: string|null}
     * }
     */
    public function apply(?string $profile = null): array
    {
        $profile = $this->resolveProfile($profile);
        $discovered = $this->bootstrap->discover();

        $activated = [];
        $skipped = [];
        $errors = [];

        if ($profile !== self::PROFILE_CORE) {
            $targets = $this->targetsFor($profile);
            $skipLicense = (bool) config('install.skip_license_checks', false);
            $result = $this->bootstrap->activateTargets($targets, $skipLicense);
            $activated = $result['activated'];
            $skipped = $result['skipped'];
            $errors = $result['errors'];
        }

        $themes = $this->ensureFrontendThemeBaseline($profile);

        Log::info('Install profile applied', [
            'profile' => $profile,
            'discovered' => $discovered,
            'activated' => $activated,
            'errors' => $errors,
            'themes' => $themes,
        ]);

        return [
            'profile' => $profile,
            'discovered' => $discovered,
            'activated' => $activated,
            'skipped' => $skipped,
            'errors' => $errors,
            'themes' => $themes,
        ];
    }

    /**
     * @return list<string>
     */
    public function targetsFor(string $profile): array
    {
        return match ($profile) {
            self::PROFILE_CMS => ExtensionFamilyCatalog::slugsInFamily(ExtensionFamilyCatalog::CMS),
            self::PROFILE_CMS_SITE => array_values(array_unique([
                ...ExtensionFamilyCatalog::slugsInFamily(ExtensionFamilyCatalog::CMS),
                'site',
            ])),
            default => [],
        };
    }

    /**
     * @return array{scanned: int, active: string|null}
     */
    private function ensureFrontendThemeBaseline(string $profile): array
    {
        if ($profile === self::PROFILE_CORE) {
            return ['scanned' => 0, 'active' => null];
        }

        if (! class_exists(\Modules\Layout\Services\ThemeService::class)) {
            return ['scanned' => 0, 'active' => null];
        }

        try {
            /** @var \Modules\Layout\Services\ThemeService $themes */
            $themes = app(\Modules\Layout\Services\ThemeService::class);
            $scanned = $themes->scanThemes();
            $active = $themes->ensureDefaultFrontendTheme();
            if (class_exists(\Modules\Layout\Services\ThemeCacheService::class)) {
                app(\Modules\Layout\Services\ThemeCacheService::class)->clearAll();
            }

            return [
                'scanned' => is_countable($scanned) ? count($scanned) : 0,
                'active' => $active?->slug,
            ];
        } catch (Throwable $e) {
            Log::warning('Install profile theme baseline failed', ['error' => $e->getMessage()]);

            return ['scanned' => 0, 'active' => null];
        }
    }
}
