<?php

declare(strict_types=1);

namespace Modules\Core\System\Http\Controllers\Console;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Modules\Core\Security\Models\DependencyVulnerability;
use Modules\Core\System\Http\Controllers\BaseApiController;

/**
 * Controller for listing all project dependencies (composer + npm)
 */
class DependencyPackageController extends BaseApiController
{
    /**
     * List all packages from composer.lock and package-lock.json
     */
    public function index(Request $request): JsonResponse
    {
        $packages = $this->getAllPackages();

        // Apply filters
        if ($request->filled('source')) {
            $source = is_string($request->source) ? $request->source : '';
            $packages = array_filter($packages, fn (array $p): bool => $p['source'] === $source);
        }

        if ($request->filled('status')) {
            $status = is_string($request->status) ? $request->status : '';
            $packages = array_filter($packages, fn (array $p): bool => $p['status'] === $status);
        }

        if ($request->filled('package')) {
            $search = is_string($request->package) ? strtolower($request->package) : '';
            $packages = array_filter($packages, fn (array $p): bool => str_contains(strtolower((string) $p['name']), $search));
        }

        // Re-index array after filtering
        $packages = array_values($packages);

        // Pagination
        $perPage = is_numeric($request->input('per_page', 50)) ? (int) $request->input('per_page', 50) : 50;
        $page = is_numeric($request->input('page', 1)) ? (int) $request->input('page', 1) : 1;
        $total = count($packages);
        $offset = ($page - 1) * $perPage;

        $paginatedPackages = array_slice($packages, $offset, $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Packages retrieved',
            'data' => [
                'data' => $paginatedPackages,
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => (int) ceil($total / $perPage),
                'from' => $total > 0 ? $offset + 1 : 0,
                'to' => min($offset + $perPage, $total),
            ],
        ]);
    }

    /**
     * Get statistics for all packages
     */
    public function statistics(): JsonResponse
    {
        $packages = $this->getAllPackages();

        $composerPackages = array_filter($packages, fn (array $p): bool => $p['source'] === 'composer');
        $npmPackages = array_filter($packages, fn (array $p): bool => $p['source'] === 'npm');
        $vulnerablePackages = array_filter($packages, fn (array $p): bool => $p['status'] === 'vulnerable');

        return $this->success([
            'total' => count($packages),
            'composer' => count($composerPackages),
            'npm' => count($npmPackages),
            'secure' => count($packages) - count($vulnerablePackages),
            'vulnerable' => count($vulnerablePackages),
        ], 'Package statistics retrieved');
    }

    private function resolveNpmLockPath(): ?string
    {
        foreach ([base_path('../frontend/package-lock.json'), base_path('../package-lock.json'), base_path('package-lock.json')] as $path) {
            if (File::exists($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Get all packages from lock files
     *
     * @return array<int, array{name: string, version: string, source: string, status: string, vulnerability_id: int|null}>
     */
    private function getAllPackages(): array
    {
        $packages = [];

        // Get vulnerable package names for status check (Key: ID, Value: Name)
        /** @var array<int, string> $vulnerablePackages */
        $vulnerablePackages = DependencyVulnerability::query()
            ->unresolved()
            ->pluck('package_name', 'id')
            ->toArray();
        // Create map: Name => ID
        $vulnerableMap = array_flip($vulnerablePackages);

        // Parse composer.lock
        $composerLockPath = base_path('composer.lock');
        if (File::exists($composerLockPath)) {
            $composerLock = json_decode(File::get($composerLockPath), true);

            if (is_array($composerLock)) {
                $processPackage = function ($package) use (&$packages, $vulnerableMap): void {
                    if (is_array($package) && isset($package['name'], $package['version'])) {
                        $name = is_string($package['name']) ? $package['name'] : (is_scalar($package['name']) ? (string) $package['name'] : '');
                        $version = is_string($package['version']) ? $package['version'] : (is_scalar($package['version']) ? (string) $package['version'] : '');
                        $packages[] = [
                            'name' => $name,
                            'version' => $version,
                            'source' => 'composer',
                            'status' => isset($vulnerableMap[$name]) ? 'vulnerable' : 'secure',
                            'vulnerability_id' => $vulnerableMap[$name] ?? null,
                        ];
                    }
                };

                // Regular dependencies
                if (isset($composerLock['packages']) && is_array($composerLock['packages'])) {
                    foreach ($composerLock['packages'] as $package) {
                        $processPackage($package);
                    }
                }

                // Dev dependencies
                if (isset($composerLock['packages-dev']) && is_array($composerLock['packages-dev'])) {
                    foreach ($composerLock['packages-dev'] as $package) {
                        $processPackage($package);
                    }
                }
            }
        }

        // Parse package-lock.json (frontend or repo root)
        $npmLockPath = $this->resolveNpmLockPath();
        if ($npmLockPath !== null && File::exists($npmLockPath)) {
            $npmLock = json_decode(File::get($npmLockPath), true);

            if (is_array($npmLock) && isset($npmLock['packages']) && is_array($npmLock['packages'])) {
                foreach ($npmLock['packages'] as $path => $package) {
                    // Skip root package and node_modules subdependencies
                    if ($path === '' || ! is_array($package) || ! isset($package['version'])) {
                        continue;
                    }

                    // Extract package name from path (e.g., "node_modules/vue" -> "vue")
                    $pathStr = is_string($path) ? $path : (string) $path;
                    $name = str_replace('node_modules/', '', $pathStr);

                    // Skip nested dependencies (those with multiple slashes after node_modules)
                    if (substr_count($name, '/') > 1 || str_contains($name, 'node_modules')) {
                        continue;
                    }

                    $packages[] = [
                        'name' => $name,
                        'version' => is_string($package['version']) ? $package['version'] : (is_scalar($package['version']) ? (string) $package['version'] : ''),
                        'source' => 'npm',
                        'status' => isset($vulnerableMap[$name]) ? 'vulnerable' : 'secure',
                        'vulnerability_id' => $vulnerableMap[$name] ?? null,
                    ];
                }
            }
        }

        // Sort by name
        usort($packages, fn (array $a, array $b): int => strcasecmp((string) $a['name'], (string) $b['name']));

        return $packages;
    }
}
