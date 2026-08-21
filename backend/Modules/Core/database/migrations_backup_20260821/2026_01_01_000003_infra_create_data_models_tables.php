<?php

declare(strict_types=1);

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
        if (! Schema::hasTable('sys_content_types')) {
            Schema::create('sys_content_types', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->json('fields')->nullable(); // Manifest fields schema: [ { name, slug, type, rules, is_required } ]
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('sys_dynamic_records')) {
            Schema::create('sys_dynamic_records', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('content_type_id')->index();
                $table->json('data')->nullable(); // Holds the dynamic EAV field payload
                $table->timestamps();

                $table->foreign('content_type_id')
                    ->references('id')
                    ->on('sys_content_types')
                    ->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sys_dynamic_records');
        Schema::dropIfExists('sys_content_types');
    }
};
