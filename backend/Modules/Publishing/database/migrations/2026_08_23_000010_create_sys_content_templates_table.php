<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sys_content_templates')) {
            return;
        }

        Schema::create('sys_content_templates', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('type')->default('post');
            $table->text('title_template')->nullable();
            $table->longText('body_template')->nullable();
            $table->text('excerpt_template')->nullable();
            $table->json('default_fields')->nullable();
            $table->json('meta')->nullable();
            $table->uuid('category_id')->nullable()->index();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('usage_count')->default(0);
            $table->uuid('author_id')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sys_content_templates');
    }
};
