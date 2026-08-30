<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pub_bookmarks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('srv_auth_users')->cascadeOnDelete();
            $table->foreignUuid('content_id')->constrained('pub_contents')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'content_id']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pub_bookmarks');
    }
};
