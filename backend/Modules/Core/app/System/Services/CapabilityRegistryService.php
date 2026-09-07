<?php

declare(strict_types=1);

namespace Modules\Core\System\Services;

use Illuminate\Support\Facades\File;
use Modules\Core\System\Models\Extension;
use Modules\Core\System\Models\Permission;
use Modules\Core\System\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Enterprise Capability & Permission Registry (Single Source of Truth).
 * Discovers and synchronizes declared module/plugin capabilities, provides
 * hierarchical matrix tree for the UI, role reset-to-defaults, and OAuth2 scopes.
 */
class CapabilityRegistryService
{
    /** @var array<string, array<string, mixed>>|null */
    private ?array $cachedManifests = null;

    /**
     * Load all manifest files across in-tree modules and active extensions.
     *
     * @return array<string, array<string, mixed>> Keyed by module slug
     */
    public function getDiscoveredManifests(): array
    {
        if ($this->cachedManifests !== null) {
            return $this->cachedManifests;
        }

        $manifests = [];
        $modulesPath = base_path('Modules');

        // 1. Scan in-tree modules
        if (File::isDirectory($modulesPath)) {
            $directories = File::directories($modulesPath);
            foreach ($directories as $dir) {
                $manifestFile = $dir.'/manifest.json';
                if (File::exists($manifestFile)) {
                    $content = json_decode(File::get($manifestFile), true);
                    if (is_array($content) && ! empty($content['slug'])) {
                        $manifests[$content['slug']] = $content;
                    }
                }
            }
        }

        // 2. Also incorporate active extensions from database if they have manifest stored
        try {
            $activeExtensions = Extension::query()
                ->where('status', 'active')
                ->get();

            foreach ($activeExtensions as $extension) {
                if (is_array($extension->manifest) && ! empty($extension->slug)) {
                    if (! isset($manifests[$extension->slug])) {
                        $manifests[$extension->slug] = $extension->manifest;
                    }
                }
            }
        } catch (\Throwable $e) {
            // DB might be unavailable during initial migration
        }

        $this->cachedManifests = $manifests;

        return $manifests;
    }

    /**
     * Build hierarchical tree: Module -> Feature -> Actions/Capabilities.
     *
     * @return list<array{
     *     slug: string,
     *     name: string,
     *     description: string,
     *     is_core: bool,
     *     family: string,
     *     features: list<array{
     *         slug: string,
     *         name: string,
     *         description: string,
     *         actions: list<array{
     *             key: string,
     *             permission: string,
     *             label: string,
     *             action_type: string,
     *             default_roles: list<string>,
     *             oauth_scope: ?string,
     *             is_dangerous: bool
     *         }>
     *     }>
     * }>
     */
    public function getRegistryTree(): array
    {
        $manifests = $this->getDiscoveredManifests();
        $tree = [];

        // Order: Core first, then alphabetical
        uksort($manifests, function (string $a, string $b): int {
            if ($a === 'core') return -1;
            if ($b === 'core') return 1;
            return strcmp($a, $b);
        });

        foreach ($manifests as $slug => $manifest) {
            $moduleName = (string) ($manifest['name'] ?? ucfirst($slug));
            $description = (string) ($manifest['description'] ?? '');
            $isCore = (bool) ($manifest['is_core'] ?? false);
            $family = (string) ($manifest['family'] ?? 'module');

            $featuresList = [];

            if (! empty($manifest['capabilities']) && is_array($manifest['capabilities'])) {
                foreach ($manifest['capabilities'] as $featureSlug => $featureData) {
                    if (! is_array($featureData)) {
                        continue;
                    }

                    $actionsList = [];
                    if (! empty($featureData['actions']) && is_array($featureData['actions'])) {
                        foreach ($featureData['actions'] as $actionKey => $actionData) {
                            if (! is_array($actionData) || empty($actionData['permission'])) {
                                continue;
                            }

                            $actionsList[] = [
                                'key' => (string) $actionKey,
                                'permission' => (string) $actionData['permission'],
                                'label' => (string) ($actionData['label'] ?? ucfirst($actionKey)),
                                'action_type' => (string) ($actionData['action_type'] ?? 'manage'),
                                'default_roles' => is_array($actionData['default_roles'] ?? null)
                                    ? array_values(array_filter($actionData['default_roles'], 'is_string'))
                                    : ['admin'],
                                'oauth_scope' => isset($actionData['oauth_scope']) ? (string) $actionData['oauth_scope'] : null,
                                'is_dangerous' => (bool) ($actionData['is_dangerous'] ?? false),
                            ];
                        }
                    }

                    $featuresList[] = [
                        'slug' => (string) $featureSlug,
                        'name' => (string) ($featureData['name'] ?? ucfirst($featureSlug)),
                        'description' => (string) ($featureData['description'] ?? ''),
                        'actions' => $actionsList,
                    ];
                }
            } elseif (! empty($manifest['permissions']) && is_array($manifest['permissions'])) {
                // Fallback for modules that declare flat permissions without structured capabilities
                $actionsList = [];
                foreach ($manifest['permissions'] as $perm) {
                    if (! is_string($perm) || $perm === '') {
                        continue;
                    }
                    $actionsList[] = [
                        'key' => $perm,
                        'permission' => $perm,
                        'label' => ucwords($perm),
                        'action_type' => str_starts_with($perm, 'view') ? 'read' : 'manage',
                        'default_roles' => ['admin'],
                        'oauth_scope' => null,
                        'is_dangerous' => str_contains($perm, 'delete'),
                    ];
                }

                $featuresList[] = [
                    'slug' => 'general',
                    'name' => 'General Permissions',
                    'description' => '',
                    'actions' => $actionsList,
                ];
            }

            if (! empty($featuresList)) {
                $tree[] = [
                    'slug' => $slug,
                    'name' => $moduleName,
                    'description' => $description,
                    'is_core' => $isCore,
                    'family' => $family,
                    'features' => $featuresList,
                ];
            }
        }

        return $tree;
    }

