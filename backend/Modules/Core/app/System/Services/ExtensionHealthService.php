<?php

declare(strict_types=1);

namespace Modules\Core\System\Services;

use Illuminate\Support\Collection;
use Modules\Core\System\Models\ConsoleMenu;
use Modules\Core\System\Models\Extension;

class ExtensionHealthService
{
    /**
     * Pack license_tier → minimum site license rank.
     *
     * @var array<string, int>
     */
    private const PACK_RANK = [
        'free' => 0,
        'pro' => 2,
        'pro_plus' => 3,
    ];

    /**
     * Site license_type → rank.
     *
     * @var array<string, int>
     */
    private const SITE_RANK = [
        LicenseService::TIER_COMMUNITY => 0,
        LicenseService::TIER_STARTER => 1,
        LicenseService::TIER_PRO => 2,
        LicenseService::TIER_ENTERPRISE => 4,
        LicenseService::TIER_WHITE_LABEL => 5,
    ];

    public function __construct(
        private LicenseService $license,
        private ExtensionGraphService $graph,
    ) {}

    /**
     * @param  Collection<int, Extension>  $extensions
     * @return Collection<int, Extension>
     */
    public function attach(Collection $extensions): Collection
    {
        $menus = ConsoleMenu::query()
            ->get(['id', 'group_slug', 'name', 'route_name', 'extension_slug', 'is_visible']);

        $expectedBySlug = $this->expectedMenuRouteNames();

        foreach ($extensions as $extension) {
            $extension->setAttribute(
                'health',
                $this->report($extension, $extensions, $menus, $expectedBySlug),
            );
        }

        return $extensions;
    }

    /**
     * @param  Collection<int, Extension>  $extensions
     * @param  Collection<int, ConsoleMenu>  $menus
     * @param  array<string, list<string>>  $expectedBySlug
     * @return array{status: string, issues: list<array{code: string, message: string}>}
     */
    public function report(
        Extension $extension,
        Collection $extensions,
        Collection $menus,
        array $expectedBySlug = [],
    ): array {
        $issues = [];

        $license = $this->licenseBlocker($extension);
        if ($license !== null) {
            $issues[] = ['code' => 'license', 'message' => $license];
        }

        foreach ($this->graph->runtimeBlockers($extension) as $blocker) {
            $issues[] = [
                'code' => 'runtime',
                'message' => "Constraint runtime {$blocker['name']} membutuhkan '{$blocker['constraint']}', saat ini '{$blocker['version']}'.",
            ];
        }

        $slug = $extension->slug;
        $routeConflicts = $this->routeConflictsFor($slug, $menus);
        foreach ($routeConflicts as $message) {
            $issues[] = ['code' => 'route_conflict', 'message' => $message];
        }

        $menuConflicts = $this->menuSlugConflictsFor($slug, $menus);
        foreach ($menuConflicts as $message) {
            $issues[] = ['code' => 'menu_conflict', 'message' => $message];
        }

        if ($extension->status === 'active') {
            $missingPerms = app(ExtensionContributionService::class)->missingPermissions($extension);
            if ($missingPerms !== []) {
                $issues[] = [
                    'code' => 'missing_permissions',
                    'message' => 'Permission belum di-seed: '.implode(', ', $missingPerms).'.',
                ];
            }

            $expected = $expectedBySlug[$slug] ?? [];
            if ($expected !== []) {
                $present = $menus
                    ->where('extension_slug', $slug)
                    ->pluck('route_name')
                    ->filter()
                    ->all();
                $missing = array_values(array_diff($expected, $present));
                if ($missing !== []) {
                    $issues[] = [
                        'code' => 'missing_menus',
                        'message' => 'Item menu belum lengkap: '.implode(', ', $missing).'.',
                    ];
                }
            }
        }

        $status = 'ok';
        foreach ($issues as $issue) {
            if (in_array($issue['code'], ['license', 'runtime', 'route_conflict', 'menu_conflict'], true)) {
                $status = 'error';
                break;
            }
            $status = 'warning';
        }

        return [
            'status' => $status,
            'issues' => $issues,
        ];
    }

    public function licenseBlocker(Extension $extension): ?string
    {
        if ($extension->is_core) {
            return null;
        }

        $packTier = $this->packLicenseTier($extension);
        if ($packTier === 'free') {
            return null;
        }

        $required = self::PACK_RANK[$packTier] ?? 0;
        $site = $this->license->getLicenseTier();
        $have = self::SITE_RANK[$site] ?? 0;
        if ($have >= $required) {
            return null;
        }

        return "Lisensi situs ({$site}) tidak mencukupi untuk paket '{$extension->slug}' (butuh {$packTier}).";
    }

    /**
     * @return list<string>
     */
    public function routeConflictsFor(string $slug, Collection $menus): array
    {
        $ours = $menus->where('extension_slug', $slug)->pluck('route_name')->filter()->unique()->all();
        if ($ours === []) {
            return [];
        }

        $messages = [];
        foreach ($ours as $route) {
            $claimants = $menus
                ->where('route_name', $route)
                ->pluck('extension_slug')
                ->filter()
                ->unique()
                ->reject(fn ($other) => $other === $slug)
                ->values()
                ->all();
            if ($claimants !== []) {
                $messages[] = "route_name '{$route}' juga diklaim oleh ".implode(', ', $claimants).'.';
            }
        }

        return $messages;
    }

    /**
     * @return list<string>
     */
    public function menuSlugConflictsFor(string $slug, Collection $menus): array
    {
        $ours = $menus->where('extension_slug', $slug);
        $messages = [];
        foreach ($ours as $item) {
            $dupes = $menus->filter(function (ConsoleMenu $other) use ($item, $slug): bool {
                return $other->extension_slug !== $slug
                    && $other->group_slug === $item->group_slug
                    && $other->name === $item->name
                    && is_string($other->extension_slug)
                    && $other->extension_slug !== '';
            });
            if ($dupes->isNotEmpty()) {
                $others = $dupes->pluck('extension_slug')->unique()->implode(', ');
                $messages[] = "Menu '{$item->name}' di grup {$item->group_slug} bentrok dengan {$others}.";
            }
        }

        return array_values(array_unique($messages));
    }

    /**
     * @return array<string, list<string>>
     */
    public function expectedMenuRouteNames(): array
    {
        $bySlug = [];
        foreach (ConsoleMenu::getDefaultMenus() as $group) {
            $children = $group['children'] ?? [];
            if (! is_array($children)) {
                continue;
            }
            foreach ($children as $child) {
                if (! is_array($child)) {
                    continue;
                }
                $ext = $child['extension_slug'] ?? null;
                $route = $child['route_name'] ?? null;
                if (! is_string($ext) || $ext === '' || ! is_string($route) || $route === '') {
                    continue;
                }
                $bySlug[$ext] ??= [];
                if (! in_array($route, $bySlug[$ext], true)) {
                    $bySlug[$ext][] = $route;
                }
            }
        }

        return $bySlug;
    }

    private function packLicenseTier(Extension $extension): string
    {
        $fromManifest = is_array($extension->manifest) ? ($extension->manifest['license_tier'] ?? null) : null;
        $fromSettings = is_array($extension->settings) ? ($extension->settings['license_tier'] ?? null) : null;
        $raw = is_string($fromManifest) ? $fromManifest : (is_string($fromSettings) ? $fromSettings : 'free');

        return in_array($raw, ['free', 'pro', 'pro_plus'], true) ? $raw : 'free';
    }
}
