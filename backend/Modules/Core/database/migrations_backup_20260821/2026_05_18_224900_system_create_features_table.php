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
        if (! Schema::hasTable('sys_features')) {
            Schema::create('sys_features', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->string('extension_slug', 100);
                $table->string('slug', 100)->unique();
                $table->string('name', 150);
                $table->text('description')->nullable();
                $table->string('category', 50); // 'infrastructure', 'security', 'business', 'plugin'
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('extension_slug')
                    ->references('slug')
                    ->on('sys_extensions')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sys_features');
    }
};
