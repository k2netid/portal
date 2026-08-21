<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sys_scheduled_tasks', function (Blueprint $table): void {
            if (! Schema::hasColumn('sys_scheduled_tasks', 'status')) {
                $table->string('status')->nullable()->after('next_run_at');
            }
            if (! Schema::hasColumn('sys_scheduled_tasks', 'output')) {
                $table->text('output')->nullable()->after('status');
            }
            if (! Schema::hasColumn('sys_scheduled_tasks', 'options')) {
                $table->json('options')->nullable()->after('description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sys_scheduled_tasks', function (Blueprint $table): void {
            $columnsToDrop = [];
            if (Schema::hasColumn('sys_scheduled_tasks', 'status')) {
                $columnsToDrop[] = 'status';
            }
            if (Schema::hasColumn('sys_scheduled_tasks', 'output')) {
                $columnsToDrop[] = 'output';
            }
            if (Schema::hasColumn('sys_scheduled_tasks', 'options')) {
                $columnsToDrop[] = 'options';
            }
            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
