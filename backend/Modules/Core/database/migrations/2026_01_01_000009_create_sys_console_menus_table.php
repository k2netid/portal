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
        Schema::dropIfExists('sys_console_menus');

        Schema::create('sys_console_menus', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('parent_id')->nullable()->index();
            $table->string('group_slug', 64)->default('system_config')->index();
            $table->string('name', 128);
            $table->string('label_key', 128)->nullable();
            $table->string('route_name', 128)->nullable();
            $table->string('url', 255)->nullable();
            $table->string('icon', 64)->nullable();
            $table->string('permission', 128)->nullable();
            $table->string('role', 64)->nullable();
            $table->string('extension_slug', 64)->nullable()->index();
            $table->string('badge_text', 32)->nullable();
            $table->string('badge_variant', 32)->default('primary');
            $table->integer('order')->default(0)->index();
            $table->boolean('is_visible')->default(true);
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::table('sys_console_menus', function (Blueprint $table): void {
            $table->foreign('parent_id')
                ->references('id')
                ->on('sys_console_menus')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sys_console_menus');
    }
};
