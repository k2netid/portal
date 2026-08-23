<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_usage_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable();
            $table->string('feature', 64);
            $table->string('provider', 32)->nullable();
            $table->unsignedInteger('tokens_in')->default(0);
            $table->unsignedInteger('tokens_out')->default(0);
            $table->unsignedSmallInteger('duration_ms')->nullable();
            $table->timestamps();

            $table->index(['created_at']);
            $table->index(['feature', 'created_at']);
        });

        Schema::create('ai_taxonomy_batches', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('status', 32)->default('pending');
            $table->unsignedSmallInteger('total_items')->default(0);
            $table->unsignedSmallInteger('completed_items')->default(0);
            $table->unsignedSmallInteger('failed_items')->default(0);
            $table->json('items');
            $table->json('results')->nullable();
            $table->string('error_message', 512)->nullable();
            $table->string('provider', 64)->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_taxonomy_batches');
        Schema::dropIfExists('ai_usage_logs');
    }
};
