<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sys_mail_messages') && ! Schema::hasColumn('sys_mail_messages', 'scheduled_at')) {
            Schema::table('sys_mail_messages', function (Blueprint $table): void {
                $table->timestamp('scheduled_at')->nullable()->index()->after('sent_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sys_mail_messages') && Schema::hasColumn('sys_mail_messages', 'scheduled_at')) {
            Schema::table('sys_mail_messages', function (Blueprint $table): void {
                $table->dropColumn('scheduled_at');
            });
        }
    }
};
