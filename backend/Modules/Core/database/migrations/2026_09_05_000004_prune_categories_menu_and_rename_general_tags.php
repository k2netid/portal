<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('sys_console_menus')) {
            return;
        }

        // 1. Remove standalone Categories menu item (now consolidated inside Content Studio tabs)
        DB::table('sys_console_menus')
            ->where('route_name', 'categories.index')
            ->delete();

        // 2. Rename Tags to General Tags in Library group for UX clarity
        DB::table('sys_console_menus')
            ->where('route_name', 'tags')
            ->update([
                'name' => 'General Tags',
                'label_key' => 'library.navigation.menu.generalTags',
                'updated_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('sys_console_menus')) {
            return;
        }

        // Revert General Tags to Tags
        DB::table('sys_console_menus')
            ->where('route_name', 'tags')
            ->update([
                'name' => 'Tags',
                'label_key' => 'library.navigation.menu.tags',
                'updated_at' => now(),
            ]);
    }
};
