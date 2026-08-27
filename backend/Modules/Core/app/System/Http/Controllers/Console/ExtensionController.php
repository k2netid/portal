<?php

declare(strict_types=1);

namespace Modules\Core\System\Http\Controllers\Console;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Modules\Core\System\Helpers\IpHelper;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Models\ConsoleMenu;
use Modules\Core\System\Models\Extension;
use Modules\Core\System\Models\ExtensionLog;
use Modules\Core\System\Models\Feature;
use Modules\Core\System\Models\Setting;
use Modules\Core\System\Services\ExtensionContributionService;
use Modules\Core\System\Services\ExtensionGraphService;
use Modules\Core\System\Services\ExtensionHealthService;
use Modules\Core\System\Services\ExtensionSecurityScanner;
use Modules\Core\System\Support\ExtensionFamilyCatalog;
use Modules\Core\System\Support\ExtensionPaths;
use ZipArchive;

class ExtensionController extends BaseApiController
{
    /**
     * Kernel packages that must stay active and cannot be uninstalled.
     * Primary: consolidated Modules/Core (alias `core`).
     * Legacy: old CMS split packages if they still appear on disk.
     *
     * @var list<string>
     */
    private const KERNEL_SLUGS = ['core', 'system', 'security', 'infra'];

    /**
     * List all extensions (combining database statuses and physical folder discovery).
     */
    public function index(): JsonResponse
    {
        $this->discoverExtensions();

        $extensions = Extension::with('features')->latest()->get();
        app(ExtensionHealthService::class)->attach($extensions);
        $extensions->each(function (Extension $extension): void {
            $extension->setAttribute('can_uninstall', $this->canUninstall($extension));
        });

        return $this->success($extensions, 'Extensions retrieved successfully');
    }

    /**
     * Whether a slug is part of the always-on platform kernel.
     */
    protected function isKernelSlug(string $slug): bool
    {
        return in_array(strtolower($slug), self::KERNEL_SLUGS, true);
    }

