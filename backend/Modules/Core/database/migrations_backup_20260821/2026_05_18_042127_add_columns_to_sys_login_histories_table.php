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
        Schema::table('sys_login_histories', function (Blueprint $table): void {
            if (! Schema::hasColumn('sys_login_histories', 'logout_at')) {
                $table->timestamp('logout_at')->nullable()->after('login_at');
            }
            if (! Schema::hasColumn('sys_login_histories', 'session_duration')) {
                $table->integer('session_duration')->nullable()->after('logout_at');
            }
            if (! Schema::hasColumn('sys_login_histories', 'failure_reason')) {
                $table->text('failure_reason')->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sys_login_histories', function (Blueprint $table): void {
            if (Schema::hasColumn('sys_login_histories', 'logout_at')) {
                $table->dropColumn('logout_at');
            }
            if (Schema::hasColumn('sys_login_histories', 'session_duration')) {
                $table->dropColumn('session_duration');
            }
            if (Schema::hasColumn('sys_login_histories', 'failure_reason')) {
                $table->dropColumn('failure_reason');
            }
        });
    }
};
