<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sys_extension_logs', function (Blueprint $table): void {
            if (! Schema::hasColumn('sys_extension_logs', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('performed_by');
            }
            if (! Schema::hasColumn('sys_extension_logs', 'meta')) {
                $table->json('meta')->nullable()->after('ip_address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sys_extension_logs', function (Blueprint $table): void {
            if (Schema::hasColumn('sys_extension_logs', 'meta')) {
                $table->dropColumn('meta');
            }
            if (Schema::hasColumn('sys_extension_logs', 'ip_address')) {
                $table->dropColumn('ip_address');
            }
        });
    }
};
