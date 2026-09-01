<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mem_member_two_factor')) {
            return;
        }

        Schema::create('mem_member_two_factor', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('member_id')->unique();
            $table->text('secret')->nullable();
            $table->json('backup_codes')->nullable();
            $table->boolean('enabled')->default(false);
            $table->timestamp('enabled_at')->nullable();
            $table->timestamp('recovery_codes_generated_at')->nullable();
            $table->timestamps();

            $table->foreign('member_id')->references('id')->on('mem_members')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mem_member_two_factor');
    }
};
