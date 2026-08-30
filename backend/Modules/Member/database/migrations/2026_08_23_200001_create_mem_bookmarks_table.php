<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mem_bookmarks', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('member_id');
            $table->uuid('content_id');
            $table->timestamps();

            $table->unique(['member_id', 'content_id']);
            $table->foreign('member_id')->references('id')->on('mem_members')->cascadeOnDelete();
            $table->foreign('content_id')->references('id')->on('pub_contents')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mem_bookmarks');
    }
};
