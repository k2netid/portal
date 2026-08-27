<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sys_extensions', function (Blueprint $table): void {
            if (! Schema::hasColumn('sys_extensions', 'family')) {
                $table->string('family', 40)->default('module')->after('type');
            }
            if (! Schema::hasColumn('sys_extensions', 'parent_slug')) {
                $table->string('parent_slug', 100)->nullable()->after('family');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sys_extensions', function (Blueprint $table): void {
            if (Schema::hasColumn('sys_extensions', 'parent_slug')) {
                $table->dropColumn('parent_slug');
            }
            if (Schema::hasColumn('sys_extensions', 'family')) {
                $table->dropColumn('family');
            }
        });
    }
};
