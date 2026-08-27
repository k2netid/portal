<?php

declare(strict_types=1);

namespace Modules\Core\System\Services;

use Modules\Core\System\Models\Extension;
use Modules\Core\System\Models\Permission;
use Modules\Core\System\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Manifest contribution points: permissions seeded on activate, never deleted on deactivate.
 */
class ExtensionContributionService
{
    /**
     * @return list<string>
     */
    public function declaredPermissions(Extension $extension): array
    {
        $raw = [];
        if (is_array($extension->manifest) && isset($extension->manifest['permissions']) && is_array($extension->manifest['permissions'])) {
            $raw = $extension->manifest['permissions'];
        } elseif (is_array($extension->settings) && isset($extension->settings['permissions']) && is_array($extension->settings['permissions'])) {
            $raw = $extension->settings['permissions'];
        }

        $out = [];
        foreach ($raw as $name) {
            if (is_string($name) && $name !== '') {
                $out[] = $name;
            }
        }

        return array_values(array_unique($out));
    }

    /**
     * @return list<string> permission names that were ensured
     */
    public function seedPermissions(Extension $extension): array
    {
        $names = $this->declaredPermissions($extension);
        if ($names === []) {
            return [];
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($names as $name) {
            Permission::findOrCreate($name, 'web');
        }

        foreach (['super', 'admin'] as $roleName) {
            $role = Role::query()
                ->where('name', $roleName)
                ->where('guard_name', 'web')
                ->first();
            if ($role) {
                $role->givePermissionTo($names);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $names;
    }

    /**
     * @return list<string>
     */
    public function missingPermissions(Extension $extension): array
    {
        $declared = $this->declaredPermissions($extension);
        if ($declared === []) {
            return [];
        }

        $present = Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('name', $declared)
            ->pluck('name')
            ->all();

        return array_values(array_diff($declared, $present));
    }

    /**
     * Widget types declared on the pack so the registry can advertise contribution points.
     *
     * @return list<array{type: string, name: string, areas?: list<string>}>
     */
    public function declaredWidgets(Extension $extension): array
    {
        $raw = [];
        if (is_array($extension->manifest) && isset($extension->manifest['contribution_points']['widgets']) && is_array($extension->manifest['contribution_points']['widgets'])) {
            $raw = $extension->manifest['contribution_points']['widgets'];
        }

        $out = [];
        foreach ($raw as $widget) {
            if (! is_array($widget) || ! isset($widget['type'], $widget['name'])) {
                continue;
            }
            if (! is_string($widget['type']) || ! is_string($widget['name'])) {
                continue;
            }
            $item = [
                'type' => $widget['type'],
                'name' => $widget['name'],
            ];
            if (isset($widget['areas']) && is_array($widget['areas'])) {
                $item['areas'] = array_values(array_filter($widget['areas'], 'is_string'));
            }
            $out[] = $item;
        }

        return $out;
    }
}
