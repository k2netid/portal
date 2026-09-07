<?php

declare(strict_types=1);

namespace Modules\Mail\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\System\Models\Permission;
use Modules\Core\System\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class MailPermissionSeeder extends Seeder
{
    public static function ensure(): void
    {
        (new self)->run();
    }

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $allPerms = [
            'use mail',
            'manage personal mail account',
            'manage multi mail accounts',
        ];

        foreach ($allPerms as $name) {
            Permission::findOrCreate($name, 'web');
        }

        foreach (['super', 'admin'] as $roleName) {
            $role = Role::query()
                ->where('name', $roleName)
                ->where('guard_name', 'web')
                ->first();
            if ($role) {
                $role->givePermissionTo($allPerms);
            }
        }

        foreach (['editor', 'operator'] as $roleName) {
            $role = Role::query()
                ->where('name', $roleName)
                ->where('guard_name', 'web')
                ->first();
            if ($role) {
                $role->givePermissionTo([
                    'use mail',
                    'manage personal mail account',
                ]);
            }
        }
    }
}
