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
        Schema::create('nwl_subscribers', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('email')->index();
            $table->string('name')->nullable();
            $table->string('status')->default('subscribed'); // subscribed, unsubscribed
            $table->string('source')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('subscribed_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['email']);
        });

        Schema::create('nwl_campaigns', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('subject');
            $table->longText('content');
            $table->string('status')->default('draft'); // draft, sending, sent, failed
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->integer('total_recipients')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nwl_campaigns');
        Schema::dropIfExists('nwl_subscribers');
    }
};
