<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sys_extensions')) {
            return;
        }

        $exists = DB::table('sys_extensions')->where('slug', 'visual-builder')->exists();
        if (! $exists) {
            $manifest = [
                'slug' => 'visual-builder',
                'type' => 'plugin',
                'name' => 'Visual Page & Site Builder',
                'version' => '1.0.0',
                'description' => 'Visual drag-and-drop page editor and site layout composer with multi-device responsive preview scaling.',
                'author' => 'Jejakawan',
                'license' => 'Commercial PRO',
                'license_tier' => 'pro',
                'is_core' => false,
                'default_status' => 'active',
                'family' => 'cms',
                'dependencies' => [
                    'layout' => '>=1.0.0',
                    'publishing' => '>=1.0.0',
                ],
                'settings' => [
                    'enable_site_editor' => true,
                    'enable_page_builder' => true,
                    'default_device_mode' => 'desktop',
                ],
            ];

            DB::table('sys_extensions')->insert([
                'id' => (string) Str::uuid(),
                'slug' => 'visual-builder',
                'type' => 'plugin',
                'family' => 'cms',
                'name' => 'Visual Page & Site Builder',
                'version' => '1.0.0',
                'database_version' => '1.0.0',
                'status' => 'active',
                'is_active' => true,
                'is_core' => false,
                'is_system' => false,
                'author' => 'Jejakawan',
                'description' => 'Visual drag-and-drop page editor and site layout composer with multi-device responsive preview scaling.',
                'license' => 'Commercial PRO',
                'manifest' => json_encode($manifest),
                'requirements' => json_encode([
                    'layout' => '>=1.0.0',
                    'publishing' => '>=1.0.0',
                ]),
                'settings' => json_encode([
                    'enable_site_editor' => true,
                    'enable_page_builder' => true,
                    'default_device_mode' => 'desktop',
                ]),
                'priority' => 8,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sys_extensions')) {
            DB::table('sys_extensions')->where('slug', 'visual-builder')->delete();
        }
    }
};
