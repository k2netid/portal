<?php

declare(strict_types=1);

namespace Modules\Core\System\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Modules\Core\System\Models\Extension;
use Modules\Core\System\Support\ExtensionFamilyCatalog;
use Spatie\Permission\PermissionRegistrar;

class ExtensionGraphService
{
    public const NAV_CACHE_KEY = 'extensions:sidebar_navigation';

    /**
     * @return array<string, string>
     */
    public function requiresOf(Extension $extension): array
    {
        $requirements = $extension->requirements;
        if (! is_array($requirements)) {
            return [];
        }

        $out = [];
        foreach ($requirements as $slug => $constraint) {
            if (! is_string($slug) || $slug === '' || ! is_scalar($constraint)) {
                continue;
            }
            $out[$slug] = (string) $constraint;
        }

        return $out;
    }

    /**
     * @return array<string, string>
     */
    public function suggestsOf(Extension $extension): array
    {
        $fromManifest = $extension->manifest['suggests'] ?? null;
        $fromSettings = is_array($extension->settings) ? ($extension->settings['suggests'] ?? null) : null;
        $raw = is_array($fromManifest) ? $fromManifest : (is_array($fromSettings) ? $fromSettings : []);

        $out = [];
        foreach ($raw as $slug => $constraint) {
            if (! is_string($slug) || $slug === '' || ! is_scalar($constraint)) {
                continue;
            }
            $out[$slug] = (string) $constraint;
        }

        return $out;
    }

    /**
     * Active extensions that hard-require this slug.
     *
     * @return list<Extension>
     */
    public function activeDependents(Extension $extension): array
    {
        $dependents = [];
        $candidates = Extension::query()
            ->where('status', 'active')
            ->where('slug', '!=', $extension->slug)
            ->get();

        foreach ($candidates as $candidate) {
            if (array_key_exists($extension->slug, $this->requiresOf($candidate))) {
                $dependents[] = $candidate;
            }
        }

        return $dependents;
    }

    /**
     * @throws Exception
     */
    public function assertCanDeactivate(Extension $extension): void
    {
        $dependents = $this->activeDependents($extension);
        if ($dependents === []) {
            return;
        }

        $names = array_map(
            static fn (Extension $dep): string => $dep->name.' ('.$dep->slug.')',
            $dependents,
        );

        throw new Exception(
            'Tidak dapat menonaktifkan: masih dipakai oleh '.implode(', ', $names).'. Nonaktifkan dependensi itu terlebih dahulu.',
        );
    }

