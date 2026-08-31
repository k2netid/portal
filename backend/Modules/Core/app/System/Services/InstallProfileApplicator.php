<?php

declare(strict_types=1);

namespace Modules\Core\System\Services;

use Illuminate\Support\Facades\Log;
use Modules\Core\System\Database\Seeders\CmsRolesSeeder;
use Modules\Core\System\Models\Extension;
use Modules\Core\System\Support\ExtensionFamilyCatalog;
use Throwable;

/**
 * Durable post-migrate / install applicator.
 *
 * Profiles **enforce desired product shape** (activate missing + deactivate extras):
 *
 * core     → discover; deactivate CMS family + Site → apex kernel landing
 * cms      → activate CMS family; deactivate Site → apex kernel landing
 * cms_site → activate CMS + Site → apex public theme
 */
class InstallProfileApplicator
{
    public const PROFILE_CORE = 'core';

    public const PROFILE_CMS = 'cms';

    public const PROFILE_CMS_SITE = 'cms_site';

    public function __construct(
        private ExtensionBootstrapService $bootstrap,
        private ExtensionGraphService $graph,
        private ExtensionHealthService $health,
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
     * Preview impact without mutating state.
     *
     * @return array{
     *   profile: string,
     *   mode: 'enforce',
     *   contract: 'enforce',
     *   will_activate: list<array{slug: string, name: string}>,
     *   will_deactivate: list<array{slug: string, name: string}>,
     *   already_active: list<array{slug: string, name: string}>,
     *   missing: list<string>,
     *   warnings: list<string>,
     *   blockers: list<string>,
     *   can_apply: bool,
     *   noop: bool,
     *   site_active: bool,
     *   cms_active_count: int
     * }
     */
    public function preview(string $profile): array
    {
        $profile = $this->resolveProfile($profile);
        $siteActive = Extension::isProductActive('site');
        $cmsActiveCount = Extension::query()
            ->where('family', ExtensionFamilyCatalog::CMS)
            ->where('status', 'active')
            ->where('is_core', false)
            ->count();

        $warnings = [];
        $blockers = [];
        $willActivate = [];
        $willDeactivate = [];
        $alreadyActive = [];
        $missing = [];

        if ($profile !== self::PROFILE_CORE) {
            $targets = $this->targetsFor($profile);
            $existing = Extension::query()->whereIn('slug', $targets)->get()->keyBy('slug');
            foreach ($targets as $slug) {
                if (! $existing->has($slug)) {
                    $missing[] = $slug;
                }
            }

            $planTargets = array_values(array_filter(
                $targets,
                static fn (string $slug): bool => $existing->has($slug),
            ));

            $plan = $this->graph->activationPlan($planTargets);
            foreach ($plan['will_activate'] as $row) {
                $willActivate[] = [
                    'slug' => $row['slug'],
                    'name' => $row['name'],
                ];
            }
            foreach ($plan['already_active'] ?? [] as $row) {
                $alreadyActive[] = [
                    'slug' => $row['slug'],
                    'name' => $row['name'],
                ];
            }

            if (($plan['can_cascade'] ?? false) !== true && ($willActivate !== [] || ($plan['missing'] ?? []) !== [] || ($plan['cycle'] ?? []) !== [])) {
                $blockers[] = $this->graph->planFailureMessage($plan);
            } elseif ($missing !== []) {
                $blockers[] = 'missing_targets:'.implode(',', $missing);
            }

            $skipLicense = (bool) config('install.skip_license_checks', false)
                && in_array(config('app.env'), ['local', 'testing'], true);
            if (! $skipLicense) {
                foreach ($willActivate as $row) {
                    $ext = $existing->get($row['slug']);
                    if ($ext === null) {
                        continue;
                    }
                    $license = $this->health->licenseBlocker($ext);
                    if ($license !== null) {
                        $blockers[] = $license;
                    }
                }
            } elseif ((bool) config('install.skip_license_checks', false)
                && ! in_array(config('app.env'), ['local', 'testing'], true)) {
                $warnings[] = 'license_skip_misconfigured';
            }
        }

        $extrude = $this->extrudeSlugs($profile);
        if ($extrude !== []) {
            $deactPlan = $this->graph->deactivationPlan($extrude);
            foreach ($deactPlan['will_deactivate'] as $row) {
                $willDeactivate[] = [
                    'slug' => $row['slug'],
                    'name' => $row['name'],
                ];
            }
            if ($profile === self::PROFILE_CMS && $siteActive) {
                $warnings[] = 'cms_will_disable_site';
            }
            if ($profile === self::PROFILE_CORE && ($cmsActiveCount > 0 || $siteActive)) {
                $warnings[] = 'core_will_disable_packs';
            }
        }

        $noop = $willActivate === [] && $willDeactivate === [] && $missing === [] && $blockers === [];
        if ($noop) {
            $warnings[] = 'profile_already_satisfied';
        }

        return [
            'profile' => $profile,
            'mode' => 'enforce',
            'contract' => 'enforce',
            'will_activate' => $willActivate,
            'will_deactivate' => $willDeactivate,
            'already_active' => $alreadyActive,
            'missing' => $missing,
            'warnings' => array_values(array_unique($warnings)),
            'blockers' => array_values(array_unique($blockers)),
            'can_apply' => $blockers === [],
            'noop' => $noop,
            'site_active' => $siteActive,
            'cms_active_count' => $cmsActiveCount,
        ];
    }

    /**
     * @return array{
     *   profile: string,
     *   discovered: int,
     *   activated: list<string>,
     *   deactivated: list<string>,
     *   skipped: list<string>,
     *   errors: list<string>,
     *   themes: array{scanned: int, active: string|null},
     *   preview: array<string, mixed>
     * }
     */
    public function apply(?string $profile = null): array
    {
        return ExtensionLifecycleLock::run(function () use ($profile): array {
            $profile = $this->resolveProfile($profile);
            $discovered = $this->bootstrap->discover();

            $preview = $this->preview($profile);
            if ($preview['can_apply'] !== true) {
                return [
                    'profile' => $profile,
                    'discovered' => $discovered,
                    'activated' => [],
                    'deactivated' => [],
                    'skipped' => [],
                    'errors' => $preview['blockers'],
                    'themes' => ['scanned' => 0, 'active' => null],
                    'preview' => $preview,
                ];
            }

            $activated = [];
            $deactivated = [];
            $skipped = [];
            $errors = [];

            if ($profile !== self::PROFILE_CORE) {
                $targets = $this->targetsFor($profile);
                $skipLicense = (bool) config('install.skip_license_checks', false)
                    && in_array(config('app.env'), ['local', 'testing'], true);
                $result = $this->bootstrap->activateTargets($targets, $skipLicense);
                $activated = $result['activated'];
                $skipped = $result['skipped'];
                $errors = $result['errors'];
            }

            // Enforce shape: turn off packs outside the profile (e.g. Site when choosing cms).
            if ($errors === []) {
                $extrude = $this->extrudeSlugs($profile);
                if ($extrude !== []) {
                    $down = $this->bootstrap->deactivateTargets($extrude);
                    $deactivated = $down['deactivated'];
                    $skipped = array_values(array_unique(array_merge($skipped, $down['skipped'])));
                    $errors = array_merge($errors, $down['errors']);
                }
            }

            $themes = $errors === []
                ? $this->ensureFrontendThemeBaseline($profile)
                : ['scanned' => 0, 'active' => null];

            if ($errors === [] && in_array($profile, [self::PROFILE_CMS, self::PROFILE_CMS_SITE], true)) {
                try {
                    (new CmsRolesSeeder)->run();
                } catch (Throwable $e) {
                    Log::warning('Install profile CMS roles seed failed', ['error' => $e->getMessage()]);
                }
            }

            Log::info('Install profile applied', [
                'profile' => $profile,
                'discovered' => $discovered,
                'activated' => $activated,
                'deactivated' => $deactivated,
                'errors' => $errors,
                'themes' => $themes,
                'warnings' => $preview['warnings'],
            ]);

            return [
                'profile' => $profile,
                'discovered' => $discovered,
                'activated' => $activated,
                'deactivated' => $deactivated,
                'skipped' => $skipped,
                'errors' => $errors,
                'themes' => $themes,
                'preview' => $preview,
            ];
        });
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
     * Packs that must be off for this profile (desired-state enforcement).
     *
     * @return list<string>
     */
    public function extrudeSlugs(string $profile): array
    {
        return match ($profile) {
            self::PROFILE_CMS => ['site'],
            self::PROFILE_CORE => array_values(array_unique([
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
