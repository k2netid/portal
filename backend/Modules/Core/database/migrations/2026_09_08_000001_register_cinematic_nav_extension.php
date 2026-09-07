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

        $exists = DB::table('sys_extensions')->where('slug', 'cinematic-nav')->exists();
        if (! $exists) {
            DB::table('sys_extensions')->insert([
                'id' => (string) Str::uuid(),
                'slug' => 'cinematic-nav',
                'type' => 'plugin',
                'family' => 'plugin',
                'name' => 'Cinematic Side Navigation & Viewport Scroll Snap',
                'version' => '1.0.0',
                'database_version' => '1.0.0',
                'status' => 'active',
                'is_active' => true,
                'is_core' => false,
                'is_system' => false,
                'author' => 'Jejakawan',
                'description' => 'Navigasi titik samping mengambang universal dengan 4 preset visual (glass, minimal, glow, bars), GSAP spring motion, dan viewport scroll snap di semua tema.',
                'license' => 'MIT',
                'settings' => json_encode([
                    'theme_blocks' => [
                        ['slot' => 'floating_overlay'],
                    ],
                    'preset' => 'glass',
                    'position' => 'right',
                    'label_mode' => 'hover',
                    'show_on_mobile' => false,
                    'enable_scroll_snap' => false,
                ]),
                'priority' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sys_extensions')) {
            DB::table('sys_extensions')->where('slug', 'cinematic-nav')->delete();
        }
    }
};
