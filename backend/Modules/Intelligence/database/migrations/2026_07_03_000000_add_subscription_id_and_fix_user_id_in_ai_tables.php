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
        // 1. Update ai_usage_logs: Add subscription_id (UUID, nullable, with index)
        if (Schema::hasTable('ai_usage_logs')) {
            Schema::table('ai_usage_logs', function (Blueprint $table): void {
                if (! Schema::hasColumn('ai_usage_logs', 'subscription_id')) {
                    $table->uuid('subscription_id')->nullable()->index();
                }
            });
        }

        // 2. Update ai_taxonomy_batches: Add subscription_id & Fix user_id datatype
        if (Schema::hasTable('ai_taxonomy_batches')) {
            Schema::table('ai_taxonomy_batches', function (Blueprint $table): void {
                if (! Schema::hasColumn('ai_taxonomy_batches', 'subscription_id')) {
                    $table->uuid('subscription_id')->nullable()->index();
                }
                if (Schema::hasColumn('ai_taxonomy_batches', 'user_id')) {
                    $table->dropColumn('user_id');
                }
            });
            // Re-create user_id as UUID
            Schema::table('ai_taxonomy_batches', function (Blueprint $table): void {
                $table->uuid('user_id')->nullable()->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('ai_usage_logs')) {
            Schema::table('ai_usage_logs', function (Blueprint $table): void {
                if (Schema::hasColumn('ai_usage_logs', 'subscription_id')) {
                    $table->dropColumn('subscription_id');
                }
            });
        }

        if (Schema::hasTable('ai_taxonomy_batches')) {
            Schema::table('ai_taxonomy_batches', function (Blueprint $table): void {
                if (Schema::hasColumn('ai_taxonomy_batches', 'subscription_id')) {
                    $table->dropColumn('subscription_id');
                }
                if (Schema::hasColumn('ai_taxonomy_batches', 'user_id')) {
                    $table->dropColumn('user_id');
                }
            });
            Schema::table('ai_taxonomy_batches', function (Blueprint $table): void {
                $table->unsignedBigInteger('user_id')->nullable();
            });
        }
    }
};
