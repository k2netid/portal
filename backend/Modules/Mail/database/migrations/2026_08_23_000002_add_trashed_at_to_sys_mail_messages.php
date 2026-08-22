<?php

declare(strict_types=1);

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
            if (! Schema::hasColumn('sys_mail_messages', 'trashed_at')) {
                $table->timestamp('trashed_at')->nullable()->after('folder');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('sys_mail_messages')) {
            return;
        }

        Schema::table('sys_mail_messages', function (Blueprint $table): void {
            if (Schema::hasColumn('sys_mail_messages', 'trashed_at')) {
                $table->dropColumn('trashed_at');
            }
        });
    }
};
