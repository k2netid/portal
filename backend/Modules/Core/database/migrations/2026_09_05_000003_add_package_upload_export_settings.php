<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('sys_settings')) {
            return;
        }

        $settings = [
            [
                'key' => 'enable_theme_upload',
                'value' => '1',
                'group' => 'security',
                'type' => 'boolean',
            ],
            [
                'key' => 'enable_plugin_upload',
                'value' => '1',
                'group' => 'security',
                'type' => 'boolean',
            ],
            [
                'key' => 'enable_theme_export',
                'value' => '1',
                'group' => 'security',
                'type' => 'boolean',
            ],
            [
                'key' => 'enable_plugin_export',
                'value' => '1',
                'group' => 'security',
                'type' => 'boolean',
            ],
        ];

        foreach ($settings as $setting) {
            $exists = DB::table('sys_settings')->where('key', $setting['key'])->exists();
            if (! $exists) {
                DB::table('sys_settings')->insert([
                    'id' => (string) Str::uuid(),
                    'key' => $setting['key'],
                    'value' => $setting['value'],
                    'group' => $setting['group'],
                    'type' => $setting['type'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sys_settings')) {
            DB::table('sys_settings')->whereIn('key', [
                'enable_theme_upload',
                'enable_plugin_upload',
                'enable_theme_export',
                'enable_plugin_export',
            ])->delete();
        }
    }
};
