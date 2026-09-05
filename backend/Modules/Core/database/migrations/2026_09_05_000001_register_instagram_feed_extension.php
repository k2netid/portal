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

        $exists = DB::table('sys_extensions')->where('slug', 'instagram-feed')->exists();
        if (! $exists) {
            DB::table('sys_extensions')->insert([
                'id' => (string) Str::uuid(),
                'slug' => 'instagram-feed',
                'type' => 'plugin',
                'name' => 'Instagram Feed Integration',
                'version' => '1.0.0',
                'database_version' => '1.0.0',
                'status' => 'inactive',
                'is_active' => false,
                'is_core' => false,
                'is_system' => false,
                'author' => 'Jejakawan',
                'description' => 'Integrasi feed Instagram, galeri postingan media, komentar dan suka ke dalam tema publik dan Page Builder.',
                'license' => 'MIT',
                'settings' => json_encode([
                    'access_token' => '',
                    'instagram_username' => '',
                    'instagram_account_id' => '',
                    'theme_blocks' => [
                        ['slot' => 'after_hero'],
                    ],
                    'cache_ttl_minutes' => 60,
                    'post_limit' => 8,
                    'show_likes_count' => true,
                    'show_comments_count' => true,
                    'enable_lightbox' => true,
                    'comment_filter_keywords' => '',
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
            DB::table('sys_extensions')->where('slug', 'instagram-feed')->delete();
        }
    }
};
