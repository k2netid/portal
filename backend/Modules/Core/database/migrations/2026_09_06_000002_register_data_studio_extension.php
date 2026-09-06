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

        $exists = DB::table('sys_extensions')->where('slug', 'data-studio')->exists();
        if (! $exists) {
            $manifest = [
                'slug' => 'data-studio',
                'type' => 'plugin',
                'name' => 'Data Model Studio',
                'version' => '1.0.0',
                'description' => 'Dynamic entity modeling engine, custom field schemas, relational validations, and instant REST API generation.',
                'author' => 'Jejakawan',
                'license' => 'Commercial PRO',
                'license_tier' => 'pro',
                'is_core' => false,
                'default_status' => 'active',
                'family' => 'infra',
                'dependencies' => [
                    'core' => '>=1.0.0',
                ],
                'settings' => [
                    'enable_instant_api' => true,
                    'enable_scaffolding' => true,
                ],
            ];

            DB::table('sys_extensions')->insert([
                'id' => (string) Str::uuid(),
                'slug' => 'data-studio',
                'type' => 'plugin',
                'family' => 'infra',
                'name' => 'Data Model Studio',
                'version' => '1.0.0',
                'database_version' => '1.0.0',
                'status' => 'active',
                'is_active' => true,
                'is_core' => false,
                'is_system' => false,
                'author' => 'Jejakawan',
                'description' => 'Dynamic entity modeling engine, custom field schemas, relational validations, and instant REST API generation.',
                'license' => 'Commercial PRO',
                'manifest' => json_encode($manifest),
                'requirements' => json_encode([
                    'core' => '>=1.0.0',
                ]),
                'settings' => json_encode([
                    'enable_instant_api' => true,
                    'enable_scaffolding' => true,
                ]),
                'priority' => 9,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (Schema::hasTable('sys_console_menus')) {
            DB::table('sys_console_menus')
                ->where('route_name', 'model-index')
                ->update([
                    'extension_slug' => 'data-studio',
                    'badge_text' => 'PRO',
                    'badge_variant' => 'primary',
                ]);

            DB::table('sys_console_menus')
                ->where('group_slug', 'studio')
                ->whereNull('parent_id')
                ->update([
                    'extension_slug' => 'data-studio',
                ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sys_extensions')) {
            DB::table('sys_extensions')->where('slug', 'data-studio')->delete();
        }

        if (Schema::hasTable('sys_console_menus')) {
            DB::table('sys_console_menus')
                ->where('route_name', 'model-index')
                ->update([
                    'extension_slug' => null,
                    'badge_text' => null,
                    'badge_variant' => 'primary',
                ]);

            DB::table('sys_console_menus')
                ->where('group_slug', 'studio')
                ->whereNull('parent_id')
                ->update([
                    'extension_slug' => null,
                ]);
        }
    }
};
