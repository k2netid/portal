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
        if (! Schema::hasTable('sys_mail_messages')) {
            Schema::create('sys_mail_messages', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->string('account_id')->nullable()->index();
                $table->string('message_id')->nullable()->index();
                $table->string('folder', 32)->default('inbox')->index();
                $table->string('sender_name');
                $table->string('sender_email')->index();
                $table->json('recipients');
                $table->json('cc')->nullable();
                $table->json('bcc')->nullable();
                $table->string('subject');
                $table->text('snippet')->nullable();
                $table->longText('body')->nullable();
                $table->boolean('is_read')->default(false)->index();
                $table->boolean('is_starred')->default(false)->index();
                $table->json('labels')->nullable();
                $table->json('attachments')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('received_at')->nullable();
                $table->timestamps();

                $table->index(['folder', 'is_read']);
                $table->index(['folder', 'is_starred']);
                $table->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sys_mail_messages');
    }
};
