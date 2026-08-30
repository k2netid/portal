<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('srch_indexes', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('searchable_type');
            $table->uuid('searchable_id');
            $table->string('title');
            $table->text('content')->nullable();
            $table->text('excerpt')->nullable();
            $table->json('meta')->nullable();
            $table->string('url')->nullable();
            $table->string('type')->nullable();
            $table->integer('relevance_score')->default(0);
            $table->timestamps();

            $table->index(['searchable_type', 'searchable_id']);
            if (DB::connection()->getDriverName() !== 'sqlite') {
                $table->fulltext(['title', 'content']);
            }
        });

        Schema::create('srch_queries', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable()->index();
            $table->string('query');
            $table->integer('results_count')->default(0);
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('filters')->nullable();
            $table->timestamp('searched_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('srch_queries');
        Schema::dropIfExists('srch_indexes');
    }
};
