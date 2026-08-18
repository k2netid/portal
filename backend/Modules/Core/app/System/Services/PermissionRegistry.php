<?php

declare(strict_types=1);

namespace Modules\Core\System\Services;

use Modules\Core\System\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Central catalog of module-declared permissions (SSOT for RBAC names).
 */
final class PermissionRegistry
{
    /** @var array<string, list<string>> */
    private array $byModule = [];

    /**
     * @param  list<string>  $permissions
     */
    public function register(string $module, array $permissions): void
    {
        $normalized = array_values(array_unique(array_map(
            static fn (string $p): string => trim($p),
            $permissions,
        )));
        $this->byModule[$module] = array_merge($this->byModule[$module] ?? [], $normalized);
        $this->byModule[$module] = array_values(array_unique($this->byModule[$module]));
    }

    /**
     * @return list<string>
     */
    public function all(): array
    {
        $flat = [];
        foreach ($this->byModule as $permissions) {
            foreach ($permissions as $permission) {
                $flat[] = $permission;
            }
        }

        return array_values(array_unique($flat));
    }

    /**
     * @return array<string, list<string>>
     */
    public function byModule(): array
    {
        return $this->byModule;
    }

    /**
     * Persist all registered permissions (idempotent).
     */
    public function syncToDatabase(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($this->all() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
    }
}
