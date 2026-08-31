<?php

declare(strict_types=1);

namespace Modules\Library\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\System\Models\Permission;
use Modules\Core\System\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Taxonomy permissions for Library (also seeded by Publishing when both packs are active).
 */
class LibraryPermissionSeeder extends Seeder
{
    public static function ensure(): void
    {
        (new self)->run();
    }

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $perms = [
            'view categories',
            'manage categories',
            'manage tags',
            'manage content',
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
                'view categories',
                'manage content',
            ]);
        }
    }
}