    /**
     * In-tree Modules/* packs stay on disk. Deactivate hides them; uninstall is plugins only.
     */
    protected function isShippedFirstPartyModule(Extension $extension): bool
    {
        if ($extension->type !== 'module') {
            return false;
        }

        $folder = base_path('Modules/'.str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $extension->slug))));

        return is_dir($folder);
    }

    protected function canUninstall(Extension $extension): bool
    {
        if ($extension->is_core || $this->isKernelSlug($extension->slug)) {
            return false;
        }

        return ! $this->isShippedFirstPartyModule($extension);
    }

    /**
     * Refuse lifecycle mutations that would disable or delete the kernel.
     */
    protected function guardKernelLifecycle(Extension $extension, string $action): ?JsonResponse
    {
        if (! $extension->is_core && ! $this->isKernelSlug($extension->slug)) {
            return null;
        }

        return $this->error("Platform kernel modules cannot be {$action}");
    }

    /**
     * Activate a module/plugin. Pass cascade=1 to activate required deps first (topo order).
     */
    public function activate(Request $request, string $slug): JsonResponse
    {
        $extension = Extension::where('slug', $slug)->firstOrFail();
        $cascade = $request->boolean('cascade');
        $graph = app(ExtensionGraphService::class);
        $failedSlug = $extension->slug;
        $versionBefore = $extension->version;

        try {
            if ($cascade) {
                $plan = $graph->activationPlan([$extension->slug]);
                if ($plan['can_cascade'] !== true) {
                    throw new Exception($graph->planFailureMessage($plan));
                }
                if ($plan['will_activate'] === []) {
                    return $this->error('Extension is already active');
                }

                $activatedRows = $this->activatePlanWithRollback($plan['will_activate']);
                $last = $activatedRows !== [] ? Extension::where('slug', $activatedRows[array_key_last($activatedRows)]['slug'])->first() : $extension;

                return $this->success($last ?? $extension, 'Extension activated successfully');
            }

            if ($extension->status === 'active') {
                return $this->error('Extension is already active');
            }

            $licenseBlock = app(ExtensionHealthService::class)->licenseBlocker($extension);
            if ($licenseBlock !== null) {
                throw new Exception($licenseBlock);
            }

            $this->verifyDependencies($extension);
            $runtime = $graph->runtimeBlockers($extension);
            if ($runtime !== []) {
                throw new Exception($graph->planFailureMessage([
                    'runtime_conflicts' => $runtime,
                    'missing' => [],
                    'version_conflicts' => [],
                    'cycle' => [],
                ]));
            }
            $activated = $this->performActivation($extension);

            return $this->success($activated, 'Extension activated successfully');
        } catch (Exception $e) {
            ExtensionLog::create([
                'extension_slug' => $failedSlug,
                'action' => 'activate',
                'version_before' => $versionBefore,
                'version_after' => $extension->version,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'performed_by' => auth()->id(),
            ]);

            return $this->error('Failed to activate extension: '.$e->getMessage());
        }
    }

    /**
     * Deactivate a module/plugin.
     */
    public function deactivate(string $slug): JsonResponse
    {
        $extension = Extension::where('slug', $slug)->firstOrFail();

        if ($guard = $this->guardKernelLifecycle($extension, 'deactivated')) {
            return $guard;
        }

        if ($extension->status !== 'active') {
            return $this->error('Extension is not active');
        }

        try {
            app(ExtensionGraphService::class)->assertCanDeactivate($extension);

            // 1. Trigger onDeactivate lifecycle event/hook
            \Hook::action('extension_deactivated', $extension);

            // 2. Update status
            $extension->update(['status' => 'inactive']);

            ExtensionLog::create([
                'extension_slug' => $extension->slug,
                'action' => 'deactivate',
                'version_before' => $extension->version,
                'version_after' => $extension->version,
                'status' => 'success',
                'performed_by' => auth()->id(),
            ]);

            app(ExtensionGraphService::class)->forgetLifecycleCaches();
            ConsoleMenu::syncVisibilityForExtension($extension->slug, false);

            return $this->success($extension, 'Extension deactivated successfully');

        } catch (Exception $e) {
            ExtensionLog::create([
                'extension_slug' => $extension->slug,
                'action' => 'deactivate',
                'version_before' => $extension->version,
                'version_after' => $extension->version,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'performed_by' => auth()->id(),
            ]);

            return $this->error('Failed to deactivate extension: '.$e->getMessage());
        }
    }

    /**
     * Preview activate/deactivate impact (requires, suggests, reverse dependents).
     */
    public function lifecyclePreview(Request $request, string $slug): JsonResponse
    {
        $extension = Extension::where('slug', $slug)->firstOrFail();
        $intent = is_string($request->query('intent')) ? (string) $request->query('intent') : 'activate';

        $preview = app(ExtensionGraphService::class)->preview($extension, $intent, $request->boolean('cascade'));
        if ($intent === 'activate') {
            $license = app(ExtensionHealthService::class)->licenseBlocker($extension);
            if ($license !== null) {
                $preview['license'] = $license;
                $preview['can_proceed'] = false;
                $preview['blockers'][] = [
                    'slug' => 'license',
                    'name' => 'license',
                    'reason' => 'license',
                    'satisfied' => false,
                ];
            }
        }

        return $this->success($preview, 'Lifecycle preview');
    }

    /**
     * Preview topo-sorted activation for a family or explicit slug list.
     */
    public function activationPlan(Request $request): JsonResponse
    {
        $targets = $this->resolveActivationTargets($request);
        $plan = app(ExtensionGraphService::class)->activationPlan($targets);

        return $this->success($plan, 'Activation plan');
    }

    /**
     * Activate a family (e.g. cms) or an explicit slug list in dependency order.
     */
    public function bulkActivate(Request $request): JsonResponse
    {
        $targets = $this->resolveActivationTargets($request);
        if ($targets === []) {
            return $this->success(['activated' => []], 'Nothing to activate');
        }

        $graph = app(ExtensionGraphService::class);
        $plan = $graph->activationPlan($targets);
        if ($plan['can_cascade'] !== true) {
            return $this->error($graph->planFailureMessage($plan));
        }

        try {
            $activated = $this->activatePlanWithRollback($plan['will_activate']);

            return $this->success(['activated' => $activated], 'Extensions activated successfully');
        } catch (Exception $e) {
            $this->writeExtensionLog($targets[0], 'activate', 'failed', null, null, $e->getMessage(), [
                'targets' => $targets,
            ]);

            return $this->error('Failed to activate extension: '.$e->getMessage());
        }
    }

    /**
     * Update configuration settings for an extension.
     */
    public function updateSettings(Request $request, string $slug): JsonResponse
    {
        $extension = Extension::where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        $extension->update(['settings' => $validated['settings']]);

        return $this->success($extension, 'Extension settings updated successfully');
    }

    /**
     * Uninstall and physically delete an extension.
     */
    public function uninstall(string $slug, Request $request): JsonResponse
    {
        $extension = Extension::where('slug', $slug)->firstOrFail();

        if ($guard = $this->guardKernelLifecycle($extension, 'uninstalled')) {
            return $guard;
        }

        if ($this->isShippedFirstPartyModule($extension)) {
            return $this->error(
                'First-party modules cannot be uninstalled. Deactivate them instead. Uninstall is reserved for uploaded plugins.',
                422
            );
        }

        if ($extension->status === 'active') {
            $deactivated = $this->deactivate($extension->slug);
            if ($deactivated->getStatusCode() >= 400) {
                return $deactivated;
            }
            $extension->refresh();
        }

        try {
            // 1. Trigger onUninstall lifecycle event/hook
            \Hook::action('extension_uninstalled', $extension);

            // 1b. Run dynamic database rollback migrations (if not keeping data)
            $keepData = $request->boolean('keep_data');
            if (! $keepData) {
                $migrationPath = $extension->type === 'module'
                    ? base_path('Modules/'.str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $extension->slug))).'/database/migrations')
                    : ExtensionPaths::pluginMigrationsDirectory($extension->slug);

                if (is_dir($migrationPath)) {
                    Artisan::call('migrate:rollback', [
                        '--path' => str_replace(base_path().'/', '', $migrationPath),
                        '--force' => true,
                    ]);
                }
            }

            // 2. Delete physical folder files
            $folderPath = $extension->type === 'module'
                ? base_path('Modules/'.str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $extension->slug))))
                : ExtensionPaths::pluginDirectory($extension->slug);

            if (is_dir($folderPath)) {
                File::deleteDirectory($folderPath);
            }

            // 3. Remove DB record
            $extension->forceDelete();

            ExtensionLog::create([
                'extension_slug' => $slug,
                'action' => 'uninstall',
                'version_before' => $extension->version,
                'version_after' => null,
                'status' => 'success',
                'performed_by' => auth()->id(),
            ]);

            return $this->success(null, 'Extension uninstalled and files deleted successfully');

        } catch (Exception $e) {
            ExtensionLog::create([
                'extension_slug' => $slug,
                'action' => 'uninstall',
                'version_before' => $extension->version,
                'version_after' => null,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'performed_by' => auth()->id(),
            ]);

            return $this->error('Failed to uninstall extension: '.$e->getMessage());
        }
    }

    /**
     * Upload an extension ZIP package, perform security scans, extract, and register.
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:zip|max:51200', // max 50MB
        ]);

        $uploadedFile = $request->file('file');
        $tempPath = $uploadedFile->getPathname();

        try {
            // 1. Run our Static Security AST Scanner
            (new ExtensionSecurityScanner)->scanZip($tempPath);

            // 2. Extract temporarily to inspect manifest.json
            $zip = new ZipArchive;
            if ($zip->open($tempPath) !== true) {
                return $this->error('Failed to open uploaded ZIP file.');
            }

            $manifestContent = null;

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                if (! is_string($filename) || $filename === '') {
                    continue;
                }
                if (basename($filename) === 'manifest.json') {
                    $rawContent = $zip->getFromIndex($i);
                    if (is_string($rawContent) && $rawContent !== '') {
                        $manifestContent = $rawContent;
                    }
                    break;
                }
            }

            if (! $manifestContent) {
                $zip->close();

                return $this->error('Security gate: Missing manifest.json in package.');
            }

            $manifestArr = $this->decodeJsonToArray($manifestContent);
            if ($manifestArr === null) {
                $zip->close();

                return $this->error('Security gate: Invalid manifest.json schema.');
            }

            $parsed = $this->parseValidatedInstallManifest($manifestArr);
            if ($parsed === null) {
                $zip->close();

                return $this->error('Security gate: Invalid manifest.json schema.');
            }

            $slug = $parsed['slug'];
            $type = $parsed['type'];

            // 3. Perform target directory extraction
            $targetDir = $type === 'module'
                ? base_path('Modules/'.str_replace(' ', '', ucwords(str_replace('-', ' ', $slug))))
                : ExtensionPaths::pluginDirectory($slug);

            if (is_dir($targetDir)) {
                $zip->close();

                return $this->error('An extension with this slug already exists.');
            }

            File::makeDirectory($targetDir, 0755, true, true);
            $zip->extractTo($targetDir);
            $zip->close();

            // 4. Register in database
            $extension = Extension::updateOrCreate(
                ['slug' => $slug],
                [
                    'type' => $type,
                    'name' => $parsed['name'],
                    'version' => $parsed['version'],
                    'database_version' => '0.0.0',
                    'status' => 'inactive',
                    'is_core' => false,
                    'author' => $parsed['author'],
                    'license' => $parsed['license'],
                    'requirements' => $parsed['requirements'],
                    'settings' => [],
                ]
            );

            ExtensionLog::create([
                'extension_slug' => $slug,
                'action' => 'install',
                'version_before' => null,
                'version_after' => $parsed['version'],
                'status' => 'success',
                'performed_by' => auth()->id(),
            ]);

            return $this->success($extension, 'Extension uploaded and installed successfully', 201);

        } catch (Exception $e) {
            return $this->error('Failed to upload/install package: '.$e->getMessage());
        }
    }

    /**
     * Automatically discover and synchronize newly added folders in Modules/ and extensions/
     */
    protected function discoverExtensions(): void
    {
        $discovered = [];

        // 1. Scan Modules
        $modulesPath = base_path('Modules');
        if (is_dir($modulesPath)) {
            $moduleDirs = File::directories($modulesPath);
            foreach ($moduleDirs as $dir) {
                $manifestFile = $dir.'/manifest.json';
                // Fallback for modular module.json if no manifest exists yet
                if (! File::exists($manifestFile)) {
                    $manifestFile = $dir.'/module.json';
                }

                if (File::exists($manifestFile)) {
                    $manifest = $this->decodeJsonToArray(File::get($manifestFile));
                    if ($manifest === null) {
                        continue;
                    }

                    $slugRaw = $manifest['slug'] ?? $manifest['alias'] ?? null;
                    if (! is_string($slugRaw) || $slugRaw === '') {
                        continue;
                    }

                    $extracted = $this->extractDiscoveryMeta($manifest, 'module', 'Core');
                    if ($extracted === null) {
                        continue;
                    }

                    $extracted['is_core'] = $extracted['is_core'] || $this->isKernelSlug($slugRaw);
                    $discovered[$slugRaw] = $extracted;
                }
            }
        }

        // 2. Scan plugins (backend/extensions/)
        foreach (ExtensionPaths::discoverPluginPackageDirectories() as $dir) {
            $manifestFile = $dir.'/manifest.json';
            if (File::exists($manifestFile)) {
                $manifest = $this->decodeJsonToArray(File::get($manifestFile));
                if ($manifest === null) {
                    continue;
                }

                if (! isset($manifest['slug']) || ! is_string($manifest['slug']) || $manifest['slug'] === '') {
                    continue;
                }

                $pluginSlug = $manifest['slug'];
                $extracted = $this->extractDiscoveryMeta($manifest, 'plugin', 'Anonymous');
                if ($extracted === null) {
                    continue;
                }

                $extracted['is_core'] = false;
                $discovered[$pluginSlug] = $extracted;
            }
        }

        // 3. Synchronize with Database
        foreach ($discovered as $slug => $meta) {
            $existing = Extension::where('slug', $slug)->first();
            // Kernel packages are always active — heal stale inactive rows from old discovery.
            $status = $meta['is_core']
                ? 'active'
                : ($existing !== null ? $existing->status : 'inactive');

            $author = $meta['author'] !== '' && $meta['author'] !== 'Core'
                ? $meta['author']
                : 'jejakawan';

            $license = $meta['license']
                ?? ($meta['is_core'] ? 'Platform' : ($existing?->license ?: 'Proprietary'));

            $requirements = $meta['dependencies_declared']
                ? $meta['requirements']
                : (is_array($existing?->requirements) ? $existing->requirements : []);

            $settings = is_array($existing?->settings) ? $existing->settings : [];
            if (is_string($meta['settings_route']) && $meta['settings_route'] !== '') {
                $settings['settings_route'] = $meta['settings_route'];
            }
            if (is_string($meta['license_tier']) && $meta['license_tier'] !== '') {
                $settings['license_tier'] = $meta['license_tier'];
            }
            if ($meta['suggests_declared']) {
                $settings['suggests'] = $meta['suggests'];
            }
            if ($meta['runtime_requires'] !== []) {
                $settings['runtime_requires'] = $meta['runtime_requires'];
            }
            if ($meta['permissions'] !== []) {
                $settings['permissions'] = $meta['permissions'];
            }

            $extension = Extension::updateOrCreate(
                ['slug' => $slug],
                [
                    'type' => $meta['type'],
                    'family' => $meta['family'],
                    'parent_slug' => $meta['parent_slug'],
                    'name' => $meta['name'],
                    'version' => $meta['version'],
                    'database_version' => $existing !== null ? $existing->database_version : '1.0.0',
                    'status' => $status,
                    'is_core' => $meta['is_core'],
                    'author' => $author,
                    'description' => $meta['description'] ?? $existing?->description,
                    'license' => $license,
                    'requirements' => $requirements,
                    'manifest' => [
                        'settings_route' => $meta['settings_route'],
                        'license_tier' => $meta['license_tier'],
                        'suggests' => $meta['suggests_declared']
                            ? $meta['suggests']
                            : (is_array($existing?->manifest) ? ($existing->manifest['suggests'] ?? []) : []),
                        'requires' => $meta['runtime_requires'] !== []
                            ? $meta['runtime_requires']
                            : (is_array($existing?->manifest) ? ($existing->manifest['requires'] ?? []) : []),
                        'permissions' => $meta['permissions'] !== []
                            ? $meta['permissions']
                            : (is_array($existing?->manifest) ? ($existing->manifest['permissions'] ?? []) : []),
                    ],
                    'settings' => $settings,
                ]
            );

            // Synchronize sub-features
            if (! empty($meta['features']) && is_array($meta['features'])) {
                foreach ($meta['features'] as $feat) {
                    if (! is_array($feat)) {
                        continue;
                    }
                    $this->upsertManifestFeature($slug, $feat, null);
                }
            }
        }
    }

    /**
     * Normalize disk manifest / module.json into discovery meta.
     *
     * @param  array<mixed, mixed>  $manifest
     * @return array{
     *     type: string,
     *     name: string,
     *     version: string,
     *     author: string,
     *     description: string|null,
     *     license: string|null,
     *     license_tier: string|null,
     *     settings_route: string|null,
     *     is_core: bool,
     *     family: string,
     *     parent_slug: string|null,
     *     features: array<int, mixed>,
     *     requirements: array<string, string>,
     *     dependencies_declared: bool,
     *     suggests: array<string, string>,
     *     suggests_declared: bool,
     *     runtime_requires: array<string, string>,
     *     permissions: list<string>
     * }|null
     */
    private function extractDiscoveryMeta(array $manifest, string $defaultType, string $defaultAuthor): ?array
    {
        $slugRaw = $manifest['slug'] ?? $manifest['alias'] ?? null;
        if (! is_string($slugRaw) || $slugRaw === '') {
            return null;
        }

        $name = isset($manifest['name']) && is_string($manifest['name'])
            ? $manifest['name']
            : $slugRaw;
        $version = isset($manifest['version']) && is_string($manifest['version'])
            ? $manifest['version']
            : '1.0.0';
        $author = isset($manifest['author']) && is_string($manifest['author'])
            ? $manifest['author']
            : $defaultAuthor;

        $type = $defaultType;
        if (isset($manifest['type']) && is_string($manifest['type'])
            && in_array($manifest['type'], ['module', 'plugin'], true)) {
            $type = $manifest['type'];
        }

        $manifestMarksCore = array_key_exists('is_core', $manifest)
            && filter_var($manifest['is_core'], FILTER_VALIDATE_BOOLEAN);

        $familyRaw = $manifest['family'] ?? null;
        $family = ExtensionFamilyCatalog::resolve(
            is_string($familyRaw) ? $familyRaw : null,
            $slugRaw,
            $type,
            $manifestMarksCore,
        );
        $parentSlug = isset($manifest['parent_slug']) && is_string($manifest['parent_slug'])
            ? $manifest['parent_slug']
            : null;

        $description = isset($manifest['description']) && is_string($manifest['description'])
            ? $manifest['description']
            : null;
        $license = isset($manifest['license']) && is_string($manifest['license'])
            ? $manifest['license']
            : null;
        $licenseTier = isset($manifest['license_tier']) && is_string($manifest['license_tier'])
            ? $manifest['license_tier']
            : null;
        $settingsRoute = isset($manifest['settings_route']) && is_string($manifest['settings_route'])
            ? $manifest['settings_route']
            : null;

        $featuresRaw = $manifest['features'] ?? [];
        $features = is_array($featuresRaw) ? $featuresRaw : [];

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

        $requirements = [];
        $dependenciesDeclared = array_key_exists('dependencies', $manifest);
        $dependencies = $manifest['dependencies'] ?? null;
        if (is_array($dependencies)) {
            foreach ($dependencies as $depSlug => $constraint) {
                if (! is_string($depSlug) || ! is_scalar($constraint)) {
                    continue;
                }
                $requirements[$depSlug] = (string) $constraint;
            }
        }

        $suggests = [];
        $suggestsDeclared = array_key_exists('suggests', $manifest);
        $suggestsRaw = $manifest['suggests'] ?? null;
        if (is_array($suggestsRaw)) {
            foreach ($suggestsRaw as $sugSlug => $constraint) {
                if (! is_string($sugSlug) || ! is_scalar($constraint)) {
                    continue;
                }
                $suggests[$sugSlug] = (string) $constraint;
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
            $permissions = array_values(array_unique($permissions));
        }

        return [
            'type' => $type,
            'name' => $name,
            'version' => $version,
            'author' => $author,
            'description' => $description,
            'license' => $license,
            'license_tier' => $licenseTier,
            'settings_route' => $settingsRoute,
            'is_core' => $manifestMarksCore,
            'family' => $family,
            'parent_slug' => $parentSlug,
            'features' => $features,
            'requirements' => $requirements,
            'dependencies_declared' => $dependenciesDeclared,
            'suggests' => $suggests,
            'suggests_declared' => $suggestsDeclared,
            'runtime_requires' => $runtimeRequires,
            'permissions' => $permissions,
        ];
    }

    /**
     * Toggle status of a specific sub-feature (Activate/Deactivate).
     */
    public function toggleFeature(Request $request, string $slug): JsonResponse
    {
        $feature = Feature::where('slug', $slug)->firstOrFail();
        $extension = $feature->extension;

        if ($extension && ($extension->is_core || $this->isKernelSlug($extension->slug))) {
            return $this->error('Sub-features of the platform kernel cannot be toggled');
        }

        $validated = $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $feature->update([
            'is_active' => $validated['is_active'],
        ]);

        return $this->success($feature, 'Sub-feature status updated successfully');
    }

    /**
     * Clone a plugin or module from a Git repository, scan it, and register it.
     */
    public function gitClone(Request $request): JsonResponse
    {
        $validatedInput = $request->validate([
            'repo_url' => 'required|string',
        ]);

        $repoUrl = $validatedInput['repo_url'];
        if (! is_string($repoUrl) || $repoUrl === '') {
            return $this->error('Security gate: Invalid Git repository URL format.');
        }

        // Simple validation of git URL
        if (! preg_match('/^(https?:\/\/|git@|ssh:\/\/)/', $repoUrl)) {
            return $this->error('Security gate: Invalid Git repository URL format.');
        }

        // Create a temporary directory inside storage/framework
        $tempDirName = 'git-clone-'.uniqid();
        $tempPath = base_path('storage/framework/'.$tempDirName);

        try {
            // 1. Run git clone into temporary path
            $escapedRepo = escapeshellarg($repoUrl);
            $escapedPath = escapeshellarg($tempPath);

            $output = [];
            $resultCode = 0;
            exec("git clone --depth 1 {$escapedRepo} {$escapedPath} 2>&1", $output, $resultCode);

            if ($resultCode !== 0) {
                if (is_dir($tempPath)) {
                    File::deleteDirectory($tempPath);
                }
                $errorStr = implode("\n", $output);

                return $this->error('Failed to clone Git repository: '.$errorStr);
            }

            // 2. Perform Static Security Scan on cloned PHP files using AST parser
            (new ExtensionSecurityScanner)->scanDirectory($tempPath);

            // 3. Verify manifest.json exists
            $manifestFile = $tempPath.'/manifest.json';
            if (! File::exists($manifestFile)) {
                File::deleteDirectory($tempPath);

                return $this->error('Security gate: Cloned repository is missing manifest.json.');
            }

            $manifestArr = $this->decodeJsonToArray(File::get($manifestFile));
            if ($manifestArr === null) {
                File::deleteDirectory($tempPath);

                return $this->error('Security gate: Invalid manifest.json schema in Git repository.');
            }

            $parsed = $this->parseValidatedInstallManifest($manifestArr);
            if ($parsed === null) {
                File::deleteDirectory($tempPath);

                return $this->error('Security gate: Invalid manifest.json schema in Git repository.');
            }

            $slug = $parsed['slug'];
            $type = $parsed['type'];

            // 4. Move cloned directory to its final location
            $targetDir = $type === 'module'
                ? base_path('Modules/'.str_replace(' ', '', ucwords(str_replace('-', ' ', $slug))))
                : ExtensionPaths::pluginDirectory($slug);

            if (is_dir($targetDir)) {
                File::deleteDirectory($tempPath);

                return $this->error('An extension with this slug already exists.');
            }

            // Move the cloned repository
            File::moveDirectory($tempPath, $targetDir);

            // 5. Register in database
            $extension = Extension::updateOrCreate(
                ['slug' => $slug],
                [
                    'type' => $type,
                    'name' => $parsed['name'],
                    'version' => $parsed['version'],
                    'database_version' => '0.0.0',
                    'status' => 'inactive',
                    'is_core' => false,
                    'author' => $parsed['author'],
                    'license' => $parsed['license'],
                    'requirements' => $parsed['requirements'],
                    'settings' => [],
                ]
            );

            // Dynamically register sub-features from manifest if present
            $featuresRaw = $manifestArr['features'] ?? null;
            if (is_array($featuresRaw)) {
                foreach ($featuresRaw as $feat) {
                    if (! is_array($feat)) {
                        continue;
                    }
                    $this->upsertManifestFeature($slug, $feat, true);
                }
            }

            ExtensionLog::create([
                'extension_slug' => $slug,
                'action' => 'install',
                'version_before' => null,
                'version_after' => $parsed['version'],
                'status' => 'success',
                'performed_by' => auth()->id(),
            ]);

            return $this->success($extension, 'Extension cloned and installed successfully from Git repository!', 201);

        } catch (Exception $e) {
            if (is_dir($tempPath)) {
                File::deleteDirectory($tempPath);
            }

            return $this->error('Failed to clone and install: '.$e->getMessage());
        }
    }

    /**
     * Get dynamic sidebar navigation items registered by active extensions/plugins via Hook filter.
     */
    public function navigation(): JsonResponse
    {
        $payload = Cache::remember('extensions:sidebar_navigation', 300, function () {
            return $this->buildNavigationPayload();
        });

        return $this->success($payload, 'Navigation retrieved successfully');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildNavigationPayload(): array
    {
        $items = [];
        $filtered = \Modules\Core\System\Facades\Hook::filter('sidebar_navigation', $items);
        /** @var array<int, array<string, mixed>> $items */
        $items = is_array($filtered) ? $filtered : [];

        // Fetch static manifest contributions for active plugins without booting their code
        try {
            $activeExtensions = Extension::where('status', 'active')->get();
            foreach ($activeExtensions as $ext) {
                if ($ext->type !== 'plugin') {
                    continue;
                }

                $manifestPath = ExtensionPaths::pluginManifestPath($ext->slug);
                if (file_exists($manifestPath)) {
                    $content = @file_get_contents($manifestPath);
                    if (is_string($content) && $content !== '') {
                        $manifest = $this->decodeJsonToArray($content);
                        if ($manifest !== null) {
                            $menuItems = null;

                            $contributionPoints = $manifest['contribution_points'] ?? null;
                            if (is_array($contributionPoints)) {
                                $sidebarRaw = $contributionPoints['sidebar_menu'] ?? null;
                                $menuItems = is_array($sidebarRaw) ? $sidebarRaw : null;
                            }

                            if ($menuItems === null) {
                                $contributes = $manifest['contributes'] ?? null;
                                if (is_array($contributes)) {
                                    $sidebarRaw = $contributes['sidebar_menu'] ?? null;
                                    $menuItems = is_array($sidebarRaw) ? $sidebarRaw : null;
                                }
                            }

                            if (is_array($menuItems)) {
                                foreach ($menuItems as $menuItem) {
                                    if (! is_array($menuItem)) {
                                        continue;
                                    }
                                    $nameVal = $menuItem['name'] ?? $menuItem['id'] ?? null;
                                    $labelVal = $menuItem['label'] ?? $menuItem['title'] ?? null;
                                    $iconVal = $menuItem['icon'] ?? null;
                                    $groupVal = $menuItem['group'] ?? null;
                                    $toVal = $menuItem['to'] ?? $menuItem['route'] ?? null;

                                    $items[] = [
                                        'name' => is_scalar($nameVal) ? (string) $nameVal : $ext->slug,
                                        'label' => is_scalar($labelVal) ? (string) $labelVal : $ext->name,
                                        'icon' => is_scalar($iconVal) ? (string) $iconVal : 'settings',
                                        'group' => is_scalar($groupVal) ? (string) $groupVal : 'operations',
                                        'to' => is_scalar($toVal) ? (string) $toVal : '/'.Setting::resolveConsoleDashboardSlug()."/{$ext->slug}",
                                    ];
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Fail-safe to guarantee stable platform booting
        }

        return $items;
    }

    /**
     * @return list<string>
     */
    protected function resolveActivationTargets(Request $request): array
    {
        $family = $request->input('family', $request->query('family'));
        $slugs = $request->input('slugs', $request->query('slugs'));

        $extraSlugs = [];
        if (is_array($slugs)) {
            foreach ($slugs as $slug) {
                if (is_string($slug) && $slug !== '') {
                    $extraSlugs[] = $slug;
                }
            }
        }

        if (is_string($family) && $family !== '') {
            $catalog = ExtensionFamilyCatalog::slugsInFamily($family);
            $fromDb = Extension::query()->where('family', $family)->pluck('slug')->all();
            $merged = array_values(array_unique(array_merge($catalog, $fromDb, $extraSlugs)));
            if ($merged === []) {
                return [];
            }

            return Extension::query()
                ->whereIn('slug', $merged)
                ->where('status', '!=', 'active')
                ->where('is_core', false)
                ->pluck('slug')
                ->all();
        }

        if (! is_array($slugs)) {
            return [];
        }

        $clean = [];
        foreach ($slugs as $slug) {
            if (is_string($slug) && $slug !== '') {
                $clean[] = $slug;
            }
        }

        return array_values(array_unique($clean));
    }

    /**
     * @param  list<array{slug: string, name: string, reason?: string}>  $willActivate
     * @return list<array{slug: string, name: string}>
     *
     * @throws Exception
     */
    protected function activatePlanWithRollback(array $willActivate): array
    {
        $activated = [];
        $graph = app(ExtensionGraphService::class);

        try {
            foreach ($willActivate as $row) {
                $step = Extension::where('slug', $row['slug'])->firstOrFail();
                if ($step->status === 'active') {
                    continue;
                }
                $licenseBlock = app(ExtensionHealthService::class)->licenseBlocker($step);
                if ($licenseBlock !== null) {
                    throw new Exception($licenseBlock);
                }
                $runtime = $graph->runtimeBlockers($step);
                if ($runtime !== []) {
                    throw new Exception($graph->planFailureMessage([
                        'runtime_conflicts' => $runtime,
                        'missing' => [],
                        'version_conflicts' => [],
                        'cycle' => [],
                    ]));
                }
                $this->performActivation($step, array_column($willActivate, 'slug'));
                $activated[] = [
                    'slug' => $step->slug,
                    'name' => $step->name,
                ];
            }

            return $activated;
        } catch (Exception $e) {
            foreach (array_reverse($activated) as $row) {
                $model = Extension::where('slug', $row['slug'])->first();
                if ($model !== null && $model->status === 'active') {
                    $this->rollbackActivation($model, $e->getMessage());
                }
            }
            throw $e;
        }
    }

    protected function rollbackActivation(Extension $extension, string $reason): void
    {
        \Hook::action('extension_deactivated', $extension);
        $extension->update(['status' => 'inactive']);
        ConsoleMenu::syncVisibilityForExtension($extension->slug, false);
        app(ExtensionGraphService::class)->forgetLifecycleCaches();
        $this->writeExtensionLog(
            $extension->slug,
            'activate_rollback',
            'success',
            $extension->version,
            $extension->version,
            $reason,
            ['rolled_back' => true],
        );
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    protected function writeExtensionLog(
        string $slug,
        string $action,
        string $status,
        ?string $versionBefore,
        ?string $versionAfter,
        ?string $error = null,
        array $meta = [],
    ): void {
        ExtensionLog::create([
            'extension_slug' => $slug,
            'action' => $action,
            'version_before' => $versionBefore,
            'version_after' => $versionAfter,
            'status' => $status,
            'error_message' => $error,
            'performed_by' => auth()->id(),
            'ip_address' => IpHelper::getClientIp(request()),
            'meta' => $meta === [] ? null : $meta,
        ]);
    }

    protected function performActivation(Extension $extension, array $cascadeSlugs = []): Extension
    {
        $versionBefore = $extension->version;
        $settings = is_array($extension->settings) ? $extension->settings : [];
        if (app()->runningUnitTests() && ($settings['__test_fail_activate'] ?? false) === true) {
            throw new Exception('simulated activation failure');
        }

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
        app(ExtensionContributionService::class)->seedPermissions($extension);

        $extension->update([
            'status' => 'active',
            'database_version' => $extension->version,
        ]);

        $this->writeExtensionLog(
            $extension->slug,
            'activate',
            'success',
            $versionBefore,
            $extension->version,
            null,
            $cascadeSlugs === [] ? [] : ['cascade' => $cascadeSlugs],
        );

        app(ExtensionGraphService::class)->forgetLifecycleCaches();
        ConsoleMenu::ensureMissingDefaults();
        ConsoleMenu::syncVisibilityForExtension($extension->slug, true);

        return $extension->fresh() ?? $extension;
    }

    /**
     * Verify all cross-dependencies for the given extension before activation.
     *
     * @throws Exception
     */
    protected function verifyDependencies(Extension $extension): void
    {
        $requirements = $extension->requirements;
        if (empty($requirements) || ! is_array($requirements)) {
            return;
        }

        foreach ($requirements as $reqSlug => $constraint) {
            if (! is_string($reqSlug) || $reqSlug === '') {
                continue;
            }
            $reqSlugStr = $reqSlug;
            if (! is_scalar($constraint)) {
                continue;
            }
            $constraintStr = (string) $constraint;
            $requiredExt = Extension::where('slug', $reqSlugStr)->first();

            if (! $requiredExt) {
                throw new Exception("Dependensi tidak terpenuhi: Ekstensi '{$reqSlugStr}' tidak terpasang di sistem.");
            }

            if ($requiredExt->status !== 'active') {
                throw new Exception("Dependensi tidak terpenuhi: Ekstensi '{$reqSlugStr}' ('{$requiredExt->name}') terpasang tetapi belum diaktifkan.");
            }

            $currentVersion = $requiredExt->version;
            if (! $this->checkVersionConstraint($currentVersion, $constraintStr)) {
                throw new Exception("Konflik versi dependensi: Ekstensi '{$reqSlugStr}' membutuhkan versi '{$constraintStr}', tetapi versi yang aktif saat ini adalah '{$currentVersion}'.");
            }
        }
    }

    /**
     * Helper to verify a version matches standard semver constraints.
     */
    protected function checkVersionConstraint(string $version, string $constraint): bool
    {
        $constraint = trim($constraint);
        if (empty($constraint) || $constraint === '*') {
            return true;
        }

        // Handle basic operators: >=, <=, >, <, =
        if (preg_match('/^([>=<]+)?\s*([0-9a-zA-Z\.\-]+)$/', $constraint, $matches)) {
            $operator = ! empty($matches[1]) ? $matches[1] : '=';
            $reqVersion = $matches[2];

            return version_compare($version, $reqVersion, $operator);
        }

        // Handle caret operator: ^1.2.3 -> >=1.2.3 and <2.0.0
        if (str_starts_with($constraint, '^')) {
            $reqVersion = substr($constraint, 1);
            if (version_compare($version, $reqVersion, '<')) {
                return false;
            }
            $parts = explode('.', $reqVersion);
            $nextMajor = ((int) $parts[0]) + 1;

            return version_compare($version, (string) $nextMajor, '<');
        }

        // Handle tilde operator: ~1.2.3 -> >=1.2.3 and <1.3.0
        if (str_starts_with($constraint, '~')) {
            $reqVersion = substr($constraint, 1);
            if (version_compare($version, $reqVersion, '<')) {
                return false;
            }
            $parts = explode('.', $reqVersion);
            if (count($parts) >= 2) {
                $nextMinor = ((int) $parts[1]) + 1;

                return version_compare($version, $parts[0].'.'.$nextMinor.'.0', '<');
            }

            return true;
        }

        return version_compare($version, $constraint, '>=');
    }

    /**
     * @return array<mixed, mixed>|null
     */
    private function decodeJsonToArray(string $json): ?array
    {
        /** @var mixed $decoded */
        $decoded = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            return null;
        }

        return $decoded;
    }

    /**
     * @param  array<mixed, mixed>  $manifest
     * @return array{slug: string, type: string, name: string, version: string, author: string, license: string, requirements: array<string, string>}|null
     */
    private function parseValidatedInstallManifest(array $manifest): ?array
    {
        if (! isset($manifest['slug'], $manifest['type'], $manifest['name'], $manifest['version'])) {
            return null;
        }

        if (! is_string($manifest['slug']) || ! is_string($manifest['type']) || ! is_string($manifest['name']) || ! is_string($manifest['version'])) {
            return null;
        }

        $slug = $manifest['slug'];
        $type = $manifest['type'];
        $name = $manifest['name'];
        $version = $manifest['version'];

        if ($type !== 'module' && $type !== 'plugin') {
            return null;
        }

        $author = isset($manifest['author']) && is_string($manifest['author']) ? $manifest['author'] : 'Anonymous';
        $license = isset($manifest['license']) && is_string($manifest['license']) ? $manifest['license'] : 'MIT';

        $requirements = [];
        $dependencies = $manifest['dependencies'] ?? null;
        if (is_array($dependencies)) {
            foreach ($dependencies as $depSlug => $constraint) {
                if (! is_string($depSlug)) {
                    continue;
                }
                if (! is_scalar($constraint)) {
                    continue;
                }

                $requirements[$depSlug] = (string) $constraint;
            }
        }

        return [
            'slug' => $slug,
            'type' => $type,
            'name' => $name,
            'version' => $version,
            'author' => $author,
            'license' => $license,
            'requirements' => $requirements,
        ];
    }

    /**
     * @param  array<mixed, mixed>  $feat
     */
    private function upsertManifestFeature(string $extensionSlug, array $feat, ?bool $forceIsActive): void
    {
        $featSlug = $feat['slug'] ?? null;
        $featName = $feat['name'] ?? null;
        if (! is_string($featSlug) || ! is_string($featName)) {
            return;
        }

        $description = isset($feat['description']) && is_string($feat['description']) ? $feat['description'] : null;
        $category = isset($feat['category']) && is_string($feat['category']) ? $feat['category'] : 'business';

        $isActive = $forceIsActive ?? (Feature::where('slug', $featSlug)->value('is_active') ?? true);

        Feature::updateOrCreate(
            ['slug' => $featSlug],
            [
                'extension_slug' => $extensionSlug,
                'name' => $featName,
                'description' => $description,
                'category' => $category,
                'is_active' => (bool) $isActive,
            ]
        );
    }
}
