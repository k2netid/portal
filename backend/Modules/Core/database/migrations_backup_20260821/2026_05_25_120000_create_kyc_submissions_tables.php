<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kyc_submissions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('srv_auth_users')->cascadeOnDelete();
            $table->string('status', 32)->default('draft');
            $table->text('rejection_reason')->nullable();
            $table->foreignUuid('reviewed_by')->nullable()->constrained('srv_auth_users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['user_id', 'status']);
        });

        Schema::create('kyc_documents', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('submission_id')->constrained('kyc_submissions')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('srv_auth_users')->cascadeOnDelete();
            $table->string('type', 32);
            $table->string('file_path');
            $table->string('mime_type', 128);
            $table->unsignedInteger('size_bytes');
            $table->string('original_name');
            $table->timestamps();

            $table->unique(['submission_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kyc_documents');
        Schema::dropIfExists('kyc_submissions');
    }
};
