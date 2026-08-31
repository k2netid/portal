<?php

declare(strict_types=1);

namespace Modules\Core\System\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Modules\Core\System\Models\ConsoleMenu;
use Modules\Core\System\Models\Extension;
use Modules\Core\System\Models\ExtensionLog;
use Modules\Core\System\Support\ExtensionFamilyCatalog;
use Modules\Core\System\Support\ExtensionPaths;
use Throwable;

/**
 * First-party module discovery + activation for install/seed paths.
 * Keeps operators off tinker after migrate:fresh.
 */
class ExtensionBootstrapService
{
    /** @var list<string> */
    private const KERNEL_SLUGS = ['core', 'system', 'security', 'infra'];

    public function __construct(
        private ExtensionGraphService $graph,
        private ExtensionHealthService $health,
        private ExtensionContributionService $contributions,
    ) {}

    /**
     * Sync Modules/* (+ plugins) into sys_extensions without activating packs.
     *
     * @return int Number of rows touched
     */
    public function discover(): int
    {
        $discovered = [];

        $modulesPath = base_path('Modules');
        if (is_dir($modulesPath)) {
            foreach (File::directories($modulesPath) as $dir) {
                $manifestFile = $dir.'/manifest.json';
                if (! File::exists($manifestFile)) {
                    $manifestFile = $dir.'/module.json';
                }
                if (! File::exists($manifestFile)) {
                    continue;
                }

                $manifest = $this->decodeJson(File::get($manifestFile));
                if ($manifest === null) {
                    continue;
                }

                $slug = $manifest['slug'] ?? $manifest['alias'] ?? null;
                if (! is_string($slug) || $slug === '') {
                    continue;
                }

                $meta = $this->extractMeta($manifest, $slug);
                $discovered[$slug] = $meta;
            }
        }

        foreach (ExtensionPaths::discoverPluginPackageDirectories() as $dir) {
            $manifestFile = $dir.'/manifest.json';
            if (! File::exists($manifestFile)) {
                continue;
            }
            $manifest = $this->decodeJson(File::get($manifestFile));
            if ($manifest === null) {
                continue;
            }
            $slug = $manifest['slug'] ?? null;
            if (! is_string($slug) || $slug === '') {
                continue;
            }
            $meta = $this->extractMeta($manifest, $slug, defaultType: 'plugin');
            $meta['is_core'] = false;
            $discovered[$slug] = $meta;
        }

        $count = 0;
        foreach ($discovered as $slug => $meta) {
            $existing = Extension::query()->where('slug', $slug)->first();
            $isKernel = $meta['is_core'] || $this->isKernelSlug($slug);
            $status = $isKernel
                ? 'active'
                : ($existing !== null ? (string) $existing->status : 'inactive');

            Extension::updateOrCreate(
                ['slug' => $slug],
                [
                    'type' => $meta['type'],
                    'family' => $meta['family'],
                    'name' => $meta['name'],
                    'version' => $meta['version'],
                    'database_version' => $existing?->database_version ?? '1.0.0',
                    'status' => $status,
                    'is_core' => $isKernel,
                    'author' => $meta['author'],
                    'description' => $meta['description'],
                    'license' => $meta['license'] ?? ($isKernel ? 'Platform' : 'Proprietary'),
                    'requirements' => $meta['requirements'],
                    'manifest' => [
                        'settings_route' => $meta['settings_route'],
                        'license_tier' => $meta['license_tier'],
                        'suggests' => $meta['suggests'],
                        'requires' => $meta['runtime_requires'],
                        'permissions' => $meta['permissions'],
                    ],
                    'settings' => array_filter([
                        'settings_route' => $meta['settings_route'],
                        'license_tier' => $meta['license_tier'],
                        'suggests' => $meta['suggests'] !== [] ? $meta['suggests'] : null,
                        'runtime_requires' => $meta['runtime_requires'] !== [] ? $meta['runtime_requires'] : null,
                        'permissions' => $meta['permissions'] !== [] ? $meta['permissions'] : null,
                    ], static fn ($v) => $v !== null && $v !== '' && $v !== []),
                ]
            );
            $count++;
        }

        return $count;
    }

