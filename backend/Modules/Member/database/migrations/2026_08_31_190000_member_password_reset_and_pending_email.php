<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mem_members', function (Blueprint $table): void {
            if (! Schema::hasColumn('mem_members', 'pending_email')) {
                $table->string('pending_email')->nullable()->after('email');
            }
        });

        if (! Schema::hasTable('mem_password_reset_tokens')) {
            Schema::create('mem_password_reset_tokens', function (Blueprint $table): void {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mem_password_reset_tokens');

        Schema::table('mem_members', function (Blueprint $table): void {
            if (Schema::hasColumn('mem_members', 'pending_email')) {
                $table->dropColumn('pending_email');
            }
        });
    }
};
