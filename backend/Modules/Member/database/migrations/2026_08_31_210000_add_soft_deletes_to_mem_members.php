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
            $table->softDeletes();
            $table->index('status');
            $table->index('created_at');
            $table->index('email_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('mem_members', function (Blueprint $table): void {
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['email_verified_at']);
            $table->dropSoftDeletes();
        });
    }
};