    /**
     * Reverse-topo deactivation plan: active dependents first, then targets.
     * Includes reverse dependents outside the target set (e.g. Site when CMS is turned off).
     *
     * @param  list<string>  $targetSlugs
     * @return array{
     *     will_deactivate: list<array{slug: string, name: string, reason: string}>,
     *     already_inactive: list<array{slug: string, name: string}>,
     *     blocked_kernel: list<array{slug: string, name: string}>,
     *     can_cascade: bool
     * }
     */
    public function deactivationPlan(array $targetSlugs): array
    {
        $targets = [];
        foreach ($targetSlugs as $slug) {
            if (is_string($slug) && $slug !== '' && ! in_array($slug, $targets, true)) {
                $targets[] = $slug;
            }
        }

        $alreadyInactive = [];
        $blockedKernel = [];
        /** @var array<string, Extension> $closure */
        $closure = [];

        foreach ($targets as $slug) {
            $ext = Extension::query()->where('slug', $slug)->first();
            if ($ext === null) {
                continue;
            }
            if ($ext->is_core || in_array(strtolower($ext->slug), ['core', 'system', 'security', 'infra'], true)) {
                $blockedKernel[] = ['slug' => $ext->slug, 'name' => $ext->name];

                continue;
            }
            if ($ext->status !== 'active') {
                $alreadyInactive[] = ['slug' => $ext->slug, 'name' => $ext->name];

                continue;
            }
            $closure[$ext->slug] = $ext;
        }

        // Expand active reverse dependents until fixed point.
        $changed = true;
        while ($changed) {
            $changed = false;
            $active = Extension::query()
                ->where('status', 'active')
                ->where('is_core', false)
                ->get();
            foreach ($active as $candidate) {
                if (isset($closure[$candidate->slug])) {
                    continue;
                }
                if ($candidate->is_core) {
                    continue;
                }
                foreach ($this->requiresOf($candidate) as $depSlug => $_constraint) {
                    if (isset($closure[$depSlug])) {
                        $closure[$candidate->slug] = $candidate;
                        $changed = true;
                        break;
                    }
                }
            }
        }

        // Kahn reverse: repeatedly peel nodes with no remaining active dependents inside closure.
        $remaining = $closure;
        $ordered = [];
        $guard = 0;
        while ($remaining !== [] && $guard < 200) {
            $guard++;
            $peel = [];
            foreach ($remaining as $slug => $ext) {
                $hasDepInRemaining = false;
                foreach ($remaining as $other) {
                    if ($other->slug === $slug) {
                        continue;
                    }
                    if (array_key_exists($slug, $this->requiresOf($other))) {
                        $hasDepInRemaining = true;
                        break;
                    }
                }
                if (! $hasDepInRemaining) {
                    $peel[] = $slug;
                }
            }

            if ($peel === []) {
                // Cycle — fall back to reverse activation priority.
                $fallback = array_keys($remaining);
                usort($fallback, static function (string $a, string $b): int {
                    return ExtensionFamilyCatalog::activationPriority($b)
                        <=> ExtensionFamilyCatalog::activationPriority($a);
                });
                foreach ($fallback as $slug) {
                    $ext = $remaining[$slug];
                    $ordered[] = [
                        'slug' => $ext->slug,
                        'name' => $ext->name,
                        'reason' => in_array($slug, $targets, true) ? 'target' : 'dependent',
                    ];
                }
                $remaining = [];
                break;
            }

            usort($peel, static function (string $a, string $b): int {
                return ExtensionFamilyCatalog::activationPriority($b)
                    <=> ExtensionFamilyCatalog::activationPriority($a);
            });

            foreach ($peel as $slug) {
                $ext = $remaining[$slug];
                $ordered[] = [
                    'slug' => $ext->slug,
                    'name' => $ext->name,
                    'reason' => in_array($slug, $targets, true) ? 'target' : 'dependent',
                ];
                unset($remaining[$slug]);
            }
        }

        return [
            'will_deactivate' => $ordered,
            'already_inactive' => $alreadyInactive,
            'blocked_kernel' => $blockedKernel,
            'can_cascade' => $ordered !== [] || ($targets === [] && $alreadyInactive !== []),
        ];
    }

    /**
     * Topological activation plan for one or more target slugs (required deps only).
     *
     * @param  list<string>  $targetSlugs
     * @return array{
     *     will_activate: list<array{slug: string, name: string, reason: string}>,
     *     already_active: list<array{slug: string, name: string}>,
     *     missing: list<array{slug: string, required_by: string}>,
     *     version_conflicts: list<array{slug: string, name: string, constraint: string, version: string, required_by: string}>,
     *     cycle: list<string>,
     *     can_cascade: bool
     * }
     */
    public function activationPlan(array $targetSlugs): array
    {
        $targets = [];
        foreach ($targetSlugs as $slug) {
            if (is_string($slug) && $slug !== '' && ! in_array($slug, $targets, true)) {
                $targets[] = $slug;
            }
        }

        $missing = [];
        $versionConflicts = [];
        /** @var array<string, Extension> $nodes */
        $nodes = [];
        /** @var array<string, list<string>> $requiredBy */
        $requiredBy = [];

        $queue = $targets;
        $queued = array_fill_keys($targets, true);

        while ($queue !== []) {
            $slug = array_shift($queue);
            if (! is_string($slug) || $slug === '') {
                continue;
            }

            $ext = Extension::where('slug', $slug)->first();
            if ($ext === null) {
                $missing[] = [
                    'slug' => $slug,
                    'required_by' => '',
                ];

                continue;
            }

            $nodes[$slug] = $ext;
            $requiredBy[$slug] = $requiredBy[$slug] ?? [];

            foreach ($this->requiresOf($ext) as $depSlug => $constraint) {
                $requiredBy[$slug][] = $depSlug;
                $dep = Extension::where('slug', $depSlug)->first();
                if ($dep === null) {
                    $missing[] = [
                        'slug' => $depSlug,
                        'required_by' => $slug,
                    ];

                    continue;
                }

                if (! $this->versionSatisfies($dep->version, $constraint)) {
                    $versionConflicts[] = [
                        'slug' => $dep->slug,
                        'name' => $dep->name,
                        'constraint' => $constraint,
                        'version' => $dep->version,
                        'required_by' => $slug,
                    ];
                }

                if (! isset($queued[$depSlug])) {
                    $queued[$depSlug] = true;
                    $queue[] = $depSlug;
                }
            }
        }

        $order = [];
        $cycle = [];
        if ($nodes !== []) {
            [$order, $cycle] = $this->topologicalSort(array_keys($nodes), $requiredBy);
        }

        $willActivate = [];
        $alreadyActive = [];
        foreach ($order as $slug) {
            $ext = $nodes[$slug];
            if ($ext->status === 'active') {
                $alreadyActive[] = [
                    'slug' => $ext->slug,
                    'name' => $ext->name,
                ];

                continue;
            }

            $willActivate[] = [
                'slug' => $ext->slug,
                'name' => $ext->name,
                'reason' => in_array($slug, $targets, true) ? 'target' : 'required',
            ];
        }

        $runtimeConflicts = [];
        foreach ($willActivate as $row) {
            $ext = $nodes[$row['slug']] ?? null;
            if ($ext === null) {
                continue;
            }
            foreach ($this->runtimeBlockers($ext) as $blocker) {
                $runtimeConflicts[] = $blocker;
            }
        }

        $canCascade = $missing === [] && $versionConflicts === [] && $cycle === [] && $runtimeConflicts === [];

        return [
            'will_activate' => $willActivate,
            'already_active' => $alreadyActive,
            'missing' => $missing,
            'version_conflicts' => $versionConflicts,
            'runtime_conflicts' => $runtimeConflicts,
            'cycle' => $cycle,
            'can_cascade' => $canCascade,
        ];
    }

