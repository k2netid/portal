<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sys_activity_logs')) {
            Schema::table('sys_activity_logs', function (Blueprint $table): void {
                $table->index(['log_name', 'created_at'], 'idx_act_logs_name_created');
            });
        }

        if (Schema::hasTable('sec_logs')) {
            Schema::table('sec_logs', function (Blueprint $table): void {
                $table->index(['event_type', 'created_at'], 'idx_sec_logs_event_created');
            });
        }

        if (Schema::hasTable('infra_webhook_deliveries')) {
            Schema::table('infra_webhook_deliveries', function (Blueprint $table): void {
                $table->index(['webhook_id', 'created_at'], 'idx_wh_deliveries_created');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sys_activity_logs')) {
            Schema::table('sys_activity_logs', function (Blueprint $table): void {
                $table->dropIndex('idx_act_logs_name_created');
            });
        }

        if (Schema::hasTable('sec_logs')) {
            Schema::table('sec_logs', function (Blueprint $table): void {
                $table->dropIndex('idx_sec_logs_event_created');
            });
        }

        if (Schema::hasTable('infra_webhook_deliveries')) {
            Schema::table('infra_webhook_deliveries', function (Blueprint $table): void {
                $table->dropIndex('idx_wh_deliveries_created');
            });
        }
    }
};
