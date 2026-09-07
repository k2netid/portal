<?php

declare(strict_types=1);

namespace Modules\Core\System\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\System\Models\Permission;
use Modules\Core\System\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * CMS editorial roles expected by pack PermissionSeeders (admin, editor, author).
 * Idempotent — safe on fresh seed, install profile apply, and extension activate heal.
 */
class CmsRolesSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $editor = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $author = Role::firstOrCreate(['name' => 'author', 'guard_name' => 'web']);
        $operator = Role::firstOrCreate(['name' => 'operator', 'guard_name' => 'web']);

        $adminPermissions = [
            'view profile', 'edit profile',
            'view settings', 'manage settings',
            'manage module access',
            'view users', 'create users', 'edit users', 'delete users', 'verify users', 'manage users',
            'view roles',
            'view media', 'upload media', 'edit media', 'delete media', 'manage media',
            'view files', 'upload files', 'edit files', 'delete files', 'manage files',
            'view analytics',
            'view redirects', 'manage redirects',
            'use mail',
            'manage personal mail account',
            'manage multi mail accounts',
            'view content', 'create content', 'edit content', 'delete content', 'approve content', 'publish content', 'manage content',
            'view categories', 'manage categories', 'manage tags',
            'view comments', 'manage comments',
            'view seo', 'manage seo',
            'view content templates', 'manage content templates', 'edit content templates', 'delete content templates',
            'view menus', 'create menus', 'edit menus', 'delete menus', 'manage menus',
            'view widgets', 'create widgets', 'edit widgets', 'delete widgets', 'manage widgets',
            'view themes', 'upload themes', 'edit themes', 'delete themes', 'manage themes',
            'view forms', 'create forms', 'edit forms', 'delete forms', 'manage forms',
            'view members', 'manage members',
        ];

        $editorPermissions = [
            'view profile', 'edit profile',
            'view media', 'upload media', 'edit media',
            'view analytics',
            'use mail',
            'manage personal mail account',
            'view content', 'create content', 'edit content', 'delete content', 'approve content', 'publish content', 'manage content',
            'view categories', 'manage categories', 'manage tags',
            'view comments', 'manage comments',
            'view seo', 'manage seo',
            'view content templates', 'manage content templates', 'edit content templates',
            'view menus', 'edit menus',
            'view widgets', 'edit widgets',
            'view forms',
        ];

        $authorPermissions = [
            'view profile', 'edit profile',
            'view media', 'upload media', 'edit media',
            'view content', 'create content', 'edit content',
            'view categories',
            'view comments',
            'view seo',
        ];

        $operatorPermissions = [
            'view profile', 'edit profile',
            'view media', 'upload media',
            'view files', 'upload files', 'manage files',
            'view analytics',
            'view system',
            'view logs',
            'view scheduled tasks',
            'use mail',
            'manage personal mail account',
        ];

        $admin->givePermissionTo(Permission::whereIn('name', $adminPermissions)->get());
        $editor->givePermissionTo(Permission::whereIn('name', $editorPermissions)->get());
        $author->givePermissionTo(Permission::whereIn('name', $authorPermissions)->get());
        $operator->givePermissionTo(Permission::whereIn('name', $operatorPermissions)->get());

        if ($this->command) {
            $this->command->info('CMS roles seeded (admin, editor, author, operator).');
        }
    }
}
