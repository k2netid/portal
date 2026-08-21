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
        Schema::table('srv_auth_users', function (Blueprint $table) {
            $table->string('kyc_level')->default('level_0')->after('email_verified_at');
            $table->integer('onboarding_step')->default(0)->after('kyc_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('srv_auth_users', function (Blueprint $table) {
            $table->dropColumn(['kyc_level', 'onboarding_step']);
        });
    }
};
