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

        $perms = [
            'use mail',
        ];

        foreach ($perms as $name) {
            Permission::findOrCreate($name, 'web');
        }

        foreach (['super', 'admin', 'editor', 'operator'] as $roleName) {
            $role = Role::query()
                ->where('name', $roleName)
                ->where('guard_name', 'web')
                ->first();
            if ($role) {
                $role->givePermissionTo($perms);
            }
        }
    }
}
