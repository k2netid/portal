<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Contents (Posts, Pages, etc.)
        Schema::create('pub_contents', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('author_id')->index();
            $table->string('title');
            $table->string('slug')->index();
            $table->text('excerpt')->nullable();
            $table->text('intro')->nullable();
            $table->longText('body')->nullable();
            $table->string('type')->default('post')->index(); // post, page, product
            $table->string('status')->default('draft')->index(); // draft, published, scheduled
            $table->string('featured_image')->nullable();
            $table->boolean('is_featured')->default(false)->index();
            $table->uuid('category_id')->nullable()->index();
            $table->unsignedBigInteger('views')->default(0);
            $table->string('comment_status')->default('open'); // open, closed
            $table->json('metadata')->nullable();
            $table->json('meta')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->string('og_image')->nullable();
            $table->uuid('locked_by')->nullable()->index();
            $table->timestamp('locked_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['slug', 'type']);
            $table->foreign('category_id')->references('id')->on('lib_categories')->onDelete('set null');
            $table->foreign('author_id')->references('id')->on('srv_auth_users')->onDelete('cascade');
        });

        // 2. Content-Category Pivot
        Schema::create('pub_content_category', function (Blueprint $table): void {
            $table->uuid('content_id');
            $table->uuid('category_id');
            $table->primary(['content_id', 'category_id']);
            $table->foreign('content_id')->references('id')->on('pub_contents')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('lib_categories')->onDelete('cascade');
        });

        // 4. Revisions
        Schema::create('pub_content_revisions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('content_id')->index();
            $table->uuid('author_id')->nullable()->index();
            $table->string('title');
            $table->longText('body');
            $table->json('meta')->nullable();
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->foreign('content_id')->references('id')->on('pub_contents')->onDelete('cascade');
        });

        // 5. Comments
        Schema::create('pub_comments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('content_id')->index();
            $table->uuid('user_id')->nullable()->index();
            $table->uuid('parent_id')->nullable()->index();
            $table->text('body');
            $table->string('status')->default('pending'); // pending, approved, spam, trash
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->uuid('locked_by')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('content_id')->references('id')->on('pub_contents')->onDelete('cascade');
        });

        // 7. Content Custom Fields
        Schema::create('pub_content_custom_fields', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('content_id')->index();
            $table->uuid('custom_field_id')->index();
            $table->text('value')->nullable();
            $table->timestamps();

            $table->foreign('content_id')->references('id')->on('pub_contents')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pub_content_custom_fields');
        Schema::dropIfExists('pub_comments');
        Schema::dropIfExists('pub_content_revisions');
        Schema::dropIfExists('pub_content_category');
        Schema::dropIfExists('pub_contents');
    }
};