    /**
     * Activate targets (and dependencies) in graph order.
     *
     * @param  list<string>  $targetSlugs
     * @return array{activated: list<string>, skipped: list<string>, errors: list<string>}
     */
    public function activateTargets(array $targetSlugs, bool $skipLicenseChecks = false): array
    {
        $targetSlugs = array_values(array_unique(array_filter($targetSlugs, static fn ($s) => is_string($s) && $s !== '')));
        if ($targetSlugs === []) {
            return ['activated' => [], 'skipped' => [], 'errors' => []];
        }

        $plan = $this->graph->activationPlan($targetSlugs);
        if ($plan['can_cascade'] !== true) {
            return [
                'activated' => [],
                'skipped' => [],
                'errors' => [$this->graph->planFailureMessage($plan)],
            ];
        }

        $activated = [];
        $skipped = [];
        $errors = [];

        foreach ($plan['will_activate'] as $row) {
            $slug = $row['slug'];
            $extension = Extension::query()->where('slug', $slug)->first();
            if ($extension === null) {
                $errors[] = "Missing extension row: {$slug}";
                continue;
            }
            if ($extension->status === 'active') {
                $skipped[] = $slug;
                continue;
            }

            if (! $skipLicenseChecks) {
                $licenseBlock = $this->health->licenseBlocker($extension);
                if ($licenseBlock !== null) {
                    $errors[] = $licenseBlock;
                    continue;
                }
            }

            $runtime = $this->graph->runtimeBlockers($extension);
            if ($runtime !== []) {
                $errors[] = $this->graph->planFailureMessage([
                    'runtime_conflicts' => $runtime,
                    'missing' => [],
                    'version_conflicts' => [],
                    'cycle' => [],
                ]);
                continue;
            }

            try {
                $this->performActivation($extension, array_column($plan['will_activate'], 'slug'));
                $activated[] = $slug;
            } catch (Throwable $e) {
                $errors[] = "{$slug}: ".$e->getMessage();
                Log::error('Install profile activation failed', [
                    'slug' => $slug,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        ConsoleMenu::applyActiveExtensionVisibility();

        return compact('activated', 'skipped', 'errors');
    }

    /**
     * Deactivate targets (and active reverse dependents) in reverse-dependency order.
     *
     * @param  list<string>  $targetSlugs
     * @return array{deactivated: list<string>, skipped: list<string>, errors: list<string>}
     */
    public function deactivateTargets(array $targetSlugs): array
    {
        $targetSlugs = array_values(array_unique(array_filter($targetSlugs, static fn ($s) => is_string($s) && $s !== '')));
        if ($targetSlugs === []) {
            return ['deactivated' => [], 'skipped' => [], 'errors' => []];
        }

        $plan = $this->graph->deactivationPlan($targetSlugs);
        $deactivated = [];
        $skipped = [];
        $errors = [];

        foreach ($plan['already_inactive'] ?? [] as $row) {
            $skipped[] = $row['slug'];
        }

        foreach ($plan['will_deactivate'] as $row) {
            $slug = $row['slug'];
            $extension = Extension::query()->where('slug', $slug)->first();
            if ($extension === null) {
                $errors[] = "Missing extension row: {$slug}";
                continue;
            }
            if ($extension->status !== 'active') {
                $skipped[] = $slug;
                continue;
            }
            if ($extension->is_core || $this->isKernelSlug($slug)) {
                $errors[] = "Cannot deactivate kernel: {$slug}";
                continue;
            }

            try {
                $this->graph->assertCanDeactivate($extension);
                \Hook::action('extension_deactivated', $extension);
                $extension->update(['status' => 'inactive']);
                ConsoleMenu::syncVisibilityForExtension($extension->slug, false);
                ExtensionLog::create([
                    'extension_slug' => $extension->slug,
                    'action' => 'deactivate',
                    'version_before' => $extension->version,
                    'version_after' => $extension->version,
                    'status' => 'success',
                    'performed_by' => null,
                    'meta' => ['source' => 'install_profile', 'cascade' => array_column($plan['will_deactivate'], 'slug')],
                ]);
                $deactivated[] = $slug;
            } catch (Throwable $e) {
                $errors[] = "{$slug}: ".$e->getMessage();
                Log::error('Install profile deactivation failed', [
                    'slug' => $slug,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->graph->forgetLifecycleCaches();
        ConsoleMenu::applyActiveExtensionVisibility();

        return compact('deactivated', 'skipped', 'errors');
    }

    /**
     * @param  list<string>  $cascadeSlugs
     */
    public function performActivation(Extension $extension, array $cascadeSlugs = []): Extension
    {
        $versionBefore = $extension->version;

        $migrationPath = $extension->type === 'module'
            ? base_path('Modules/'.str_replace(' ', '', ucwords(str_replace('-', ' ', $extension->slug))).'/database/migrations')
            : ExtensionPaths::pluginMigrationsDirectory($extension->slug);

        if (is_dir($migrationPath)) {
            Artisan::call('migrate', [
                '--path' => str_replace(base_path().'/', '', $migrationPath),
                '--force' => true,
            ]);
        }

        \Hook::action('extension_activated', $extension);
        $this->contributions->seedPermissions($extension);
        app(ExtensionLifecycleOrchestrator::class)->runActivateSeeders($extension);

        $extension->update([
            'status' => 'active',
            'database_version' => $extension->version,
        ]);

        ExtensionLog::create([
            'extension_slug' => $extension->slug,
            'action' => 'activate',
            'version_before' => $versionBefore,
            'version_after' => $extension->version,
            'status' => 'success',
            'performed_by' => null,
            'meta' => $cascadeSlugs === [] ? null : ['cascade' => $cascadeSlugs, 'source' => 'install_profile'],
        ]);

        $this->graph->forgetLifecycleCaches();
        ConsoleMenu::ensureMissingDefaults();
        ConsoleMenu::syncVisibilityForExtension($extension->slug, true);

        return $extension->fresh() ?? $extension;
    }

    private function isKernelSlug(string $slug): bool
    {
        return in_array(strtolower($slug), self::KERNEL_SLUGS, true);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function decodeJson(string $json): ?array
    {
        try {
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return null;
        }

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * @param  array<string, mixed>  $manifest
     * @return array{
     *   type: string,
     *   name: string,
     *   version: string,
     *   author: string,
     *   description: string|null,
     *   license: string|null,
     *   license_tier: string|null,
     *   settings_route: string|null,
     *   is_core: bool,
     *   family: string,
     *   requirements: array<string, string>,
     *   suggests: array<string, string>,
     *   runtime_requires: array<string, string>,
     *   permissions: list<string>
     * }
     */
    private function extractMeta(array $manifest, string $slug, string $defaultType = 'module'): array
    {
        $type = $defaultType;
        if (isset($manifest['type']) && is_string($manifest['type'])
            && in_array($manifest['type'], ['module', 'plugin'], true)) {
            $type = $manifest['type'];
        }

        $isCore = array_key_exists('is_core', $manifest)
            && filter_var($manifest['is_core'], FILTER_VALIDATE_BOOLEAN);

        $familyRaw = $manifest['family'] ?? null;
        $family = ExtensionFamilyCatalog::resolve(
            is_string($familyRaw) ? $familyRaw : null,
            $slug,
            $type,
            $isCore,
        );

        $requirements = [];
        $dependencies = $manifest['dependencies'] ?? null;
        if (is_array($dependencies)) {
            foreach ($dependencies as $depSlug => $constraint) {
                if (is_string($depSlug) && is_scalar($constraint)) {
                    $requirements[$depSlug] = (string) $constraint;
                }
            }
        }

        $suggests = [];
        $suggestsRaw = $manifest['suggests'] ?? null;
        if (is_array($suggestsRaw)) {
            foreach ($suggestsRaw as $sugSlug => $constraint) {
                if (is_string($sugSlug) && is_scalar($constraint)) {
                    $suggests[$sugSlug] = (string) $constraint;
                }
            }
        }

        $runtimeRequires = [];
        $requiresRaw = $manifest['requires'] ?? null;
        if (is_array($requiresRaw)) {
            foreach ($requiresRaw as $reqKey => $constraint) {
                if (! is_string($reqKey) || ! is_scalar($constraint)) {
                    continue;
                }
                $normalized = strtolower($reqKey);
                if ($normalized === 'laravel/framework') {
                    $normalized = 'laravel';
                }
                if ($normalized === 'kernel') {
                    $normalized = 'core';
                }
                if (in_array($normalized, ['php', 'laravel', 'core'], true)) {
                    $runtimeRequires[$normalized] = (string) $constraint;
                }
            }
        }

        $permissions = [];
        $permissionsRaw = $manifest['permissions'] ?? null;
        if (is_array($permissionsRaw)) {
            foreach ($permissionsRaw as $permName) {
                if (is_string($permName) && $permName !== '') {
                    $permissions[] = $permName;
                }
            }
        }

        return [
            'type' => $type,
            'name' => isset($manifest['name']) && is_string($manifest['name']) ? $manifest['name'] : $slug,
            'version' => isset($manifest['version']) && is_string($manifest['version']) ? $manifest['version'] : '1.0.0',
            'author' => isset($manifest['author']) && is_string($manifest['author']) ? $manifest['author'] : 'jejakawan',
            'description' => isset($manifest['description']) && is_string($manifest['description']) ? $manifest['description'] : null,
            'license' => isset($manifest['license']) && is_string($manifest['license']) ? $manifest['license'] : null,
            'license_tier' => isset($manifest['license_tier']) && is_string($manifest['license_tier']) ? $manifest['license_tier'] : null,
            'settings_route' => isset($manifest['settings_route']) && is_string($manifest['settings_route']) ? $manifest['settings_route'] : null,
            'is_core' => $isCore,
            'family' => $family,
            'requirements' => $requirements,
            'suggests' => $suggests,
            'runtime_requires' => $runtimeRequires,
            'permissions' => array_values(array_unique($permissions)),
        ];
    }
}
