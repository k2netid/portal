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

        $exists = DB::table('sys_extensions')->where('slug', 'floating-social-dock')->exists();
        if (! $exists) {
            DB::table('sys_extensions')->insert([
                'id' => (string) Str::uuid(),
                'slug' => 'floating-social-dock',
                'type' => 'plugin',
                'name' => 'Floating Social Dock & Hotline',
                'version' => '1.0.0',
                'database_version' => '1.0.0',
                'status' => 'active',
                'is_active' => true,
                'is_core' => false,
                'is_system' => false,
                'author' => 'Jejakawan',
                'description' => 'Dock mengambang interaktif akses cepat media sosial, hotline WhatsApp, dan saluran komunikasi resmi sekolah/organisasi di semua tema.',
                'license' => 'MIT',
                'settings' => json_encode([
                    'theme_blocks' => [
                        ['slot' => 'floating_overlay'],
                    ],
                    'position' => 'right',
                    'orientation' => 'auto',
                    'style' => 'glass',
                    'default_collapsed' => false,
                    'show_on_mobile' => false,
                ]),
                'priority' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sys_extensions')) {
            DB::table('sys_extensions')->where('slug', 'floating-social-dock')->delete();
        }
    }
};