    /**
     * Get flat list of all declared permissions across manifests.
     *
     * @return list<string>
     */
    public function getAllDeclaredPermissions(): array
    {
        $perms = [];
        foreach ($this->getRegistryTree() as $module) {
            foreach ($module['features'] as $feature) {
                foreach ($feature['actions'] as $action) {
                    $perms[] = $action['permission'];
                }
            }
        }

        return array_values(array_unique($perms));
    }

    /**
     * Synchronize all declared permissions into the database idempotently.
     *
     * @return array{created: list<string>, total: int}
     */
    public function syncToDatabase(): array
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $declared = $this->getAllDeclaredPermissions();
        $created = [];

        foreach ($declared as $permissionName) {
            $exists = Permission::query()
                ->where('name', $permissionName)
                ->where('guard_name', 'web')
                ->exists();

            if (! $exists) {
                Permission::create(['name' => $permissionName, 'guard_name' => 'web']);
                $created[] = $permissionName;
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return [
            'created' => $created,
            'total' => count($declared),
        ];
    }

    /**
     * Get the default permissions that should be assigned to a specific role.
     *
     * @return list<string>
     */
    public function getDefaultPermissionsForRole(string $roleName): array
    {
        $roleName = strtolower(trim($roleName));

        // 'super' role always has all permissions
        if ($roleName === 'super') {
            return $this->getAllDeclaredPermissions();
        }

        $perms = [];
        foreach ($this->getRegistryTree() as $module) {
            foreach ($module['features'] as $feature) {
                foreach ($feature['actions'] as $action) {
                    if (in_array($roleName, $action['default_roles'], true)) {
                        $perms[] = $action['permission'];
                    }
                }
            }
        }

        return array_values(array_unique($perms));
    }

    /**
     * Calculate difference between currently active permissions and manifest defaults for a role.
     *
     * @return array{
     *     current: list<string>,
     *     defaults: list<string>,
     *     to_add: list<string>,
     *     to_remove: list<string>,
     *     is_in_sync: bool
     * }
     */
    public function getRoleDiffWithDefaults(Role $role): array
    {
        $current = $role->permissions()->pluck('name')->all();
        $defaults = $this->getDefaultPermissionsForRole($role->name);

        $toAdd = array_values(array_diff($defaults, $current));
        $toRemove = array_values(array_diff($current, $defaults));

        return [
            'current' => $current,
            'defaults' => $defaults,
            'to_add' => $toAdd,
            'to_remove' => $toRemove,
            'is_in_sync' => ($toAdd === [] && $toRemove === []),
        ];
    }

    /**
     * Reset a role's permissions back to the manifest defaults.
     *
     * @return list<string> List of synced permissions
     */
    public function resetRoleToDefaults(Role $role): array
    {
        if ($role->name === 'super') {
            // Super has all permissions
            $defaults = $this->getAllDeclaredPermissions();
        } else {
            $defaults = $this->getDefaultPermissionsForRole($role->name);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Ensure all permissions exist before syncing
        foreach ($defaults as $permName) {
            Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
        }

        $role->syncPermissions($defaults);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $defaults;
    }

    /**
     * Reset all standard platform roles to their declared defaults.
     *
     * @return array<string, int> Count of permissions assigned per role
     */
    public function resetAllRolesToDefaults(): array
    {
        $standardRoles = [
            'admin',
            'editor',
            'author',
            'operator',
            'security-officer',
            'system-admin',
            'staff',
            'member',
        ];

        $summary = [];

        foreach ($standardRoles as $roleName) {
            $role = Role::query()->where('name', $roleName)->where('guard_name', 'web')->first();
            if ($role) {
                $synced = $this->resetRoleToDefaults($role);
                $summary[$roleName] = count($synced);
            }
        }

        return $summary;
    }

    /**
     * Extract OAuth2 / OIDC scopes map from capabilities for SSO identity server.
     *
     * @return array<string, array{description: string, permissions: list<string>}>
     */
    public function getAllOAuthScopes(): array
    {
        $scopes = [];

        foreach ($this->getRegistryTree() as $module) {
            foreach ($module['features'] as $feature) {
                foreach ($feature['actions'] as $action) {
                    if (! empty($action['oauth_scope'])) {
                        $scope = $action['oauth_scope'];
                        if (! isset($scopes[$scope])) {
                            $scopes[$scope] = [
                                'description' => $feature['name'].' - '.$action['label'],
                                'permissions' => [],
                            ];
                        }
                        $scopes[$scope]['permissions'][] = $action['permission'];
                    }
                }
            }
        }

        return $scopes;
    }
}