    /**
     * @param  array<string, mixed>  $plan
     */
    public function planFailureMessage(array $plan): string
    {
        $missing = is_array($plan['missing'] ?? null) ? $plan['missing'] : [];
        if ($missing !== []) {
            $labels = [];
            foreach ($missing as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $slug = isset($row['slug']) && is_string($row['slug']) ? $row['slug'] : '';
                $by = isset($row['required_by']) && is_string($row['required_by']) ? $row['required_by'] : '';
                if ($slug === '') {
                    continue;
                }
                $labels[] = $by !== '' ? $slug.' (dibutuhkan '.$by.')' : $slug;
            }

            return 'Dependensi tidak terpasang: '.implode(', ', $labels).'.';
        }

        $conflicts = is_array($plan['version_conflicts'] ?? null) ? $plan['version_conflicts'] : [];
        if ($conflicts !== []) {
            $first = $conflicts[0];
            $slug = is_array($first) && isset($first['slug']) && is_string($first['slug']) ? $first['slug'] : '';
            $constraint = is_array($first) && isset($first['constraint']) && is_string($first['constraint']) ? $first['constraint'] : '';
            $version = is_array($first) && isset($first['version']) && is_string($first['version']) ? $first['version'] : '';

            return "Konflik versi dependensi: '{$slug}' membutuhkan '{$constraint}', versi terpasang '{$version}'.";
        }

        $cycle = is_array($plan['cycle'] ?? null) ? $plan['cycle'] : [];
        if ($cycle !== []) {
            $labels = [];
            foreach ($cycle as $item) {
                if (is_string($item) && $item !== '') {
                    $labels[] = $item;
                }
            }

            return 'Siklus dependensi: '.implode(' → ', $labels).'.';
        }

        $runtime = is_array($plan['runtime_conflicts'] ?? null) ? $plan['runtime_conflicts'] : [];
        if ($runtime !== []) {
            $first = $runtime[0];
            $name = is_array($first) && isset($first['name']) && is_string($first['name']) ? $first['name'] : 'runtime';
            $constraint = is_array($first) && isset($first['constraint']) && is_string($first['constraint']) ? $first['constraint'] : '';
            $version = is_array($first) && isset($first['version']) && is_string($first['version']) ? $first['version'] : '';

            return "Constraint runtime tidak terpenuhi: {$name} membutuhkan '{$constraint}', saat ini '{$version}'.";
        }

        return 'Rencana aktivasi tidak dapat dijalankan.';
    }

    /**
     * @return array<string, string>
     */
    public function runtimeRequiresOf(Extension $extension): array
    {
        $raw = [];
        if (is_array($extension->manifest) && isset($extension->manifest['requires']) && is_array($extension->manifest['requires'])) {
            $raw = $extension->manifest['requires'];
        } elseif (is_array($extension->settings) && isset($extension->settings['runtime_requires']) && is_array($extension->settings['runtime_requires'])) {
            $raw = $extension->settings['runtime_requires'];
        }

        $out = [];
        foreach ($raw as $key => $constraint) {
            if (! is_string($key) || $key === '' || ! is_scalar($constraint)) {
                continue;
            }
            $normalized = strtolower($key);
            if ($normalized === 'laravel/framework') {
                $normalized = 'laravel';
            }
            if ($normalized === 'kernel') {
                $normalized = 'core';
            }
            if (! in_array($normalized, ['php', 'laravel', 'core'], true)) {
                continue;
            }
            $out[$normalized] = (string) $constraint;
        }

        return $out;
    }

