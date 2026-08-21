<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('infra_webhook_deliveries', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('webhook_id')->constrained('infra_webhooks')->cascadeOnDelete();
            $table->string('event', 128);
            $table->unsignedSmallInteger('attempt')->default(1);
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->string('status', 32);
            $table->text('response_body')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->timestamps();

            $table->index(['webhook_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('infra_webhook_deliveries');
    }
};
