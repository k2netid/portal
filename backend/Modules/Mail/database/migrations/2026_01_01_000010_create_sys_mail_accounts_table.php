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
        Schema::dropIfExists('sys_mail_accounts');

        Schema::create('sys_mail_accounts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->index();
            $table->string('name', 128);
            $table->string('email', 191)->index();
            $table->string('account_type', 32)->default('system_global'); // system_global | custom_personal

            // SMTP Settings
            $table->string('smtp_host', 191)->nullable();
            $table->integer('smtp_port')->nullable();
            $table->string('smtp_username', 191)->nullable();
            $table->text('smtp_password')->nullable(); // Encrypted
            $table->string('smtp_encryption', 16)->nullable(); // tls | ssl | null

            // IMAP Settings
            $table->string('imap_host', 191)->nullable();
            $table->integer('imap_port')->nullable();
            $table->string('imap_username', 191)->nullable();
            $table->text('imap_password')->nullable(); // Encrypted
            $table->string('imap_encryption', 16)->nullable(); // ssl | tls | null

            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('signature')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('srv_auth_users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sys_mail_accounts');
    }
};
