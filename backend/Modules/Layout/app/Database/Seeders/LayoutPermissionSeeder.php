<?php

declare(strict_types=1);

namespace Modules\Layout\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\System\Models\Permission;
use Modules\Core\System\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class LayoutPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $perms = [
            'view menus',
            'create menus',
            'edit menus',
            'delete menus',
            'manage menus',
            'view widgets',
            'create widgets',
            'edit widgets',
            'delete widgets',
            'manage widgets',
            'view redirects',
            'create redirects',
            'edit redirects',
            'delete redirects',
            'manage redirects',
        ];

        foreach ($perms as $name) {
            Permission::findOrCreate($name, 'web');
        }

        foreach (['super', 'admin', 'editor'] as $roleName) {
            $role = Role::query()
                ->where('name', $roleName)
                ->where('guard_name', 'web')
                ->first();
            if ($role) {
                $role->givePermissionTo($perms);
            }
        }

        $author = Role::query()
            ->where('name', 'author')
            ->where('guard_name', 'web')
            ->first();
        if ($author) {
            $author->givePermissionTo([
                'view menus',
                'view widgets',
                'view redirects',
            ]);
        }
    }
}
