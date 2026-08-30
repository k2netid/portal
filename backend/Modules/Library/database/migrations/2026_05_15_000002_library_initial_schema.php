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
        // 1. Tags
        Schema::create('lib_tags', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->index();
            $table->string('type')->default('content')->index(); // content, media, student, etc.
            $table->uuid('author_id')->nullable()->index();
            $table->integer('usage_count')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['slug', 'type']);
        });

        // 2. Taggables (Polymorphic Pivot)
        Schema::create('lib_taggables', function (Blueprint $table): void {
            $table->uuid('tag_id');
            $table->uuid('taggable_id');
            $table->string('taggable_type');
            $table->index(['taggable_id', 'taggable_type']);

            $table->foreign('tag_id')->references('id')->on('lib_tags')->onDelete('cascade');
        });

        // 3. Fields Definition
        Schema::create('lib_fields', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('key')->index();
            $table->string('type')->default('text'); // text, textarea, select, checkbox, radio, date, etc.
            $table->json('options')->nullable(); // For select/radio/checkbox
            $table->json('rules')->nullable(); // Validation rules
            $table->string('default_value')->nullable();
            $table->string('placeholder')->nullable();
            $table->string('help_text')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_filterable')->default(false);
            $table->integer('sort_order')->default(0);
            $table->uuid('author_id')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['key']);
        });

        // 4. Field Groups
        Schema::create('lib_field_groups', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 5. Field Group Assignments (Connect groups to models)
        Schema::create('lib_field_group_assignments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('group_id');
            $table->string('assignable_type'); // e.g., Modules\Library\Models\Content
            $table->string('module_scope')->nullable(); // e.g., Jejakawan, library
            $table->timestamps();

            $table->foreign('group_id')->references('id')->on('lib_field_groups')->onDelete('cascade');
        });

        // 6. Pivot Field <-> Group
        Schema::create('lib_field_group_pivot', function (Blueprint $table): void {
            $table->uuid('field_id');
            $table->uuid('group_id');
            $table->integer('sort_order')->default(0);

            $table->primary(['field_id', 'group_id']);
            $table->foreign('field_id')->references('id')->on('lib_fields')->onDelete('cascade');
            $table->foreign('group_id')->references('id')->on('lib_field_groups')->onDelete('cascade');
        });

        // 7. Categories
        Schema::create('lib_categories', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('parent_id')->nullable();
            $table->uuid('author_id')->nullable();
            $table->string('name');
            $table->string('slug')->index();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->string('image')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('content_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('lib_categories', function (Blueprint $table): void {
            $table->foreign('parent_id')->references('id')->on('lib_categories')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lib_categories');
        Schema::dropIfExists('lib_field_group_pivot');
        Schema::dropIfExists('lib_field_group_assignments');
        Schema::dropIfExists('lib_field_groups');
        Schema::dropIfExists('lib_fields');
        Schema::dropIfExists('lib_taggables');
        Schema::dropIfExists('lib_tags');
    }
};
