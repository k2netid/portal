<?php

declare(strict_types=1);

namespace Modules\Media\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\System\Models\Permission;
use Modules\Core\System\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class MediaPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $perms = [
            'view media',
            'upload media',
            'edit media',
            'delete media',
            'manage media',
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
                'view media',
                'upload media',
                'edit media',
            ]);
        }
    }
}