    /**
     * @return list<array{slug: string, name: string, constraint: string, version: string, reason: string, satisfied: bool}>
     */
    public function runtimeBlockers(Extension $extension): array
    {
        $blockers = [];
        foreach ($this->runtimeRequiresOf($extension) as $key => $constraint) {
            $current = match ($key) {
                'php' => PHP_VERSION,
                'laravel' => app()->version(),
                'core' => $this->kernelVersion(),
                default => '',
            };
            if ($this->versionSatisfies($current, $constraint)) {
                continue;
            }
            $blockers[] = [
                'slug' => $key,
                'name' => $key,
                'constraint' => $constraint,
                'version' => $current,
                'reason' => 'runtime',
                'satisfied' => false,
            ];
        }

        return $blockers;
    }

    public function versionSatisfies(string $version, string $constraint): bool
    {
        $constraint = trim($constraint);
        if ($constraint === '' || $constraint === '*') {
            return true;
        }

        if (str_contains($constraint, '|')) {
            foreach (preg_split('/\s*\|\|?\s*/', $constraint) ?: [] as $part) {
                if ($part !== '' && $this->versionSatisfiesSingle($version, $part)) {
                    return true;
                }
            }

            return false;
        }

        return $this->versionSatisfiesSingle($version, $constraint);
    }

    private function kernelVersion(): string
    {
        $core = Extension::query()->where('slug', 'core')->value('version');
        if (is_string($core) && $core !== '') {
            return $core;
        }

        return '1.0.0-beta.1';
    }

    private function versionSatisfiesSingle(string $version, string $constraint): bool
    {
        $constraint = trim($constraint);
        if ($constraint === '' || $constraint === '*') {
            return true;
        }

        if (preg_match('/^([>=<]+)?\s*([0-9a-zA-Z.\-]+)$/', $constraint, $matches)) {
            $operator = $matches[1] !== '' ? $matches[1] : '=';

            return version_compare($version, $matches[2], $operator);
        }

        if (str_starts_with($constraint, '^')) {
            $reqVersion = substr($constraint, 1);
            if (version_compare($version, $reqVersion, '<')) {
                return false;
            }
            $parts = explode('.', $reqVersion);
            $nextMajor = ((int) $parts[0]) + 1;

            return version_compare($version, (string) $nextMajor, '<');
        }

        if (str_starts_with($constraint, '~')) {
            $reqVersion = substr($constraint, 1);
            if (version_compare($version, $reqVersion, '<')) {
                return false;
            }
            $parts = explode('.', $reqVersion);
            if (count($parts) >= 2) {
                return version_compare($version, $parts[0].'.'.(((int) $parts[1]) + 1).'.0', '<');
            }

            return true;
        }

        return version_compare($version, $constraint, '>=');
    }

