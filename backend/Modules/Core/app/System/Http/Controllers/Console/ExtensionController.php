<?php

declare(strict_types=1);

namespace Modules\Core\System\Http\Controllers\Console;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Models\Extension;
use Modules\Core\System\Models\ExtensionLog;
use Modules\Core\System\Models\Feature;
use Modules\Core\System\Services\ExtensionSecurityScanner;
use Modules\Core\System\Support\ExtensionPaths;
use ZipArchive;

class ExtensionController extends BaseApiController
{
    /**
     * List all extensions (combining database statuses and physical folder discovery).
     */
    public function index(): JsonResponse
    {
        $this->discoverExtensions();

        $extensions = Extension::with('features')->latest()->get();

        return $this->success($extensions, 'Extensions retrieved successfully');
    }

    /**
     * Activate a module/plugin.
     */
    public function activate(string $slug): JsonResponse
    {
        $extension = Extension::where('slug', $slug)->firstOrFail();

        if ($extension->status === 'active') {
            return $this->error('Extension is already active');
        }

        $versionBefore = $extension->version;

        try {
            // 0. Verify cross-dependencies before activation
            $this->verifyDependencies($extension);

            // 1. Run dynamic migrations if any exist in the package folder
            $migrationPath = $extension->type === 'module'
                ? base_path('Modules/'.str_replace(' ', '', ucwords(str_replace('-', ' ', $extension->slug))).'/database/migrations')
                : ExtensionPaths::pluginMigrationsDirectory($extension->slug);

            if (is_dir($migrationPath)) {
                Artisan::call('migrate', [
                    '--path' => str_replace(base_path().'/', '', $migrationPath),
                    '--force' => true,
                ]);
            }

            // 2. Trigger onActivate lifecycle event/hook
            \Hook::action('extension_activated', $extension);

            // 3. Update status
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
                'performed_by' => auth()->id(),
            ]);

            return $this->success($extension, 'Extension activated successfully');

        } catch (Exception $e) {
            ExtensionLog::create([
                'extension_slug' => $extension->slug,
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

        if ($extension->is_core) {
            return $this->error('Core modules cannot be deactivated');
        }

        if ($extension->status !== 'active') {
            return $this->error('Extension is not active');
        }

        try {
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

        if ($extension->is_core) {
            return $this->error('Core modules cannot be uninstalled');
        }

        if ($extension->status === 'active') {
            $this->deactivate($extension->slug);
        }

        try {
            // 1. Trigger onUninstall lifecycle event/hook
            \Hook::action('extension_uninstalled', $extension);

            // 1b. Run dynamic database rollback migrations (if not keeping data)
            $keepData = $request->boolean('keep_data');
            if (! $keepData) {
                $migrationPath = $extension->type === 'module'
                    ? base_path('Modules/'.str_replace(' ', '', ucwords(str_replace('-', ' ', $extension->slug))).'/database/migrations')
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
                ? base_path('Modules/'.str_replace(' ', '', ucwords(str_replace('-', ' ', $extension->slug))))
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

                    $name = isset($manifest['name']) && is_string($manifest['name'])
                        ? $manifest['name']
                        : basename($dir);
                    $version = isset($manifest['version']) && is_string($manifest['version'])
                        ? $manifest['version']
                        : '1.0.0';
                    $author = isset($manifest['author']) && is_string($manifest['author'])
                        ? $manifest['author']
                        : 'Core';
                    $featuresRaw = $manifest['features'] ?? [];
                    $features = is_array($featuresRaw) ? $featuresRaw : [];

                    $discovered[$slugRaw] = [
                        'type' => 'module',
                        'name' => $name,
                        'version' => $version,
                        'author' => $author,
                        'is_core' => in_array($slugRaw, ['system', 'security', 'analytics', 'infra', 'ai', 'media', 'publishing'], true),
                        'features' => $features,
                    ];
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
                $name = isset($manifest['name']) && is_string($manifest['name'])
                    ? $manifest['name']
                    : basename($dir);
                $version = isset($manifest['version']) && is_string($manifest['version'])
                    ? $manifest['version']
                    : '1.0.0';
                $author = isset($manifest['author']) && is_string($manifest['author'])
                    ? $manifest['author']
                    : 'Anonymous';
                $featuresRaw = $manifest['features'] ?? [];
                $features = is_array($featuresRaw) ? $featuresRaw : [];

                $discovered[$pluginSlug] = [
                    'type' => 'plugin',
                    'name' => $name,
                    'version' => $version,
                    'author' => $author,
                    'is_core' => false,
                    'features' => $features,
                ];
            }
        }

        // 3. Synchronize with Database
        foreach ($discovered as $slug => $meta) {
            $extension = Extension::updateOrCreate(
                ['slug' => $slug],
                [
                    'type' => $meta['type'],
                    'name' => $meta['name'],
                    'version' => $meta['version'],
                    'database_version' => Extension::where('slug', $slug)->value('database_version') ?? '1.0.0',
                    'status' => Extension::where('slug', $slug)->value('status') ?? ($meta['is_core'] ? 'active' : 'inactive'),
                    'is_core' => $meta['is_core'],
                    'author' => 'jejakawan',
                    'license' => 'Proprietary',
                    'requirements' => [],
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
     * Toggle status of a specific sub-feature (Activate/Deactivate).
     */
    public function toggleFeature(Request $request, string $slug): JsonResponse
    {
        $feature = Feature::where('slug', $slug)->firstOrFail();
        $extension = $feature->extension;

        if ($extension && $extension->is_core && in_array($extension->slug, ['system', 'security', 'infra'])) {
            return $this->error('Sub-features of critical core modules cannot be toggled');
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
                                        'to' => is_scalar($toVal) ? (string) $toVal : "/" . \Modules\Core\System\Models\Setting::resolveConsoleDashboardSlug() . "/{$ext->slug}",
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
