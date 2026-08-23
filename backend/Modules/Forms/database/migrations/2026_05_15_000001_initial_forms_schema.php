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
        // 1. Forms
        Schema::create('frm_forms', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('author_id')->nullable();
            $table->string('name');
            $table->string('slug')->index();
            $table->text('description')->nullable();
            $table->text('success_message')->nullable();
            $table->string('redirect_url')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('submission_count')->default(0);
            $table->integer('view_count')->default(0);
            $table->integer('start_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Form Fields
        Schema::create('frm_form_fields', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('form_id')->index();
            $table->string('name');
            $table->string('label');
            $table->string('type')->default('text');
            $table->text('placeholder')->nullable();
            $table->text('help_text')->nullable();
            $table->text('default_value')->nullable();
            $table->json('options')->nullable();
            $table->json('validation_rules')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_required')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('form_id')->references('id')->on('frm_forms')->onDelete('cascade');
        });

        // 3. Form Submissions
        Schema::create('frm_form_submissions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('form_id')->index();
            $table->uuid('user_id')->nullable()->index();
            $table->json('data');
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('status')->default('new'); // new, read, processed
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('form_id')->references('id')->on('frm_forms')->onDelete('cascade');
        });

        // 4. Form Analytics
        Schema::create('frm_form_analytics', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('form_id')->index();
            $table->date('date');
            $table->integer('views')->default(0);
            $table->integer('starts')->default(0);
            $table->integer('submissions')->default(0);
            $table->timestamps();

            $table->unique(['form_id', 'date']);
            $table->foreign('form_id')->references('id')->on('frm_forms')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('frm_form_analytics');
        Schema::dropIfExists('frm_form_submissions');
        Schema::dropIfExists('frm_form_fields');
        Schema::dropIfExists('frm_forms');
    }
};