    /**
     * @return array<string, mixed>
     */
    public function preview(Extension $extension, string $intent, bool $cascade = false): array
    {
        $intent = $intent === 'deactivate' ? 'deactivate' : 'activate';

        $requires = [];
        foreach ($this->requiresOf($extension) as $slug => $constraint) {
            $requires[] = $this->describeRelation($slug, $constraint);
        }

        $suggests = [];
        foreach ($this->suggestsOf($extension) as $slug => $constraint) {
            $suggests[] = $this->describeRelation($slug, $constraint);
        }

        $dependents = array_map(
            static fn (Extension $dep): array => [
                'slug' => $dep->slug,
                'name' => $dep->name,
                'status' => $dep->status,
            ],
            $this->activeDependents($extension),
        );

        $plan = $intent === 'activate'
            ? $this->activationPlan([$extension->slug])
            : [
                'will_activate' => [],
                'already_active' => [],
                'missing' => [],
                'version_conflicts' => [],
                'runtime_conflicts' => [],
                'cycle' => [],
                'can_cascade' => true,
            ];

        $blockers = [];
        if ($intent === 'activate') {
            foreach ($this->runtimeBlockers($extension) as $runtime) {
                $blockers[] = $runtime;
            }
            if ($cascade) {
                foreach ($plan['missing'] as $row) {
                    $blockers[] = [
                        'slug' => $row['slug'],
                        'name' => $row['slug'],
                        'reason' => 'not_installed',
                        'satisfied' => false,
                    ];
                }
                foreach ($plan['version_conflicts'] as $row) {
                    $blockers[] = [
                        'slug' => $row['slug'],
                        'name' => $row['name'],
                        'reason' => 'version_conflict',
                        'satisfied' => false,
                    ];
                }
                if ($plan['cycle'] !== []) {
                    $blockers[] = [
                        'slug' => implode(',', $plan['cycle']),
                        'name' => implode(' → ', $plan['cycle']),
                        'reason' => 'cycle',
                        'satisfied' => false,
                    ];
                }
            } else {
                foreach ($requires as $row) {
                    if ($row['satisfied'] !== true) {
                        $blockers[] = $row;
                    }
                }
            }
        }

        if ($intent === 'deactivate' && $dependents !== []) {
            foreach ($dependents as $dep) {
                $blockers[] = [
                    'slug' => $dep['slug'],
                    'name' => $dep['name'],
                    'reason' => 'active_dependent',
                    'satisfied' => false,
                ];
            }
        }

        return [
            'intent' => $intent,
            'slug' => $extension->slug,
            'name' => $extension->name,
            'can_proceed' => $blockers === [],
            'can_cascade' => $plan['can_cascade'] === true,
            'requires' => $requires,
            'suggests' => $suggests,
            'dependents' => $dependents,
            'blockers' => $blockers,
            'runtime' => $intent === 'activate' ? $this->runtimeBlockers($extension) : [],
            'cascade_plan' => $plan,
            'sidebar_note' => $intent === 'activate'
                ? 'Item sidebar yang terikat slug ini akan muncul setelah aktivasi.'
                : 'Item sidebar yang terikat slug ini akan disembunyikan setelah dinonaktifkan.',
        ];
    }

    public function forgetLifecycleCaches(): void
    {
        Cache::forget(self::NAV_CACHE_KEY);
        Cache::forget('system_settings_public');
        Cache::forget('system_settings_all');
        Extension::flushProductActiveMemo();
        try {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        } catch (\Throwable) {
            // Registrar may be unbound during early boot.
        }
    }

    /**
     * Kahn topo-sort. Edge dep → dependent (deps activate first).
     *
     * @param  list<string>  $slugs
     * @param  array<string, list<string>>  $requiredBy  slug => required slugs
     * @return array{0: list<string>, 1: list<string>}
     */
    private function topologicalSort(array $slugs, array $requiredBy): array
    {
        $adjacency = [];
        $indegree = [];
        foreach ($slugs as $slug) {
            $adjacency[$slug] = [];
            $indegree[$slug] = 0;
        }

        foreach ($requiredBy as $slug => $deps) {
            if (! isset($indegree[$slug])) {
                continue;
            }
            foreach ($deps as $dep) {
                if (! isset($indegree[$dep])) {
                    continue;
                }
                $adjacency[$dep][] = $slug;
                $indegree[$slug]++;
            }
        }

        $ready = [];
        foreach ($indegree as $slug => $degree) {
            if ($degree === 0) {
                $ready[] = $slug;
            }
        }

        $sortReady = static function (array &$ready): void {
            usort($ready, static function (string $a, string $b): int {
                $pa = ExtensionFamilyCatalog::activationPriority($a);
                $pb = ExtensionFamilyCatalog::activationPriority($b);
                if ($pa === $pb) {
                    return strcmp($a, $b);
                }

                return $pa <=> $pb;
            });
        };

        $sortReady($ready);
        $order = [];
        while ($ready !== []) {
            $slug = array_shift($ready);
            if (! is_string($slug)) {
                continue;
            }
            $order[] = $slug;
            foreach ($adjacency[$slug] as $next) {
                $indegree[$next]--;
                if ($indegree[$next] === 0) {
                    $ready[] = $next;
                    $sortReady($ready);
                }
            }
        }

        $cycle = array_values(array_diff($slugs, $order));

        return [$order, $cycle];
    }

    /**
     * @return array{slug: string, name: string, status: string, constraint: string, satisfied: bool, reason: string}
     */
    private function describeRelation(string $slug, string $constraint): array
    {
        $related = Extension::where('slug', $slug)->first();
        if ($related === null) {
            return [
                'slug' => $slug,
                'name' => $slug,
                'status' => 'missing',
                'constraint' => $constraint,
                'satisfied' => false,
                'reason' => 'not_installed',
            ];
        }

        $active = $related->status === 'active';

        return [
            'slug' => $related->slug,
            'name' => $related->name,
            'status' => $related->status,
            'constraint' => $constraint,
            'satisfied' => $active,
            'reason' => $active ? 'ok' : 'inactive',
        ];
    }
}
