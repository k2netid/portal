<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sys_mail_messages')) {
            return;
        }

        Schema::table('sys_mail_messages', function (Blueprint $table): void {
            if (! Schema::hasColumn('sys_mail_messages', 'user_id')) {
                $table->uuid('user_id')->nullable()->index()->after('id');
            }
            if (! Schema::hasColumn('sys_mail_messages', 'snoozed_until')) {
                $table->timestamp('snoozed_until')->nullable()->index()->after('scheduled_at');
            }
            if (! Schema::hasColumn('sys_mail_messages', 'dispatch_locked_at')) {
                $table->timestamp('dispatch_locked_at')->nullable()->after('snoozed_until');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('sys_mail_messages')) {
            return;
        }

        Schema::table('sys_mail_messages', function (Blueprint $table): void {
            foreach (['user_id', 'snoozed_until', 'dispatch_locked_at'] as $column) {
                if (Schema::hasColumn('sys_mail_messages', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
