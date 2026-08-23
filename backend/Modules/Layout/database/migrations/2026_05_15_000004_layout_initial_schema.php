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
        // 1. Menus
        Schema::create('lay_menus', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->index();
            $table->string('location')->nullable()->index();
            $table->string('description')->nullable();
            $table->string('module_scope')->default('publishing')->index();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Menu Items
        Schema::create('lay_menu_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('menu_id')->index();
            $table->uuid('parent_id')->nullable()->index();
            $table->string('title');
            $table->string('url')->nullable();
            $table->string('type')->default('link'); // link, route, content, etc.
            $table->string('target_id')->nullable(); // Support both Integer and UUID targets
            $table->string('target_type')->nullable();
            $table->string('icon')->nullable();
            $table->string('css_class')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('open_in_new_tab')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('menu_id')->references('id')->on('lay_menus')->onDelete('cascade');
        });

        // 3. Widgets
        Schema::create('lay_widgets', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('type')->index(); // html, content_list, form, etc.
            $table->string('location')->index();
            $table->json('settings')->nullable();
            $table->string('module_scope')->default('publishing')->index();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // 4. Redirects
        Schema::create('lay_redirects', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('source_path')->index();
            $table->string('target_path');
            $table->integer('status_code')->default(301); // 301, 302, or 0 for internal alias
            $table->string('module_scope')->default('publishing')->index();
            $table->unsignedBigInteger('hits')->default(0);
            $table->timestamp('last_hit_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['source_path']);
        });

        // 5. Themes
        Schema::create('lay_themes', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->default('frontend'); // frontend, admin
            $table->string('path');
            $table->string('version')->nullable();
            $table->text('description')->nullable();
            $table->string('author')->nullable();
            $table->string('author_url')->nullable();
            $table->string('license')->nullable();
            $table->string('status')->default('inactive');
            $table->json('settings')->nullable();
            $table->json('dependencies')->nullable();
            $table->json('supports')->nullable();
            $table->string('parent_theme')->nullable();
            $table->string('preview_image')->nullable();
            $table->text('custom_css')->nullable();
            $table->string('update_url')->nullable();
            $table->boolean('auto_update')->default(false);
            $table->string('requires_publishing_version')->nullable();
            $table->timestamp('last_updated_at')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lay_themes');
        Schema::dropIfExists('lay_redirects');
        Schema::dropIfExists('lay_widgets');
        Schema::dropIfExists('lay_menu_items');
        Schema::dropIfExists('lay_menus');
    }
};
